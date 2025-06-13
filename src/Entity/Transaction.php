<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: '`transaction`')]
#[ORM\Index(name: 'idx_status_created_at', columns: ['status', 'created_at'])]
#[ORM\Index(name: 'idx_account_number', columns: ['account_number', 'status'])]
#[ORM\Index(name: 'idx_damaged_educator', columns: ['damaged_educator_id', 'status'])]
#[ORM\Index(name: 'idx_remaining_amount', columns: ['user_id', 'status', 'created_at'])]
#[ORM\Index(name: 'idx_not_paid', columns: ['user_id', 'status', 'user_donor_confirmed'])]
#[ORM\Index(name: 'idx_user_total_amount', columns: ['user_id', 'account_number', 'status', 'created_at'])]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    public const STATUS_NEW = 1;
    public const STATUS_WAITING_CONFIRMATION = 2;
    public const STATUS_CONFIRMED = 3;
    public const STATUS_CANCELLED = 4;
    public const STATUS_NOT_PAID = 5;
    public const STATUS_EXPIRED = 6;
    public const STATUS_PAID = 7;

    public const STATUS = [
        self::STATUS_NEW => 'TransactionWaitingPayment',
        self::STATUS_WAITING_CONFIRMATION => 'TransactionWaitingConfirmation',
        self::STATUS_EXPIRED => 'TransactionExpired',
        self::STATUS_NOT_PAID => 'TransactionNotPaid',
        self::STATUS_CONFIRMED => 'TransactionConfirmed',
        self::STATUS_CANCELLED => 'TransactionCancelled',
        self::STATUS_PAID => 'TransactionPaid',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DamagedEducator $damagedEducator = null;

    #[ORM\Column(length: 50)]
    private ?string $accountNumber = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column]
    private ?int $status = self::STATUS_NEW;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $statusComment = null;

    #[ORM\Column]
    private ?bool $userDonorConfirmed = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userDonorFirstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userDonorLastName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDamagedEducator(): ?DamagedEducator
    {
        return $this->damagedEducator;
    }

    public function setDamagedEducator(?DamagedEducator $damagedEducator): static
    {
        $this->damagedEducator = $damagedEducator;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusComment(): ?string
    {
        return $this->statusComment;
    }

    public function setStatusComment(?string $statusComment): static
    {
        $this->statusComment = $statusComment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): static
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function allowConfirmPayment(): bool
    {
        if (self::STATUS_NEW == $this->getStatus()) {
            return true;
        }

        return false;
    }

    public function allowShowPrint(): bool
    {
        if (self::STATUS_NEW == $this->getStatus()) {
            return true;
        }

        return false;
    }

    public function allowShowQR(): bool
    {
        if (self::STATUS_NEW == $this->getStatus()) {
            return true;
        }

        return false;
    }

    public function isStatusNew(): bool
    {
        return self::STATUS_NEW === $this->status;
    }

    public function isStatusConfirmed(): bool
    {
        return self::STATUS_CONFIRMED === $this->status;
    }

    public function isMaskInformation(): bool
    {
        if (self::STATUS_NOT_PAID && $this->isUserDonorConfirmed()) {
            return false;
        }

        if (in_array($this->getStatus(), [self::STATUS_CANCELLED, self::STATUS_EXPIRED, self::STATUS_NOT_PAID])) {
            return true;
        }

        return false;
    }

    public function allowToChangeStatus(): bool
    {
        $days = $this->getUpdatedAt()->diff(new \DateTime())->days;
        $hours = $this->getUpdatedAt()->diff(new \DateTime())->h;

        if (self::STATUS_WAITING_CONFIRMATION == $this->getStatus() && 0 == $days && $hours <= 6) {
            return false;
        }

        if (in_array($this->getStatus(), [self::STATUS_EXPIRED, self::STATUS_WAITING_CONFIRMATION])) {
            return true;
        }

        if (in_array($this->getStatus(), [self::STATUS_CONFIRMED, self::STATUS_NOT_PAID]) && $days < 7) {
            return true;
        }

        return false;
    }

    public function getReferenceCode(): string
    {
        return 'MS'.$this->getId();
    }

    public function isUserDonorConfirmed(): ?bool
    {
        return $this->userDonorConfirmed;
    }

    public function setUserDonorConfirmed(bool $userDonorConfirmed): static
    {
        $this->userDonorConfirmed = $userDonorConfirmed;

        return $this;
    }

    public function getStatusConfirmed(): int
    {
        return self::STATUS_CONFIRMED;
    }

    public function getUserDonorFirstName(): ?string
    {
        return $this->userDonorFirstName;
    }

    public function setUserDonorFirstName(?string $userDonorFirstName): static
    {
        $this->userDonorFirstName = $userDonorFirstName;

        return $this;
    }

    public function getUserDonorLastName(): ?string
    {
        return $this->userDonorLastName;
    }

    public function setUserDonorLastName(?string $userDonorLastName): static
    {
        $this->userDonorLastName = $userDonorLastName;

        return $this;
    }

    public function getUserDonorFullName(): string
    {
        if ($this->userDonorFirstName && $this->userDonorLastName) {
            return $this->userDonorFirstName.' '.$this->userDonorLastName;
        }

        return '-';
    }
}
