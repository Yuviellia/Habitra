<?php
namespace App\Controller;

use App\Service\TodoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TodoController extends AbstractController {
    private TodoService $todoService;

    public function __construct(TodoService $todoService) {
        $this->todoService = $todoService;
    }

    #[Route('/api/todos', name: 'get_user_todos', methods: ['GET'])]
    #[OA\Get(
        path: '/api/todos',
        summary: 'Retrieve all todos for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['ToDos']
    )]
    #[OA\Response(
        response: 200,
        description: 'A list of todos for the user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 101),
                    new OA\Property(property: 'task', type: 'string', example: 'Finish API documentation'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-05-18 14:45:00')
                ],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'No todos found for this user',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'No todos found for this user')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized (missing or invalid JWT)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found or invalid')
            ],
            type: 'object'
        )
    )]
    #[IsGranted('ROLE_USER')]
    public function getUserTodos(): JsonResponse {
        $user = $this->getUser();
        $result = $this->todoService->getUserTodos($user->getId());
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/todos', name: 'create_user_todo', methods: ['POST'])]
    #[OA\Post(
        path: '/api/todos',
        summary: 'Create a new todo for the authenticated user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['task'],
                properties: [
                    new OA\Property(property: 'task', description: 'Task description', type: 'string', example: 'Write unit tests')
                ],
                type: 'object'
            )
        ),
        tags: ['ToDos']
    )]
    #[OA\Response(
        response: 201,
        description: 'Todo created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Todo created successfully'),
                new OA\Property(
                    property: 'todo',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 102),
                        new OA\Property(property: 'userId', type: 'integer', example: 5),
                        new OA\Property(property: 'task', type: 'string', example: 'Write unit tests'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-05-18 14:50:00')
                    ],
                    type: 'object'
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad Request (task missing)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Task is required')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'User not found')
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized (missing or invalid JWT)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found or invalid')
            ],
            type: 'object'
        )
    )]
    #[IsGranted('ROLE_USER')]
    public function createUserTodo(Request $request): JsonResponse {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $result = $this->todoService->createUserTodo($user->getId(), $data);
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/todos/{id}', name: 'delete_user_todo', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/todos/{id}',
        summary: 'Delete a specific todo for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['ToDos'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the todo to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 102)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Todo deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Todo deleted successfully')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized to delete this todo',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized to delete this todo')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Todo not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Todo not found')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized (missing or invalid JWT)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found or invalid')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    #[IsGranted('ROLE_USER')]
    public function deleteUserTodo(int $id): JsonResponse {
        $user = $this->getUser();
        $result = $this->todoService->deleteUserTodo($user->getId(), $id);
        return $this->json($result['body'], $result['status']);
    }
}
