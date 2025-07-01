<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des utilisateurs
        $users = $this->createUsers($manager);

        // Création des tâches
        $this->createTasks($manager, $users);

        $manager->flush();

        echo "Fixtures chargées avec succès !\n";
        echo "Utilisateurs créés :\n";
        foreach ($users as $user) {
            echo "   - {$user->getEmail()} (mot de passe: password123)\n";
        }
    }

    private function createUsers(ObjectManager $manager): array
    {
        $usersData = [
            [
                'email' => 'admin@taskmanager.com',
                'firstName' => 'Admin',
                'lastName' => 'System',
                'roles' => ['ROLE_ADMIN', 'ROLE_USER']
            ],
            [
                'email' => 'john.doe@nda-media.com',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'roles' => ['ROLE_USER']
            ],
            [
                'email' => 'jane.smith@nda-media.com',
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'roles' => ['ROLE_USER']
            ],
            [
                'email' => 'pierre.martin@nda-media.com',
                'firstName' => 'Pierre',
                'lastName' => 'Martin',
                'roles' => ['ROLE_USER']
            ],
            [
                'email' => 'marie.durand@nda-media.com',
                'firstName' => 'Marie',
                'lastName' => 'Durand',
                'roles' => ['ROLE_USER']
            ]
        ];

        $users = [];
        foreach ($usersData as $userData) {
            $user = new User();
            $user->setEmail($userData['email'])
                 ->setFirstName($userData['firstName'])
                 ->setLastName($userData['lastName'])
                 ->setRoles($userData['roles']);

            // Hachage du mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    private function createTasks(ObjectManager $manager, array $users): void
    {
        $tasksData = [
            [
                'title' => 'Développer l\'API REST',
                'description' => 'Créer les endpoints pour la gestion des tâches avec authentification JWT',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => Task::PRIORITY_HIGH,
                'dueDate' => new \DateTimeImmutable('+7 days')
            ],
            [
                'title' => 'Rédiger la documentation technique',
                'description' => 'Documenter l\'architecture de l\'application et les endpoints API',
                'status' => Task::STATUS_PENDING,
                'priority' => Task::PRIORITY_MEDIUM,
                'dueDate' => new \DateTimeImmutable('+14 days')
            ],
            [
                'title' => 'Optimiser les performances',
                'description' => 'Analyser et optimiser les requêtes de base de données',
                'status' => Task::STATUS_PENDING,
                'priority' => Task::PRIORITY_LOW,
                'dueDate' => new \DateTimeImmutable('+21 days')
            ],
            [
                'title' => 'Mettre en place les tests unitaires',
                'description' => 'Écrire les tests pour les entités et services principaux',
                'status' => Task::STATUS_COMPLETED,
                'priority' => Task::PRIORITY_HIGH,
                'dueDate' => new \DateTimeImmutable('-3 days')
            ],
            [
                'title' => 'Configurer l\'environnement de production',
                'description' => 'Préparer les serveurs et la configuration pour la mise en production',
                'status' => Task::STATUS_PENDING,
                'priority' => Task::PRIORITY_URGENT,
                'dueDate' => new \DateTimeImmutable('+3 days')
            ],
            [
                'title' => 'Révision du code',
                'description' => 'Faire une revue de code complète avant la livraison',
                'status' => Task::STATUS_PENDING,
                'priority' => Task::PRIORITY_MEDIUM,
                'dueDate' => new \DateTimeImmutable('+10 days')
            ],
            [
                'title' => 'Formation équipe',
                'description' => 'Former l\'équipe sur les nouvelles fonctionnalités',
                'status' => Task::STATUS_CANCELLED,
                'priority' => Task::PRIORITY_LOW,
                'dueDate' => new \DateTimeImmutable('-1 day')
            ],
            [
                'title' => 'Mise à jour sécurité',
                'description' => 'Appliquer les derniers correctifs de sécurité',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => Task::PRIORITY_URGENT,
                'dueDate' => new \DateTimeImmutable('+1 day')
            ]
        ];

        foreach ($tasksData as $index => $taskData) {
            $task = new Task();
            $task->setTitle($taskData['title'])
                 ->setDescription($taskData['description'])
                 ->setStatus($taskData['status'])
                 ->setPriority($taskData['priority'])
                 ->setDueDate($taskData['dueDate']);

            // Assignation aléatoire du créateur
            $createdBy = $users[array_rand($users)];
            $task->setCreatedBy($createdBy);

            // Assignation aléatoire d'un responsable (80% de chance)
            if (random_int(1, 100) <= 80) {
                $assignedTo = $users[array_rand($users)];
                $task->setAssignedTo($assignedTo);
            }

            // Ajuster la date de création pour certaines tâches
            if ($index % 3 === 0) {
                $task->setCreatedAt(new \DateTimeImmutable('-' . random_int(1, 30) . ' days'));
            }

            $manager->persist($task);
        }
    }
}
