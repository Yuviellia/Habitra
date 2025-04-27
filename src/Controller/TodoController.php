<?php
namespace App\Controller;

use App\Service\TodoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class TodoController extends AbstractController {
    private TodoService $todoService;

    public function __construct(TodoService $todoService) {
        $this->todoService = $todoService;
    }
    #[Route('/api/user/{userId}/todos', name: 'get_user_todos', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/{userId}/todos',
        summary: 'Retrieve all todos for a user',
        parameters: [
            new OA\Parameter(
                name: 'userId',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A list of todos for the user',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'task', type: 'string'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 404, description: 'No todos found for this user')
        ]
    )]
    public function getUserTodos(int $userId): JsonResponse {
        $result = $this->todoService->getUserTodos($userId);
        return $this->json($result['body'], $result['status']);
    }


    #[Route('/api/user/{userId}/todos', name: 'create_user_todo', methods: ['POST'])]
    #[OA\Post(
        path: '/api/user/{userId}/todos',
        summary: 'Create a new todo for the user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'task', description: 'Task description', type: 'string')
                ],
                type: 'object'
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'userId',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Todo created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Todo created successfully'),
                        new OA\Property(
                            property: 'todo',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'userId', type: 'integer'),
                                new OA\Property(property: 'task', type: 'string'),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Bad Request (task missing)'),
            new OA\Response(response: 404, description: 'User not found')
        ]
    )]
    public function createUserTodo(int $userId, Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $result = $this->todoService->createUserTodo($userId, $data);
        return $this->json($result['body'], $result['status']);
    }
}
