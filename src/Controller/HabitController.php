<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HabitController extends AbstractController {
    #[Route('/api/habits/{id}', name: 'get_habit', methods: ['GET'])]
    public function getHabit(int $id): JsonResponse {
        if ($id !== 1) {
            return $this->json(['message' => 'Habit not found'], 404);
        }

        $habitData = [
            'id' => 1,
            'name' => 'run',
            'description' => 'run for your life',
            'streak' => 5
        ];

        return $this->json($habitData, 200);
    }
}
