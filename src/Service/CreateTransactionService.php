<?php

namespace App\Service;

use App\Entity\DamagedEducator;
use App\Entity\Transaction;
use App\Entity\UserDonor;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class CreateTransactionService
{
    public function __construct(private EntityManagerInterface $entityManager, private MailerInterface $mailer, private TransactionRepository $transactionRepository)
    {
    }

    public function isHoliday(): bool
    {
        $dates = ['01.01', '02.01', '06.01', '07.01', '15.01', '16.01', '17.01', '20.01', '01.05', '02.05', '06.05', '06.12', '11.11', '25.12', '31.12'];

        return in_array(date('d.m'), $dates);
    }

    public function getDamagedEducators(int $maxDonationAmount, int $minTransactionDonationAmount, array $parameters): array
    {
        $transactionStatuses = [
            Transaction::STATUS_NEW,
            Transaction::STATUS_WAITING_CONFIRMATION,
            Transaction::STATUS_CONFIRMED,
            Transaction::STATUS_EXPIRED,
        ];

        $queryString = '';
        $queryString .= 'WHERE de.status = :status';

        $queryParameters = [];
        $queryParameters['status'] = DamagedEducator::STATUS_NEW;

        if (!empty($parameters['schoolTypeId'])) {
            $queryString .= ' AND st.id = :schoolTypeId';
            $queryParameters['schoolTypeId'] = $parameters['schoolTypeId'];
        }

        if (!empty($parameters['schoolIds']) && is_array($parameters['schoolIds']) && count($parameters['schoolIds']) === count(array_filter($parameters['schoolIds'], 'is_numeric'))) {
            $queryString .= ' AND de.school_id IN ('.implode(',', $parameters['schoolIds']).')';
        }

        $stmt = $this->entityManager->getConnection()->executeQuery('
            SELECT de.id, de.period_id, de.account_number, de.amount, de.school_id
            FROM damaged_educator AS de
             INNER JOIN damaged_educator_period AS dep ON dep.id = de.period_id AND dep.processing = 1
             INNER JOIN school AS s ON s.id = de.school_id AND s.processing = 1
             INNER JOIN school_type AS st ON st.id = s.type_id
             '.$queryString.'
            ', $queryParameters);

        $items = [];
        foreach ($stmt->fetchAllAssociative() as $item) {
            if ($item['amount'] > $maxDonationAmount) {
                $item['amount'] = $maxDonationAmount;
            }

            $transactionSum = $this->transactionRepository->getSumAmountForAccountNumber($item['period_id'], $item['account_number'], $transactionStatuses);
            $item['remainingAmount'] = $item['amount'] - $transactionSum;
            if ($item['remainingAmount'] < $minTransactionDonationAmount) {
                continue;
            }

            $items[$item['id']] = $item;
        }

        // Sort by remaining amount
        uasort($items, function ($a, $b) {
            return $b['remainingAmount'] <=> $a['remainingAmount'];
        });

        return $items;
    }

    public function hasNotPaidTransactionsInLastDays(UserDonor $userDonor, int $days): bool
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('COUNT(t.id)')
            ->from(Transaction::class, 't')
            ->where('t.user = :user')
            ->andWhere('t.status = :status')
            ->andWhere('t.createdAt > :dateLimit')
            ->setParameter('user', $userDonor->getUser())
            ->setParameter('status', Transaction::STATUS_NOT_PAID)
            ->setParameter('dateLimit', new \DateTime('-'.$days.' days'));

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function getSumTransactions(UserDonor $userDonor): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('SUM(t.amount)')
            ->from(Transaction::class, 't')
            ->where('t.user = :user')
            ->andWhere('t.status IN (:statuses)')
            ->setParameter('user', $userDonor->getUser())
            ->setParameter('statuses', [
                Transaction::STATUS_NEW,
                Transaction::STATUS_WAITING_CONFIRMATION,
                Transaction::STATUS_CONFIRMED,
                Transaction::STATUS_EXPIRED,
            ]);

        if ($userDonor->isMonthly()) {
            $qb->andWhere('t.createdAt > :dateLimit')
                ->setParameter('dateLimit', new \DateTime('-30 days'));
        }

        $sum = (int) $qb->getQuery()->getSingleScalarResult();
        $sumNotPaidButConfirmed = $this->getSumNotPaidButConfirmedTransactions($userDonor);

        return $sum + $sumNotPaidButConfirmed;
    }

    private function getSumNotPaidButConfirmedTransactions(UserDonor $userDonor): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('SUM(t.amount)')
            ->from(Transaction::class, 't')
            ->where('t.user = :user')
            ->andWhere('t.status = :status')
            ->andWhere('t.userDonorConfirmed = 1')
            ->setParameter('user', $userDonor->getUser())
            ->setParameter('status', Transaction::STATUS_NOT_PAID);

        if ($userDonor->isMonthly()) {
            $qb->andWhere('t.createdAt > :dateLimit')
                ->setParameter('dateLimit', new \DateTime('-30 days'));
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function sendNewTransactionEmail(UserDonor $userDonor): void
    {
        $message = (new TemplatedEmail())
            ->to($userDonor->getUser()->getEmail())
            ->from(new Address('donatori@mrezasolidarnosti.org', 'Mreža Solidarnosti'))
            ->subject('Stigle su ti nove instrukcije za uplatu')
            ->htmlTemplate('email/donor-new-transactions.html.twig')
            ->context([
                'user' => $userDonor->getUser(),
            ]);

        try {
            $this->mailer->send($message);
        } catch (\Exception $exception) {
        }
    }

    public function sumTransactionsToEducator(UserDonor $userDonor, string $accountNumber): int
    {
        $transactionStatuses = [
            Transaction::STATUS_NEW,
            Transaction::STATUS_WAITING_CONFIRMATION,
            Transaction::STATUS_CONFIRMED,
            Transaction::STATUS_EXPIRED,
        ];

        $stmt = $this->entityManager->getConnection()->executeQuery('
            SELECT SUM(t.amount)
            FROM transaction AS t
            WHERE t.user_id = :userId
             AND t.account_number = :accountNumber
             AND t.status IN ('.implode(',', $transactionStatuses).')
             AND t.created_at > DATE(NOW() - INTERVAL 1 YEAR)
            ', [
            'userId' => $userDonor->getUser()->getId(),
            'accountNumber' => $accountNumber,
        ]);

        return (int) $stmt->fetchOne();
    }
}
