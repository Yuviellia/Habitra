<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Tag;
use App\Entity\Marked;
use App\Repository\HabitRepository;
use Doctrine\ORM\EntityManagerInterface;

class HabitService {
    private HabitRepository $habitRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        HabitRepository $habitRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->habitRepository = $habitRepository;
        $this->entityManager = $entityManager;
    }

    public function getUserHabits(int $userId): array {
        $habits = $this->habitRepository->getHabitsByUserId($userId);
        if (empty($habits)) {
            return [
                'status' => 404,
                'body'   => ['message' => 'No habits found for this user'],
            ];
        }

        $data = [];
        foreach ($habits as $habit) {
            $markedDates = [];
            foreach ($habit->getMarkedDates() as $marked) {
                $markedDates[] = [
                    'id'   => $marked->getId(),
                    'date' => $marked->getDate()->format('Y-m-d'),
                ];
            }
            $data[] = [
                'id'           => $habit->getId(),
                'name'         => $habit->getName(),
                'created_at'   => $habit->getCreatedAt()->format('Y-m-d H:i:s'),
                'marked_dates' => $markedDates,
            ];
        }

        return [
            'status' => 200,
            'body'   => $data,
        ];
    }

    public function createUserHabit(int $userId, array $data): array {
        if (empty($data['name'])) {
            return [
                'status' => 400,
                'body'   => ['message' => 'Habit name is required'],
            ];
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return [
                'status' => 404,
                'body'   => ['message' => 'User not found'],
            ];
        }

        $habit = new Tag();
        $habit->setUser($user)
            ->setName($data['name'])
            ->setCreatedAt(new \DateTime());

        $this->entityManager->persist($habit);
        $this->entityManager->flush();

        return [
            'status' => 201,
            'body'   => [
                'message' => 'Habit created',
                'habit'   => [
                    'id'         => $habit->getId(),
                    'name'       => $habit->getName(),
                    'created_at' => $habit->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
            ],
        ];
    }

    public function markHabit(int $userId, int $habitId, array $data): array {
        if (empty($data['date'])) {
            return [
                'status' => 400,
                'body'   => ['message' => 'Marked date is required'],
            ];
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return [
                'status' => 404,
                'body'   => ['message' => 'User not found'],
            ];
        }

        $habit = $this->habitRepository->find($habitId);
        if (!$habit || $habit->getUser()->getId() !== $userId) {
            return [
                'status' => 404,
                'body'   => ['message' => 'Habit not found for this user'],
            ];
        }

        try {
            $date = new \DateTime($data['date']);
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'body'   => ['message' => 'Invalid date format'],
            ];
        }

        $marked = new Marked();
        $marked->setTag($habit)
            ->setDate($date);

        $this->entityManager->persist($marked);
        $this->entityManager->flush();

        return [
            'status' => 201,
            'body'   => [
                'message' => 'Habit marked for date',
                'marked'  => [
                    'id'   => $marked->getId(),
                    'date' => $marked->getDate()->format('Y-m-d'),
                ],
            ],
        ];
    }
}