<?php
namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

#[Route('/dashboard', name: 'dashboard_')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, NotificationRepository $repo, EntityManagerInterface $em): Response
    {
        // Pagination
        $page = max((int)$request->query->get('page', 1), 1);
        $limit = 10;
        $notifications = $repo->findBy([], ['createdAt' => 'DESC'], $limit, ($page - 1) * $limit);

        // Form to create a notification
        $notification = new Notification();
        $form = $this->createFormBuilder($notification)
            ->add('recipientEmail', TextType::class, ['label' => 'Recipient Email'])
            ->add('subject', TextType::class, ['label' => 'Subject'])
            ->add('body', TextareaType::class, ['label' => 'Body'])
            ->add('save', SubmitType::class, ['label' => 'Create Notification'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $notification->setStatus('pending');
            $notification->setCreatedAt(new \DateTime());
            $em->persist($notification);
            $em->flush();

            $this->addFlash('success', 'Notification created successfully.');
            return $this->redirectToRoute('dashboard_index');
        }

        return $this->render('dashboard/index.html.twig', [
            'notifications' => $notifications,
            'form' => $form->createView(),
            'page' => $page
        ]);
    }

    #[Route('/send/{id}', name: 'send')]
    public function send(Notification $notification, EntityManagerInterface $em): Response
    {
        if ($notification->getStatus() !== 'sent') {
            $notification->setStatus('sent');
            $notification->setSentAt(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'Notification sent successfully.');
        } else {
            $this->addFlash('warning', 'Notification was already sent.');
        }

        return $this->redirectToRoute('dashboard_index');
    }
}
