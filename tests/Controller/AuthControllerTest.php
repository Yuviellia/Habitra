<?php
namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase {
    private $client;
    private $em;
    private $hasher;

    protected function setUp(): void {
        $this->client = static::createClient();
        $this->em    = self::getContainer()->get(EntityManagerInterface::class);
        $this->hasher= self::getContainer()->get(UserPasswordHasherInterface::class);

        $conn = $this->em->getConnection();

        $schemaManager = $conn->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        foreach ($tables as $table) {
            $conn->executeStatement("DROP TABLE IF EXISTS \"$table\" CASCADE");
        }

        $sql = file_get_contents(__DIR__ . '/../../database.sql');
        $conn->executeStatement($sql);
    }

    public function testLoginRequiresEmailAndPassword(): void {
        $this->client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json']
            , json_encode([]));

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Email and password required', $data['message']);
    }

    public function testLoginInvalidEmail(): void {
        $this->client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json']
            , json_encode(['email'=>'lmao@example.com','password'=>'pp']));

        $response = $this->client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Invalid credentials [email]', $data['message']);
    }

    public function testLoginInvalidPassword(): void {
        $details = new UserDetails();
        $details->setName('lalala');
        $details->setSurname('la');
        $this->em->persist($details);

        $user = new User();
        $user->setEmail('usercostam@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'haslo'));
        $user->setUserDetails($details);
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE'=>'application/json'
        ], json_encode(['email'=>'usercostam@example.com','password'=>'NIEhaslo']));

        $response = $this->client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Invalid credentials [password]', $data['message']);
    }

    public function testLoginSuccess(): void {
        $details = new UserDetails();
        $details->setName('lalala');
        $details->setSurname('la');
        $this->em->persist($details);

        $user = new User();
        $user->setEmail('usercostam@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'haslo'));
        $user->setUserDetails($details);
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('POST','/api/login',[],[],['CONTENT_TYPE'=>'application/json']
            , json_encode(['email'=>'usercostam@example.com','password'=>'haslo']));

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Login successful', $data['message']);
        $this->assertArrayHasKey('token', $data);
    }
}
