<?php

namespace App\Command\Transaction;

use App\Entity\Transaction;
use App\Entity\UserDonor;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:transaction:clean',
    description: 'Clean transactions',
)]
class CleanTransactionsCommand extends Command
{
    private int $lastId = 0;

    public function __construct(private EntityManagerInterface $entityManager, private MailerInterface $mailer, private readonly TransactionRepository $transactionRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $store = new FlockStore();
        $factory = new LockFactory($store);
        $lock = $factory->createLock($this->getName(), 0);
        if (!$lock->acquire()) {
            return Command::FAILURE;
        }

        $io = new SymfonyStyle($input, $output);
        $io->section('Command started at '.date('Y-m-d H:i:s'));

        $total = 0;
        while (true) {
            $donors = $this->getDonors();
            if (empty($donors)) {
                break;
            }

            foreach ($donors as $donor) {
                if ($donor->isMonthly()) {
                    continue;
                }

                $transactions = $this->transactionRepository->findBy([
                    'user' => $donor->getUser(),
                ], ['id' => 'ASC']);

                $sumPaid = 0;
                $lastPaidId = 0;
                $haveNotPaid = false;
                $transactionsAfterLastPaid = 0;

                foreach ($transactions as $transaction) {
                    if ($transaction->isUserDonorConfirmed() || Transaction::STATUS_CONFIRMED == $transaction->getStatus()) {
                        $sumPaid += $transaction->getAmount();
                        $lastPaidId = $transaction->getId();
                        continue;
                    }

                    if (Transaction::STATUS_NOT_PAID == $transaction->getStatus()) {
                        $haveNotPaid = true;
                    }
                }

                foreach ($transactions as $transaction) {
                    if ($lastPaidId < $transaction->getId()) {
                        ++$transactionsAfterLastPaid;
                    }
                }

                if (0 == $transactionsAfterLastPaid) {
                    continue;
                }

                if (!$haveNotPaid) {
                    continue;
                }

                if (($sumPaid + 499) < $donor->getAmount()) {
                    continue;
                }

                $output->writeln('Delete all not paid transactions for '.$donor->getUser()->getEmail().' | LastPaidID: '.$lastPaidId);
                $this->deleteAllNotPaidTransactions($donor, $lastPaidId);
                ++$total;
            }
        }

        var_dump($total);
        $io->success('Command finished at '.date('Y-m-d H:i:s'));

        return Command::SUCCESS;
    }

    public function deleteAllNotPaidTransactions(UserDonor $userDonor, int $lastPaidId): void
    {
        $this->entityManager->getConnection()->executeQuery('
            DELETE FROM transaction
            WHERE user_id = :userId
             AND status = :status
             AND user_donor_confirmed = 0
             AND id > :lastPaidId
            ', [
            'userId' => $userDonor->getUser()->getId(),
            'status' => Transaction::STATUS_NOT_PAID,
            'lastPaidId' => $lastPaidId,
        ]);

        $this->entityManager->getConnection()->executeQuery('
            DELETE FROM transaction
            WHERE user_id = :userId
             AND status = :status
             AND user_donor_confirmed = 0
             AND id > :lastPaidId
            ', [
            'userId' => $userDonor->getUser()->getId(),
            'status' => Transaction::STATUS_EXPIRED,
            'lastPaidId' => $lastPaidId,
        ]);
    }

    public function getDonors(): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ud')
            ->from(UserDonor::class, 'ud')
            ->innerJoin('ud.user', 'u')
            ->andWhere('u.isActive = 1')
            ->andWhere('u.isEmailVerified = 1')
            ->andWhere('ud.id > :lastId')
            ->setParameter('lastId', $this->lastId)
            ->orderBy('ud.id', 'ASC')
            ->setMaxResults(100);

        $results = $qb->getQuery()->getResult();
        if (!empty($results)) {
            $last = end($results);
            $this->lastId = $last->getId();
        }

        return $results;
    }
}
