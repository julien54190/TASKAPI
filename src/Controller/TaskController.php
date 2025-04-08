<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


final class TaskController extends AbstractController
{
    public function __construct(private Security $security){}
    #[Route('api/tasks', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $status = $request->query->get('status');
        $tasks = $em->getRepository(Task::class)->findBy([
            'user' => $this->security->getUser(),
            ...($status ? ['status' => $status] : [])
        ]);
        return $this->json($tasks);
    }
    #[Route('api/tasks/{id}', methods: ['GET'])]
    public function get(Task $task): JsonResponse
    {
        return $this->json($task);
    }
    #[Route('api/tasks', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? null);
        $task->setUser($this->security->getUser());
        $task->setisDone($data['status'] ?? 'pending');
        $em->persist($task);
        $em->flush();
        return $this->json($task, 201);
    }
    #[Route('api/tasks/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Task $task, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $task->setIsDone($data['status']);
        }
        $em->flush();
        return $this->json($task);
    }
    #[Route('api/tasks/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Task $task, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($task);
        $em->flush();
        return $this->json(null, 204);
    }

}
