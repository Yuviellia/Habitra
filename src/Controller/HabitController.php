<?php
namespace App\Controller;

use App\Entity\User;
use App\Service\HabitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HabitController extends AbstractController {
    private HabitService $habitService;

    public function __construct(HabitService $habitService) {
        $this->habitService = $habitService;
    }

    #[Route('/api/habits', name: 'get_user_habits', methods: ['GET'])]
    #[OA\Get(
        path: '/api/habits',
        summary: 'Retrieve all habits for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Habits']
    )]
    #[OA\Response(
        response: 200,
        description: 'A list of habits (each with marked dates) for the user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 42),
                    new OA\Property(property: 'name', type: 'string', example: 'Drink Water'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-05-18 14:23:00'),
                    new OA\Property(
                        property: 'marked_dates',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 123),
                                new OA\Property(property: 'date', type: 'string', format: 'date', example: '2025-05-18')
                            ],
                            type: 'object'
                        )
                    )
                ],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'No habits found for this user',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'No habits found for this user')
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
    public function getUserHabits(): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $result = $this->habitService->getUserHabits($user->getId());
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/habits', name: 'create_user_habit', methods: ['POST'])]
    #[OA\Post(
        path: '/api/habits',
        summary: 'Create a new habit for the authenticated user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Name of the habit', type: 'string', example: 'Read a Book')
                ],
                type: 'object'
            )
        ),
        tags: ['Habits']
    )]
    #[OA\Response(
        response: 201,
        description: 'Habit created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Habit created'),
                new OA\Property(
                    property: 'habit',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 42),
                        new OA\Property(property: 'name', type: 'string', example: 'Read a Book'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-05-18 15:00:00')
                    ],
                    type: 'object'
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad Request (missing habit name)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Habit name is required')
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
    public function createUserHabit(Request $request): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $result = $this->habitService->createUserHabit($user->getId(), $data);
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/habits/{habitId}/mark', name: 'mark_habit', methods: ['POST'])]
    #[OA\Post(
        path: '/api/habits/{habitId}/mark',
        summary: 'Toggle mark/unmark for a habit on a specific date',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['date'],
                properties: [
                    new OA\Property(property: 'date', description: 'Date to mark/unmark (YYYY-MM-DD)', type: 'string', format: 'date', example: '2025-05-18')
                ],
                type: 'object'
            )
        ),
        tags: ['Habits'],
        parameters: [
            new OA\Parameter(
                name: 'habitId',
                description: 'ID of the habit to mark/unmark',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 42)
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
                                new OA\Property(property: 'id', type: 'integer', example: 123),
                                new OA\Property(property: 'date', type: 'string', format: 'date', example: '2025-05-18')
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 200,
                description: 'Existing mark removed for that date',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Marked date removed'),
                        new OA\Property(
                            property: 'removed',
                            properties: [
                                new OA\Property(property: 'date', type: 'string', format: 'date', example: '2025-05-18')
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request (missing or invalid date)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Marked date is required or Invalid date format')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User or Habit not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Habit not found for this user')
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
    public function markHabit(int $habitId, Request $request): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $result = $this->habitService->markHabit($user->getId(), $habitId, $data);
        return $this->json($result['body'], $result['status']);
    }

    #[Route('/api/habits/{habitId}', name: 'delete_user_habit', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/habits/{habitId}',
        summary: 'Delete a specific habit and all its marked dates',
        security: [['bearerAuth' => []]],
        tags: ['Habits'],
        parameters: [
            new OA\Parameter(
                name: 'habitId',
                description: 'ID of the habit to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 42)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Habit and all associated marks deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Habit and all associated marks deleted')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Habit not found for this user',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Habit not found for this user')
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
    public function deleteUserHabit(int $habitId): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $result = $this->habitService->deleteUserHabit($user->getId(), $habitId);
        return $this->json($result['body'], $result['status']);
    }
}
