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

class AuthController extends AbstractController {

    /** Log in */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['message' => 'Email and password required'], 400);
        }

        // Find user by email
        $user = $userRepository->findOneBy(['email' => $data['email']]);
        if (!$user) {
            return $this->json(['message' => 'Invalid credentials [email]'], 401);
        }

        // Verify password without manually handling salt
        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['message' => 'Invalid credentials [password]'], 401);
        }

        // Generate a token (you should use JWT or Symfony security instead)
        return $this->json([
            'message' => 'Login successful',
            'token' => 'example-token' // Replace with real token logic
        ], 200);
    }

    /** Register */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password']) || empty($data['name']) || empty($data['surname'])) {
            return $this->json(['message' => 'Name, surname, email, and password required'], 400);
        }

        // Check if email already exists
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['message' => 'Email already registered'], 400);
        }

        // Create and save UserDetails
        $userDetails = new UserDetails();
        $userDetails->setName($data['name']);
        $userDetails->setSurname($data['surname']);
        $userDetails->setPhone($data['phone'] ?? null); // Optional field

        $entityManager->persist($userDetails);
        $entityManager->flush(); // Ensure it gets an ID before using it in User

        // Create and save User without manual salt handling
        $user = new User();
        $user->setEmail($data['email']);
        // Hash password without concatenating a manual salt
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
