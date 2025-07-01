<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testTaskIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Tâches');
    }

    public function testTaskApi(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks/api');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('tasks', $data['data']);
    }
}
