<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserDetails;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService {
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private JwtManager $jwtManager;
    private MessageSender $messageSender;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JwtManager $jwtManager,
        MessageSender $messageSender
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->messageSender = $messageSender;
    }

    public function login(array $data): array {
        if (empty($data['email']) || empty($data['password'])) {
            return [
                'status' => 400,
                'body' => ['message' => 'Email and password required'],
            ];
        }

        $user = $this->userRepository->findByEmail($data['email']);
        if (!$user) {
            return [
                'status' => 401,
                'body' => ['message' => 'Invalid credentials [email]'],
            ];
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return [
                'status' => 401,
                'body' => ['message' => 'Invalid credentials [password]'],
            ];
        }

        $token = $this->jwtManager->create($user);

        return [
            'status' => 200,
            'body' => [
                'message' => 'Login successful',
                'token' => $token,
            ],
        ];
    }

    public function register(array $data): array {
        if (empty($data['email']) || empty($data['password']) || empty($data['name']) || empty($data['surname'])) {
            return [
                'status' => 400,
                'body' => ['message' => 'Name, surname, email, and password required'],
            ];
        }

        if ($this->userRepository->findByEmail($data['email'])) {
            return [
                'status' => 400,
                'body' => ['message' => 'Email already registered'],
            ];
        }

        $userDetails = new UserDetails();
        $userDetails->setName($data['name'])
            ->setSurname($data['surname'])
            ->setPhone($data['phone'] ?? null);

        $this->entityManager->persist($userDetails);
        $this->entityManager->flush();

        $user = new User();
        $hashed = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setEmail($data['email'])
            ->setPassword($hashed)
            ->setUserDetails($userDetails)
            ->setRoles('ROLE_USER')
            ->setEnabled(true)
            ->setCreatedAt(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = $this->jwtManager->create($user);
        $this->messageSender->send("Nowy uzytkownik: {$user->getEmail()}");

        return [
            'status' => 201,
            'body' => [
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $userDetails->getName(),
                    'surname' => $userDetails->getSurname(),
                    'phone' => $userDetails->getPhone(),
                ],
            ],
        ];
    }

    public function getNonAdminUsers(): array {
        $users = $this->userRepository->findByRoleNot('ROLE_ADMIN');

        if (empty($users)) {
            return [
                'status' => 404,
                'body'   => ['message' => 'No non-admin users found'],
            ];
        }

        $data = array_map(function (User $user) {
            return [
                'id'    => $user->getId(),
                'email' => $user->getEmail(),
                'role'  => $user->getRoles()[0] ?? null,
            ];
        }, $users);

        return [
            'status' => 200,
            'body'   => $data,
        ];
    }
}
