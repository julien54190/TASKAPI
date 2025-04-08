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
use Symfony\Component\HttpFoundation\Response as Reponse;

final class TaskController extends AbstractController
{
    public function __construct(private Security $security){}



    #[Route('/', name: 'home')]
    public function index(): Reponse
    {
        return $this->render('task/index.html.twig');
    }

            /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Récupère toutes les tâches",
     *     tags={"Tâches"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tâches"
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Récupère une tâche par son ID",
     *     tags={"Tâches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tâche",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la tâche"
     *     )
     * )
     */
    #[Route('api/tasks/{id}', methods: ['GET'])]
    public function get(Task $task): JsonResponse
    {
        return $this->json($task);
    }
    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Crée une nouvelle tâche",
     *     tags={"Tâches"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tâche créée"
     *     )
     * )
     */
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
    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Met à jour une tâche",
     *     tags={"Tâches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tâche",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tâche mise à jour"
     *     )
     * )
     */
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
    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     summary="Supprime une tâche",
     *     tags={"Tâches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tâche",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Tâche supprimée"
     *     )
     * )
     */
    #[Route('api/tasks/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Task $task, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($task);
        $em->flush();
        return $this->json(null, 204);
    }

}
