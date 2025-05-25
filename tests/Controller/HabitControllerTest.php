<?php
namespace App\Tests\Controller;

use App\Entity\Marked;
use App\Entity\User;
use App\Entity\UserDetails;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


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
        $user->setRoles('ROLE_USER');
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function testGetUserHabitsNotFound(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $this->client->request('GET', '/api/habits', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('No habits found for this user', json_decode($response->getContent(), true)['message']);
    }

    public function testCreateHabitMissingName(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $this->client->request('POST', "/api/habits", [], [], [
            'CONTENT_TYPE'=>'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode([]));
        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Habit name is required', json_decode($response->getContent(), true)['message']);
    }

    public function testCreateHabitUnauthorised(): void {
        $this->client->request('POST', '/api/habits', [], [], ['CONTENT_TYPE'=>'application/json'], json_encode(['name'=>'Read']));
        $response = $this->client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testCreateHabitSuccess(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);
        $this->client->request('POST', "/api/habits", [], [], [
            'CONTENT_TYPE'=>'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode(['name'=>'Read']));
        $response = $this->client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Habit created', $data['message']);
        $this->assertSame('Read', $data['habit']['name']);
    }

    public function testMarkHabitInvalidDate(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $habit = new Tag();
        $habit->setUser($user);
        $habit->setName('Run');
        $habit->setCreatedAt(new \DateTime());
        $this->em->persist($habit);
        $this->em->flush();

        $this->client->request('POST', "/api/habits/{$habit->getId()}/mark", [], [], [
            'CONTENT_TYPE'=>'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode(['date'=>'invalid-date']));
        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid date format', json_decode($response->getContent(), true)['message']);
    }

    public function testMarkHabitUserHabit(): void {
        $user = $this->createUser('1@example.com');
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $otherUser = $this->createUser('2@example.com');
        $otherUser->setEmail('ooo@example.com');
        $this->em->flush();

        $habit = new Tag();
        $habit->setUser($otherUser);
        $habit->setName('Mismatch');
        $habit->setCreatedAt(new \DateTime());
        $this->em->persist($habit);
        $this->em->flush();

        $this->client->request('POST', "/api/habits/{$habit->getId()}/mark", [], [], [
            'CONTENT_TYPE'=>'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode(['date'=>'2025-04-21']));
        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Habit not found for this user', json_decode($response->getContent(), true)['message']);
    }

    public function testDeleteHabitNotFound(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $this->client->request('DELETE', '/api/habits/99999', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Habit not found for this user', json_decode($response->getContent(), true)['message']);
    }

    public function testDeleteHabitUnauthorized(): void {
        $owner = $this->createUser('owner@example.com');
        $intruder = $this->createUser('intruder@example.com');
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($intruder);

        $habit = new Tag();
        $habit->setUser($owner);
        $habit->setName('Private habit');
        $this->em->persist($habit);
        $this->em->flush();

        $this->client->request('DELETE', '/api/habits/' . $habit->getId(), [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Habit not found for this user', json_decode($response->getContent(), true)['message']);
    }

    public function testDeleteHabitSuccess(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $habit = new Tag();
        $habit->setUser($user);
        $habit->setName('Test habit');
        $this->em->persist($habit);
        $this->em->flush();

        $habitId = $habit->getId();

        $this->client->request('DELETE', '/api/habits/' . $habitId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Habit and all associated marks deleted', json_decode($response->getContent(), true)['message']);

        $deletedHabit = $this->em->getRepository(Tag::class)->find($habitId);
        $this->assertNull($deletedHabit);
    }

    public function testMarkHabitNotOwnedByUser(): void {
        $owner = $this->createUser('owner@example.com');
        $otherUser = $this->createUser('intruder@example.com');
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($otherUser);

        $habit = new Tag();
        $habit->setUser($owner);
        $habit->setName('Secret habit');
        $this->em->persist($habit);
        $this->em->flush();

        $this->client->request('POST', '/api/habits/' . $habit->getId() . '/mark', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['date' => '2025-05-01']));

        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Habit not found for this user', json_decode($response->getContent(), true)['message']);
    }
    public function testMarkHabitInvalidDateFormat(): void {
        $user = $this->createUser();
        $habit = new Tag();
        $habit->setUser($user);
        $habit->setName('Read book');
        $this->em->persist($habit);
        $this->em->flush();

        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $this->client->request('POST', '/api/habits/' . $habit->getId() . '/mark', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['date' => 'not-a-date']));

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Invalid date format', json_decode($response->getContent(), true)['message']);
    }

    public function testMarkHabitMissingDate(): void {
        $user = $this->createUser();
        $habit = new Tag();
        $habit->setUser($user);
        $habit->setName('Journal');
        $this->em->persist($habit);
        $this->em->flush();

        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $this->client->request('POST', '/api/habits/' . $habit->getId() . '/mark', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Marked date is required', json_decode($response->getContent(), true)['message']);
    }

    public function testMarkAndUnmarkHabit(): void {
        $user = $this->createUser();
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $habit = new Tag();
        $habit->setUser($user);
        $habit->setName('Water plants');
        $this->em->persist($habit);
        $this->em->flush();

        $habitId = $habit->getId();
        $date = '2025-05-01';

        $url = '/api/habits/' . $habitId . '/mark';
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ];
        $body = json_encode(['date' => $date]);

        $this->client->request('POST', $url, [], [], $headers, $body);
        $response = $this->client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Habit marked for date', $data['message']);
        $this->assertSame($date, $data['marked']['date']);

        $marked = $this->em->getRepository(Marked::class)->findOneBy([
            'tag' => $habit,
            'date' => new \DateTime($date),
        ]);
        $this->assertNotNull($marked, 'Marked date should exist after marking');

        $this->client->request('POST', $url, [], [], $headers, $body);
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Marked date removed', $data['message']);

        $marked = $this->em->getRepository(Marked::class)->findOneBy([
            'tag' => $habit,
            'date' => new \DateTime($date),
        ]);
        $this->assertNull($marked, 'Marked date should be removed after unmarking');
    }

}
