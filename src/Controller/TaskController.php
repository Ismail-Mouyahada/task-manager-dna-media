<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tasks', name: 'task_')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository,
        private UserRepository $userRepository
    ) {}

    /**
     * Page d'accueil avec affichage des tâches
     */
    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $sortBy = $request->query->get('sort', 'dueDate');
        $order = $request->query->get('order', 'ASC');

        // Récupération des tâches avec filtres
        if ($status) {
            $tasks = $this->taskRepository->findByStatus($status);
        } else {
            $tasks = $this->taskRepository->findBy([], [$sortBy => $order]);
        }

        // Statistiques
        $stats = $this->getTaskStatistics();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'stats' => $stats,
            'currentStatus' => $status,
            'statusChoices' => Task::getStatusChoices(),
            'priorityChoices' => Task::getPriorityChoices(),
        ]);
    }

    /**
     * Affichage JSON des données (pour tests)
     */
    #[Route('/api', name: 'api', methods: ['GET'])]
    public function api(): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();
        $users = $this->userRepository->findAll();

        $data = [
            'success' => true,
            'message' => 'Données récupérées avec succès',
            'data' => [
                'tasks' => $this->serializeTasks($tasks),
                'users' => $this->serializeUsers($users),
                'statistics' => $this->getTaskStatistics()
            ],
            'meta' => [
                'total_tasks' => count($tasks),
                'total_users' => count($users),
                'generated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
            ]
        ];

        return $this->json($data);
    }

    /**
     * Statistiques des tâches
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        return $this->json($this->getTaskStatistics());
    }

    /**
     * Tâches en retard
     */
    #[Route('/overdue', name: 'overdue', methods: ['GET'])]
    public function overdue(): JsonResponse
    {
        $overdueTasks = $this->taskRepository->findOverdueTasks();

        return $this->json([
            'success' => true,
            'count' => count($overdueTasks),
            'tasks' => $this->serializeTasks($overdueTasks)
        ]);
    }

    /**
     * Détail d'une tâche
     */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * Calcule les statistiques des tâches
     */
    private function getTaskStatistics(): array
    {
        $allTasks = $this->taskRepository->findAll();

        $stats = [
            'total' => count($allTasks),
            'by_status' => [
                Task::STATUS_PENDING => 0,
                Task::STATUS_IN_PROGRESS => 0,
                Task::STATUS_COMPLETED => 0,
                Task::STATUS_CANCELLED => 0,
            ],
            'by_priority' => [
                Task::PRIORITY_LOW => 0,
                Task::PRIORITY_MEDIUM => 0,
                Task::PRIORITY_HIGH => 0,
                Task::PRIORITY_URGENT => 0,
            ],
            'overdue' => 0,
            'completion_rate' => 0
        ];

        foreach ($allTasks as $task) {
            $stats['by_status'][$task->getStatus()]++;
            $stats['by_priority'][$task->getPriority()]++;

            if ($task->isOverdue()) {
                $stats['overdue']++;
            }
        }

        // Taux de completion
        if ($stats['total'] > 0) {
            $stats['completion_rate'] = round(
                ($stats['by_status'][Task::STATUS_COMPLETED] / $stats['total']) * 100,
                2
            );
        }

        return $stats;
    }

    /**
     * Sérialise les tâches pour JSON
     */
    private function serializeTasks(array $tasks): array
    {
        return array_map(function (Task $task) {
            return [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'status_label' => $task->getStatusLabel(),
                'priority' => $task->getPriority(),
                'priority_label' => $task->getPriorityLabel(),
                'due_date' => $task->getDueDate()?->format('Y-m-d H:i:s'),
                'is_overdue' => $task->isOverdue(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'created_by' => $task->getCreatedBy() ? [
                    'id' => $task->getCreatedBy()->getId(),
                    'name' => $task->getCreatedBy()->getFullName(),
                    'email' => $task->getCreatedBy()->getEmail()
                ] : null,
                'assigned_to' => $task->getAssignedTo() ? [
                    'id' => $task->getAssignedTo()->getId(),
                    'name' => $task->getAssignedTo()->getFullName(),
                    'email' => $task->getAssignedTo()->getEmail()
                ] : null,
            ];
        }, $tasks);
    }

    /**
     * Sérialise les utilisateurs pour JSON
     */
    private function serializeUsers(array $users): array
    {
        return array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'full_name' => $user->getFullName(),
                'created_tasks_count' => count($user->getCreatedTasks()),
                'assigned_tasks_count' => count($user->getAssignedTasks()),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $users);
    }
}
