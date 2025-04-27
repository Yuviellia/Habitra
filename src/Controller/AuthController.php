<?php
namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class AuthController extends AbstractController {
    private AuthService $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

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
    public function login(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $result = $this->authService->login($data);
        return $this->json($result['body'], $result['status']);
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
    public function register(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $result = $this->authService->register($data);
        return $this->json($result['body'], $result['status']);
    }
}