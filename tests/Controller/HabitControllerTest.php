<?php
namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserDetails;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HabitControllerTest extends WebTestCase {
    private $client;
    private $em;

    protected function setUp(): void {
        $this->client = static::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $conn = $this->em->getConnection();
        $schemaManager = $conn->createSchemaManager();
        $tables = $schemaManager->listTableNames();
        foreach ($tables as $table) {
            $conn->executeStatement("DROP TABLE IF EXISTS \"$table\" CASCADE");
        }

        $sql = file_get_contents(__DIR__ . '/../../database.sql');
        $conn->executeStatement($sql);
    }

    private function createUser(string $email = 'hehe@example.com'): User {
        $details = new UserDetails();
        $details->setName('Test');
        $details->setSurname('ing');
        $this->em->persist($details);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword('irrelevant');
        $user->setUserDetails($details);
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function testGetUserHabitsNotFound(): void {
        $this->client->request('GET', '/api/user/999/habits');
        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('No habits found for this user', json_decode($response->getContent(), true)['message']);
    }

    public function testCreateHabitMissingName(): void {
        $user = $this->createUser();
        $this->client->request('POST', "/api/user/{$user->getId()}/habits", [], [], ['CONTENT_TYPE'=>'application/json'], json_encode([]));
        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Habit name is required', json_decode($response->getContent(), true)['message']);
    }

    public function testCreateHabitUserNotFound(): void {
        $this->client->request('POST', '/api/user/999/habits', [], [], ['CONTENT_TYPE'=>'application/json'], json_encode(['name'=>'Read']));
        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('User not found', json_decode($response->getContent(), true)['message']);
    }

    public function testCreateHabitSuccess(): void {
        $user = $this->createUser();
        $this->client->request('POST', "/api/user/{$user->getId()}/habits", [], [], ['CONTENT_TYPE'=>'application/json'], json_encode(['name'=>'Read']));
        $response = $this->client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Habit created', $data['message']);
        $this->assertSame('Read', $data['habit']['name']);
    }

    public function testMarkHabitInvalidDate(): void {
        $user = $this->createUser();
        $habit = new Tag();
        $habit->setUser($user);
        $habit->setName('Run');
        $habit->setCreatedAt(new \DateTime());
        $this->em->persist($habit);
        $this->em->flush();

        $this->client->request('POST', "/api/user/{$user->getId()}/habits/{$habit->getId()}/mark", [], [], ['CONTENT_TYPE'=>'application/json'], json_encode(['date'=>'invalid-date']));
        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid date format', json_decode($response->getContent(), true)['message']);
    }

    public function testMarkHabitUserMismatch(): void {
        $user = $this->createUser('1@example.com');
        $otherUser = $this->createUser('2@example.com');
        $otherUser->setEmail('ooo@example.com');
        $this->em->flush();

        $habit = new Tag();
        $habit->setUser($otherUser);
        $habit->setName('Mismatch');
        $habit->setCreatedAt(new \DateTime());
        $this->em->persist($habit);
        $this->em->flush();

        $this->client->request('POST', "/api/user/{$user->getId()}/habits/{$habit->getId()}/mark", [], [], ['CONTENT_TYPE'=>'application/json'], json_encode(['date'=>'2025-04-21']));
        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Habit not found for this user', json_decode($response->getContent(), true)['message']);
    }

    public function testMarkHabitSuccess(): void {
        $user = $this->createUser();
        $habit = new Tag();
        $habit->setUser($user);
        $habit->setName('Stretch');
        $habit->setCreatedAt(new \DateTime());
        $this->em->persist($habit);
        $this->em->flush();

        $this->client->request('POST', "/api/user/{$user->getId()}/habits/{$habit->getId()}/mark", [], [], ['CONTENT_TYPE'=>'application/json'], json_encode(['date'=>'2025-04-21']));
        $response = $this->client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Habit marked for date', $data['message']);
        $this->assertSame('2025-04-21', $data['marked']['date']);
    }
}
