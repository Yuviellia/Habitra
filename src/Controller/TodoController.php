<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class TodoController extends AbstractController {
    private $entityManager;
    private $todoRepository;
    private $userRepository;
    public function __construct(EntityManagerInterface $entityManager, TodoRepository $todoRepository, UserRepository $userRepository) {
        $this->entityManager = $entityManager;
        $this->todoRepository = $todoRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/api/user/{userId}/todos', name: 'get_user_todos', methods: ['GET'])]
    public function getUserTodos(int $userId): JsonResponse {
        // Fetch todos using the TodoRepository
        $todos = $this->todoRepository->getTodosByUserId($userId);

        if (empty($todos)) {
            return $this->json(['message' => 'No todos found for this user'], 404);
        }

        return $this->json($todos, 200);
    }


    #[Route('/api/user/{userId}/todos', name: 'create_user_todo', methods: ['POST'])]
    public function createUserTodo(int $userId, Request $request): JsonResponse {
        // Find the user by ID
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        // Get the task data from the request
        $data = json_decode($request->getContent(), true);

        if (empty($data['task'])) {
            return $this->json(['message' => 'Task is required'], 400);
        }

        // Create a new Todo entity
        $todo = new Todo();
        $todo->setUser($user);
        $todo->setTask($data['task']);
        $todo->setCreatedAt(new \DateTime());

        // Persist the Todo entity
        $this->entityManager->persist($todo);
        $this->entityManager->flush();

        // Return a successful response with the created Todo
        return $this->json([
            'message' => 'Todo created successfully',
            'todo' => [
                'id' => $todo->getId(),
                'userId' => $user->getId(),
                'task' => $todo->getTask(),
                'createdAt' => $todo->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }
}
