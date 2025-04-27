<?php
namespace App\Controller;

use App\Service\HabitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class HabitController extends AbstractController {
    private HabitService $habitService;

    public function __construct(HabitService $habitService) {
        $this->habitService = $habitService;
    }

    #[Route('/api/user/{userId}/habits', name: 'get_user_habits', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/{userId}/habits',
        summary: 'Retrieve all habits for a user',
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
                description: 'A list of habits for the user',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(
                                property: 'marked_dates',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer'),
                                        new OA\Property(property: 'date', type: 'string', format: 'date')
                                    ],
                                    type: 'object'
                                )
                            )
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 404, description: 'No habits found for this user')
        ]
    )]
    public function getUserHabits(int $userId): JsonResponse {
        $result = $this->habitService->getUserHabits($userId);
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/user/{userId}/habits', name: 'create_user_habit', methods: ['POST'])]
    #[OA\Post(
        path: '/api/user/{userId}/habits',
        summary: 'Create a new habit for the user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', description: 'Name of the habit', type: 'string')
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
                description: 'Habit created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Habit created'),
                        new OA\Property(
                            property: 'habit',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Bad Request (name missing)')
        ]
    )]
    public function createUserHabit(int $userId, Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $result = $this->habitService->createUserHabit($userId, $data);
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/user/{userId}/habits/{habitId}/mark', name: 'mark_habit', methods: ['POST'])]
    #[OA\Post(
        path: '/api/user/{userId}/habits/{habitId}/mark',
        summary: 'Mark a habit for a specific date',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'date', description: 'Date the habit was marked', type: 'string', format: 'date')
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
            ),
            new OA\Parameter(
                name: 'habitId',
                description: 'Habit ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Habit marked for the specified date',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Habit marked for date'),
                        new OA\Property(
                            property: 'marked',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'date', type: 'string', format: 'date')
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Bad Request (date missing)'),
            new OA\Response(response: 404, description: 'User or Habit not found')
        ]
    )]
    public function markHabit(int $userId, int $habitId, Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $result = $this->habitService->markHabit($userId, $habitId, $data);
        return $this->json($result['body'], $result['status']);
    }
}
