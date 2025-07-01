<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskCreation(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('This is a test task');
        $task->setStatus(Task::STATUS_IN_PROGRESS);
        $task->setPriority(Task::PRIORITY_HIGH);

        $this->assertSame('Test Task', $task->getTitle());
        $this->assertSame('This is a test task', $task->getDescription());
        $this->assertSame(Task::STATUS_IN_PROGRESS, $task->getStatus());
        $this->assertSame(Task::PRIORITY_HIGH, $task->getPriority());
        $this->assertSame('En cours', $task->getStatusLabel());
        $this->assertSame('Élevée', $task->getPriorityLabel());
        $this->assertFalse($task->isCompleted());
        $this->assertFalse($task->isOverdue());
    }

    public function testTaskOverdue(): void
    {
        $task = new Task();
        $task->setDueDate(new \DateTimeImmutable('-1 day'));
        $task->setStatus(Task::STATUS_IN_PROGRESS);

        $this->assertTrue($task->isOverdue());
    }

    public function testTaskAssignment(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $task = new Task();
        $task->setAssignedTo($user);

        $this->assertSame($user, $task->getAssignedTo());
    }
}
