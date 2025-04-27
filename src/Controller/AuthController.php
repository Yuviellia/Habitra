<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserDetails;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use OpenApi\Attributes as OA;

class AuthController extends AbstractController {
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    #[OA\Post(
        path: "/api/login",
        summary: "Logs in the user"
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", description: "User email", type: "string"),
                new OA\Property(property: "password", description: "User password", type: "string")
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Login successful",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Login successful"),
                new OA\Property(property: "token", type: "string", example: "example-token")
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Bad Request (missing email or password)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Email and password required")
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized (invalid credentials)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Invalid credentials [email]")
            ],
            type: "object"
        )
    )]
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['message' => 'Email and password required'], 400);
        }

        $user = $userRepository->findOneBy(['email' => $data['email']]);
        if (!$user) {
            return $this->json(['message' => 'Invalid credentials [email]'], 401);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['message' => 'Invalid credentials [password]'], 401);
        }

        return $this->json([
            'message' => 'Login successful',
            'token' => 'example-token'
        ], 200);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        path: "/api/register",
        summary: "Registers a new user"
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", description: "User email", type: "string"),
                new OA\Property(property: "password", description: "User password", type: "string"),
                new OA\Property(property: "name", description: "User name", type: "string"),
                new OA\Property(property: "surname", description: "User surname", type: "string"),
                new OA\Property(property: "phone", description: "User phone (optional)", type: "string", nullable: true)
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 201,
        description: "User successfully registered",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "User registered successfully"),
                new OA\Property(
                    property: "user",
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "surname", type: "string"),
                        new OA\Property(property: "phone", type: "string", nullable: true)
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Bad Request (missing required fields or email already registered)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Name, surname, email, and password required or Email already registered")
            ],
            type: "object"
        )
    )]
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password']) || empty($data['name']) || empty($data['surname'])) {
            return $this->json(['message' => 'Name, surname, email, and password required'], 400);
        }

        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['message' => 'Email already registered'], 400);
        }

        $userDetails = new UserDetails();
        $userDetails->setName($data['name']);
        $userDetails->setSurname($data['surname']);
        $userDetails->setPhone($data['phone'] ?? null);

        $entityManager->persist($userDetails);
        $entityManager->flush();

        $user = new User();
        $user->setEmail($data['email']);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setUserDetails($userDetails);
        $user->setEnabled(true);
        $user->setCreatedAt(new \DateTime());

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $userDetails->getName(),
                'surname' => $userDetails->getSurname(),
                'phone' => $userDetails->getPhone()
            ]
        ], 201);
    }
}
