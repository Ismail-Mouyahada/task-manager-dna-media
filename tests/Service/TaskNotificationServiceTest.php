<?php

namespace App\Tests\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Service\TaskNotificationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;

class TaskNotificationServiceTest extends KernelTestCase
{
    public function testSendTaskAssignedNotification(): void
    {
        self::bootKernel();

        // Mock du Mailer
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
               ->method('send');

        $service = new TaskNotificationService(
            $mailer,
            'no-reply@taskmanager.com',
            'Task Manager'
        );

        // Création d'une tâche avec un utilisateur assigné
        $creator = new User();
        $creator->setEmail('creator@example.com');
        $creator->setFirstName('Creator');
        $creator->setLastName('User');

        $assignee = new User();
        $assignee->setEmail('assignee@example.com');
        $assignee->setFirstName('Assignee');
        $assignee->setLastName('User');

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setCreatedBy($creator);
        $task->setAssignedTo($assignee);

        $service->sendTaskAssignedNotification($task);
    }
}
