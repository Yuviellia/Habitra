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
        $container = self::getContainer();

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

    // LOGIN
    public function testLoginRequiresEmailAndPassword(): void {
        $this->client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json']
            , json_encode([]));

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Invalid JSON.', $data['error']);
    }

    public function testLoginInvalidEmail(): void {
        $this->client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json']
            , json_encode(['email'=>'lmao@example.com','password'=>'pp']));

        $response = $this->client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Invalid credentials.', $data['message']);
    }

    public function testLoginInvalidPassword(): void {
        $details = new UserDetails();
        $details->setName('lalala');
        $details->setSurname('la');
        $this->em->persist($details);

        $user = new User();
        $user->setEmail('usercostam@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'haslo'));
        $user->setRoles('ROLE_USER');
        $user->setUserDetails($details);
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE'=>'application/json'
        ], json_encode(['email'=>'usercostam@example.com','password'=>'NIEhaslo']));

        echo $this->client->getResponse()->getContent();

        $response = $this->client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Invalid credentials.', $data['message']);
    }

    public function testLoginSuccess(): void {
        $details = new UserDetails();
        $details->setName('lalala');
        $details->setSurname('la');
        $this->em->persist($details);

        $user = new User();
        $user->setEmail('usercostam@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'haslo'));
        $user->setRoles('ROLE_USER');
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
        $this->assertArrayHasKey('token', $data);
    }


    // REGISTER
    public function testRegisterMissingRequiredFields(): void {
        $this->client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json']
            , json_encode([
                'email' => 'rrrr@example.com',
                'password' => 'pass'
            ]));

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Name, surname, email, and password required', $data['message']);
    }

    public function testRegisterEmailAlreadyExists(): void {
        $details = new UserDetails();
        $details->setName('Jasper');
        $details->setSurname('Bee');
        $this->em->persist($details);

        $user = new User();
        $user->setEmail('beelicious@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'beez'));
        $user->setUserDetails($details);
        $user->setRoles('ROLE_USER');
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'beelicious@example.com',
            'password' => 'beez',
            'name' => 'Jasper',
            'surname' => 'Bee'
        ]));

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Email already registered', $data['message']);
    }

    public function testRegisterSuccess(): void {
        $this->client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'pog@example.com',
            'password' => 'pogchamp',
            'name' => 'Pog',
            'surname' => 'Champ',
            'phone' => '1234567890'
        ]));

        $response = $this->client->getResponse();
        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('User registered successfully', $data['message']);
        $this->assertSame('pog@example.com', $data['user']['email']);
        $this->assertSame('Pog', $data['user']['name']);
        $this->assertSame('Champ', $data['user']['surname']);
        $this->assertSame('1234567890', $data['user']['phone']);
    }

    public function testGetNonAdminUsers(): void {
        $adminDetails = new UserDetails();
        $adminDetails->setName('Admin');
        $adminDetails->setSurname('User');
        $this->em->persist($adminDetails);

        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'adminpass'));
        $admin->setRoles('ROLE_ADMIN');
        $admin->setUserDetails($adminDetails);
        $admin->setEnabled(true);
        $admin->setCreatedAt(new \DateTime());
        $this->em->persist($admin);

        $userDetails = new UserDetails();
        $userDetails->setName('Normal');
        $userDetails->setSurname('User');
        $this->em->persist($userDetails);

        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'userpass'));
        $user->setRoles('ROLE_USER');
        $user->setUserDetails($userDetails);
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);

        $this->em->flush();

        $this->client->loginUser($admin);

        $this->client->request('GET', '/api/users');
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data, 'Response should be a JSON array');
        $this->assertNotEmpty($data, 'At least one non‐admin must be returned');

        $emails = array_map(fn($u) => $u['email'], $data);
        $roles  = array_map(fn($u) => $u['role'],  $data);

        $this->assertContains(
            'user@example.com',
            $emails,
            'The non‐admin user we created must appear in the returned array'
        );

        foreach ($roles as $role) {
            $this->assertNotSame(
                'ROLE_ADMIN',
                $role,
                'No returned user may have ROLE_ADMIN'
            );
        }
    }


}
