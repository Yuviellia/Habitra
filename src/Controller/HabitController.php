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

class HabitController extends AbstractController {
    private $habitRepository;
    private $entityManager;

    public function __construct(HabitRepository $habitRepository, EntityManagerInterface $entityManager) {
        $this->habitRepository = $habitRepository;
        $this->entityManager = $entityManager;
    }

    // GET: Retrieve all habits for a specified user along with marked dates
    #[Route('/api/user/{userId}/habits', name: 'get_user_habits', methods: ['GET'])]
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

    // POST: Create a new habit (Tag) and a Marked entry for it.
    #[Route('/api/user/{userId}/habits', name: 'create_user_habit', methods: ['POST'])]
    public function createUserHabit(int $userId, Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (empty($data['name'])) {
            return $this->json(['message' => 'Habit name is required'], 400);
        }

        // Find the user.
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        // Create new habit (Tag)
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

    // POST: Mark an existing habit (Tag) with a specific date.
    #[Route('/api/user/{userId}/habits/{habitId}/mark', name: 'mark_habit', methods: ['POST'])]
    public function markHabit(int $userId, int $habitId, Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (empty($data['date'])) {
            return $this->json(['message' => 'Marked date is required'], 400);
        }

        // Verify the user exists.
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        // Find the habit (Tag) by ID and ensure it belongs to the given user.
        $habit = $this->habitRepository->find($habitId);
        if (!$habit || $habit->getUser()->getId() !== $userId) {
            return $this->json(['message' => 'Habit not found for this user'], 404);
        }

        // Parse the provided date.
        try {
            $date = new \DateTime($data['date']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Invalid date format'], 400);
        }

        // Create a new Marked entry.
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
