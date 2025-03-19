<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TodoController extends AbstractController {
    #[Route('/api/todos/{id}', name: 'get_todo', methods: ['GET'])]
    public function getTodo(int $id): JsonResponse {
        if ($id !== 1) {
            return $this->json(['message' => 'Todo not found'], 404);
        }

        $todoData = [
            'id' => 1,
            'title' => 'groceries',
            'description' => 'buy a cucumber, mayo and pepsi',
            'completed' => false
        ];

        return $this->json($todoData, 200);
    }
}
