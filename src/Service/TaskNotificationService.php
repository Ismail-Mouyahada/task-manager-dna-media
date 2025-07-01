<?php

namespace App\Service;

use App\Entity\Task;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class TaskNotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $senderEmail,
        private string $senderName
    ) {
    }

    public function sendTaskAssignedNotification(Task $task): void
    {
        if (!$task->getAssignedTo()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to(new Address($task->getAssignedTo()->getEmail(), $task->getAssignedTo()->getFullName()))
            ->subject(sprintf('Nouvelle tâche assignée : %s', $task->getTitle()))
            ->htmlTemplate('emails/task_assigned.html.twig')
            ->context([
                'task' => $task,
                'assignee' => $task->getAssignedTo(),
                'creator' => $task->getCreatedBy(),
            ]);

        $this->mailer->send($email);
    }
}
