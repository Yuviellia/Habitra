<?php

namespace App\Service;

use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;

class TodoService {
    private EntityManagerInterface $entityManager;
    private TodoRepository $todoRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TodoRepository $todoRepository
    ) {
        $this->entityManager = $entityManager;
        $this->todoRepository = $todoRepository;
    }

    public function getUserTodos(int $userId): array {
        $todos = $this->todoRepository->getTodosByUserId($userId);
        if (empty($todos)) {
            return [
                'status' => 404,
                'body'   => ['message' => 'No todos found for this user'],
            ];
        }

        $data = [];
        foreach ($todos as $todo) {
            $data[] = [
                'id'         => $todo->getId(),
                'task'       => $todo->getTask(),
                'created_at' => $todo->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return [
            'status' => 200,
            'body'   => $data,
        ];
    }

    public function createUserTodo(int $userId, array $data): array {
        if (empty($data['task'])) {
            return [
                'status' => 400,
                'body'   => ['message' => 'Task is required'],
            ];
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return [
                'status' => 404,
                'body'   => ['message' => 'User not found'],
            ];
        }

        $todo = new Todo();
        $todo->setUser($user)
            ->setTask($data['task'])
            ->setCreatedAt(new \DateTime());

        $this->entityManager->persist($todo);
        $this->entityManager->flush();

        return [
            'status' => 201,
            'body'   => [
                'message' => 'Todo created successfully',
                'todo'    => [
                    'id'        => $todo->getId(),
                    'userId'    => $user->getId(),
                    'task'      => $todo->getTask(),
                    'createdAt' => $todo->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
            ],
        ];
    }
}