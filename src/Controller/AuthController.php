<?php
namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AuthController extends AbstractController {
    private AuthService $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
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
    public function login(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $result = $this->authService->login($data);
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        path: "/api/register",
        summary: "Registers a new user",
        tags: ["Authentication"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password", "name", "surname"],
            properties: [
                new OA\Property(property: "email", description: "User email", type: "string", example: "user@example.com"),
                new OA\Property(property: "password", description: "User password", type: "string", example: "mypassword"),
                new OA\Property(property: "name", description: "User name", type: "string", example: "John"),
                new OA\Property(property: "surname", description: "User surname", type: "string", example: "Doe"),
                new OA\Property(property: "phone", description: "User phone", type: "string", example: "+48123123123", nullable: true)
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
                new OA\Property(property: "token", type: "string", example: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
                new OA\Property(
                    property: "user",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "email", type: "string", example: "user@example.com"),
                        new OA\Property(property: "name", type: "string", example: "John"),
                        new OA\Property(property: "surname", type: "string", example: "Doe"),
                        new OA\Property(property: "phone", type: "string", example: "+48123123123", nullable: true),
                    ],
                    type: "object"
                )
            ],
            type: "object"
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Bad Request (missing fields or email already registered)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Name, surname, email, and password required")
            ],
            type: "object"
        )
    )]
    public function register(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || empty($data['name']) || empty($data['surname']) || empty($data['email']) || empty($data['password'])) {
            return $this->json(['message' => 'Name, surname, email, and password required'], 400);
        }
        $result = $this->authService->register($data);
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/users', name: 'api_get_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: "/api/users",
        summary: "Gets a list of all non-admin users",
        security: [["bearerAuth" => []]],
        tags: ["Users"]
    )]
    #[OA\Response(
        response: 200,
        description: "List of non-admin users",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(
                properties: [
                    new OA\Property(property: "id", type: "integer", example: 1),
                    new OA\Property(property: "email", type: "string", example: "user@example.com"),
                    new OA\Property(property: "role", type: "string", example: "ROLE_USER"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: "No non-admin users found",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "No non-admin users found")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized (missing or invalid JWT)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "JWT Token not found or invalid")
            ]
        )
    )]
    public function getUsers(): JsonResponse {
        $result = $this->authService->getNonAdminUsers();
        return $this->json($result['body'], $result['status']);
    }
}
