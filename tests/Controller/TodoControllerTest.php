<?php

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\UserDetails;
use App\Entity\Todo;

class TodoControllerTest extends WebTestCase {
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

    private function createUser(string $email = 'tds@example.com'): User {
        $details = new UserDetails();
        $details->setName('JAAAA');
        $details->setSurname('DDDD');
        $this->em->persist($details);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword('i');
        $user->setUserDetails($details);
        $user->setRoles('ROLE_USER');
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
    public function testGetTodosUserHasNone(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $this->client->request('GET', "/api/todos", [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('No todos found for this user', json_decode($response->getContent(), true)['message']);
    }
    public function testGetTodosUserHasSome(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $todo = new Todo();
        $todo->setUser($user);
        $todo->setTask('rurururur');
        $todo->setCreatedAt(new \DateTime());
        $this->em->persist($todo);
        $this->em->flush();

        $this->client->request('GET', "/api/todos", [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $todos = json_decode($response->getContent(), true);
        $this->assertCount(1, $todos);
        $this->assertSame('rurururur', $todos[0]['task']);
    }
    public function testCreateTodoMissingTask(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $this->client->request('POST', "/api/todos", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode([]));

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Task is required', json_decode($response->getContent(), true)['message']);
    }
    public function testCreateTodoUnauthorised(): void {
        $this->client->request('POST', '/api/todos', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['task' => 'Do laundry']));

        $response = $this->client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
    }
    public function testCreateTodoSuccess(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $payload = json_encode(['task' => 'wwwww']);

        $this->client->request('POST', "/api/todos", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], $payload);

        $response = $this->client->getResponse();
        $this->assertSame(201, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Todo created successfully', $data['message']);
        $this->assertSame($user->getId(), $data['todo']['userId']);
        $this->assertSame('wwwww', $data['todo']['task']);
        $this->assertNotEmpty($data['todo']['createdAt']);
    }
}