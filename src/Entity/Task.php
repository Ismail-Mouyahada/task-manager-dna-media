<?php

// src/Entity/Task.php

namespace App\Entity;

use App\Repository\TaskRepository;
use App\Service\TaskNotificationService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER') and (object.getCreatedBy() == user or object.getAssignedTo() == user)"),
        new Put(security: "is_granted('ROLE_USER') and object.getCreatedBy() == user"),
        new Patch(security: "is_granted('ROLE_USER') and (object.getCreatedBy() == user or object.getAssignedTo() == user)"),
        new Delete(security: "is_granted('ROLE_USER') and object.getCreatedBy() == user"),
    ],
    normalizationContext: ['groups' => ['task:read']],
    denormalizationContext: ['groups' => ['task:write']],
)]
#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Task
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';







    #[Groups(['task:read', 'task:write'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['task:read', 'task:write'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide.')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.')]
    private ?string $title = null;

    #[Groups(['task:read', 'task:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['task:read', 'task:write'])]
    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ], message: 'Statut invalide.')]
    private ?string $status = self::STATUS_PENDING;

    #[Groups(['task:read', 'task:write'])]
    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ], message: 'Priorité invalide.')]
    private ?string $priority = self::PRIORITY_MEDIUM;

    #[Groups(['task:read', 'task:write'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[Groups(['task:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['task:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;



    #[ORM\ManyToOne(inversedBy: 'createdTasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;


    #[ORM\ManyToOne(inversedBy: 'assignedTasks')]
    private ?User $assignedTo = null;
    #[ORM\PostPersist]

    #[ORM\PostUpdate]
    public function sendAssignmentNotification(TaskNotificationService $notificationService): void
    {
        if ($this->assignedTo !== null) {
            $notificationService->sendTaskAssignedNotification($this);
        }
    }


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PENDING;
        $this->priority = self::PRIORITY_MEDIUM;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        $this->updateTimestamp();

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        $this->updateTimestamp();

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updateTimestamp();

        // Marquer comme terminé si le statut est "completed"
        if (self::STATUS_COMPLETED === $status && null === $this->completedAt) {
            $this->completedAt = new \DateTimeImmutable();
        } elseif (self::STATUS_COMPLETED !== $status) {
            $this->completedAt = null;
        }

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;
        $this->updateTimestamp();

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;
        $this->updateTimestamp();

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): static
    {
        $this->assignedTo = $assignedTo;
        $this->updateTimestamp();

        return $this;
    }

    /**
     * Vérifie si la tâche est en retard.
     */
    public function isOverdue(): bool
    {
        return null !== $this->dueDate
            && $this->dueDate < new \DateTimeImmutable()
            && self::STATUS_COMPLETED !== $this->status;
    }

    /**
     * Vérifie si la tâche est terminée.
     */
    public function isCompleted(): bool
    {
        return self::STATUS_COMPLETED === $this->status;
    }

    /**
     * Retourne le statut traduit.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_COMPLETED => 'Terminée',
            self::STATUS_CANCELLED => 'Annulée',
            default => 'Inconnu'
        };
    }

    /**
     * Retourne la priorité traduite.
     */
    public function getPriorityLabel(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_MEDIUM => 'Moyenne',
            self::PRIORITY_HIGH => 'Élevée',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Inconnu'
        };
    }

    /**
     * Retourne toutes les valeurs de statut possibles.
     */
    public static function getStatusChoices(): array
    {
        return [
            'En attente' => self::STATUS_PENDING,
            'En cours' => self::STATUS_IN_PROGRESS,
            'Terminée' => self::STATUS_COMPLETED,
            'Annulée' => self::STATUS_CANCELLED,
        ];
    }

    /**
     * Retourne toutes les valeurs de priorité possibles.
     */
    public static function getPriorityChoices(): array
    {
        return [
            'Faible' => self::PRIORITY_LOW,
            'Moyenne' => self::PRIORITY_MEDIUM,
            'Élevée' => self::PRIORITY_HIGH,
            'Urgente' => self::PRIORITY_URGENT,
        ];
    }

    /**
     * Met à jour automatiquement le timestamp de modification.
     */
    #[ORM\PreUpdate]
    private function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
