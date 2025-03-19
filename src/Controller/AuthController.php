<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController {
    /** Log in */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['message' => 'Email and password required'], 400);
        }

        // check good, database when
        if ($data['email'] === 'hh@example.com' && $data['password'] === 'hh') {
            return $this->json([
                'message' => 'Login successful',
                'token' => 'example-token'
            ], 200);
        }

        return $this->json(['message' => 'Invalid credentials'], 401);
    }

    /** Register */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            return $this->json(['message' => 'Name, email and password required'], 400);
        }

        // database shall go here
        $userData = [
            'id' => 2,
            'name' => $data['name'],
            'email' => $data['email']
        ];

        return $this->json([
            'message' => 'User registered successfully',
            'user' => $userData
        ], 201);
    }
}
