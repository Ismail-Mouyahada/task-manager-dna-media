<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Trouve les tâches d'un utilisateur (créées ou assignées).
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.createdBy = :user OR t.assignedTo = :user')
            ->setParameter('user', $user)
            ->orderBy('t.dueDate', 'ASC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches par statut.
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status], ['dueDate' => 'ASC']);
    }

    /**
     * Trouve les tâches en retard.
     */
    public function findOverdueTasks(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.dueDate < :now')
            ->andWhere('t.status != :completed')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('completed', Task::STATUS_COMPLETED)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tâches par utilisateur et statut.
     */
    public function findByUserAndStatus(User $user, string $status): array
    {
        return $this->createQueryBuilder('t')
            ->where('(t.createdBy = :user OR t.assignedTo = :user) AND t.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
