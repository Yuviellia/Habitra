<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Marked;
use App\Entity\User;
use App\Repository\HabitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class HabitController extends AbstractController {
    private $habitRepository;
    private $entityManager;

    public function __construct(HabitRepository $habitRepository, EntityManagerInterface $entityManager) {
        $this->habitRepository = $habitRepository;
        $this->entityManager = $entityManager;
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
        $habits = $this->habitRepository->getHabitsByUserId($userId);

        if (empty($habits)) {
            return $this->json(['message' => 'No habits found for this user'], 404);
        }

        $data = [];
        foreach ($habits as $habit) {
            $markedDates = [];
            foreach ($habit->getMarkedDates() as $marked) {
                $markedDates[] = [
                    'id'   => $marked->getId(),
                    'date' => $marked->getDate()->format('Y-m-d')
                ];
            }
            $data[] = [
                'id'           => $habit->getId(),
                'name'         => $habit->getName(),
                'created_at'   => $habit->getCreatedAt()->format('Y-m-d H:i:s'),
                'marked_dates' => $markedDates
            ];
        }
        return $this->json($data, 200);
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
        if (empty($data['name'])) {
            return $this->json(['message' => 'Habit name is required'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $habit = new Tag();
        $habit->setUser($user);
        $habit->setName($data['name']);
        $habit->setCreatedAt(new \DateTime());
        $this->entityManager->persist($habit);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Habit created',
            'habit'   => [
                'id'         => $habit->getId(),
                'name'       => $habit->getName(),
                'created_at' => $habit->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ], 201);
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
        if (empty($data['date'])) {
            return $this->json(['message' => 'Marked date is required'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $habit = $this->habitRepository->find($habitId);
        if (!$habit || $habit->getUser()->getId() !== $userId) {
            return $this->json(['message' => 'Habit not found for this user'], 404);
        }

        try {
            $date = new \DateTime($data['date']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Invalid date format'], 400);
        }

        $marked = new \App\Entity\Marked();
        $marked->setTag($habit);
        $marked->setDate($date);

        $this->entityManager->persist($marked);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Habit marked for date',
            'marked'  => [
                'id'   => $marked->getId(),
                'date' => $marked->getDate()->format('Y-m-d')
            ]
        ], 201);
    }
}
