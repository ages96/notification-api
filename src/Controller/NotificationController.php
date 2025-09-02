<?php
// src/Controller/NotificationController.php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/api', name: 'api_')]
class NotificationController extends AbstractController
{
    /**
     * Create a new notification
     * POST /api/notifications
     */
    #[Route('/notifications', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $notification = new Notification();
        $notification->setRecipientEmail($data['recipientEmail'] ?? '');
        $notification->setSubject($data['subject'] ?? '');
        $notification->setBody($data['body'] ?? '');
        $notification->setStatus('pending');
        $notification->setCreatedAt(new \DateTime());

        // Validate entity
        $errors = $validator->validate($notification);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $em->persist($notification);
        $em->flush();

        return $this->json($notification, 201);
    }

    /**
     * List all notifications with pagination and caching
     * GET /api/notifications?page=1&limit=10
     */
    #[Route('/notifications', methods: ['GET'])]
    public function list(Request $request, NotificationRepository $repo, CacheInterface $cache): JsonResponse
    {
        $page = max((int)$request->query->get('page', 1), 1);
        $limit = max((int)$request->query->get('limit', 10), 1);

        $cacheKey = "notifications_page_{$page}_limit_{$limit}";

        $notifications = $cache->get($cacheKey, function (ItemInterface $item) use ($repo, $page, $limit) {
            $item->expiresAfter(60); // cache for 60 seconds

            $query = $repo->createQueryBuilder('n')
                          ->orderBy('n.createdAt', 'DESC')
                          ->setFirstResult(($page - 1) * $limit)
                          ->setMaxResults($limit)
                          ->getQuery();

            return $query->getArrayResult();
        });

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'data' => $notifications,
        ]);
    }

    /**
     * Simulate sending a notification
     * POST /api/notifications/{id}/send
     */
    #[Route('/notifications/{id}/send', methods: ['POST'])]
    public function send(Notification $notification, EntityManagerInterface $em): JsonResponse
    {
        if ($notification->getStatus() === 'sent') {
            return $this->json(['message' => 'Notification already sent'], 400);
        }

        // Simulate sending email
        $notification->setStatus('sent');
        $notification->setSentAt(new \DateTime());

        $em->flush();

        return $this->json(['message' => 'Notification sent successfully']);
    }
}
