<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Form\ServiceFormType;
use App\Repository\ReservationRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/services', name: 'admin_services')]
#[IsGranted('ROLE_ADMIN')]
class AdminServicesController extends AbstractController
{
    #[Route('', name: '_list')]
    public function list(ServiceRepository $serviceRepository): Response
    {
        $services = $serviceRepository->findAll();
        return $this->render('admin/services_list.html.twig', ['services' => $services]);
    }

    #[Route('/new', name: '_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($service);
            $entityManager->flush();
            $this->addFlash('success', 'Service créé avec succès !');
            return $this->redirectToRoute('admin_services_list');
        }

        return $this->render('admin/service_form.html.twig', ['form' => $form, 'service' => $service]);
    }

    #[Route('/{id}/edit', name: '_edit')]
    public function edit(Service $service, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceFormType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Service modifié avec succès !');
            return $this->redirectToRoute('admin_services_list');
        }

        return $this->render('admin/service_form.html.twig', ['form' => $form, 'service' => $service]);
    }

    #[Route('/{id}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        // CSRF token check
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_service_' . $service->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_services_list');
        }

        // Vérifier s'il y a des réservations associées à ce service
        $reservations = $reservationRepository->findBy(['service' => $service]);
        $nonCompletedReservations = array_filter($reservations, function($reservation) {
            return $reservation->getStatus() !== 'completed';
        });
        
        if (count($nonCompletedReservations) > 0) {
            $this->addFlash('error', 'Impossible de supprimer ce service car il existe ' . count($nonCompletedReservations) . ' réservation(s) non complétée(s). Seules les réservations avec le statut "completed" permettent la suppression du service.');
            return $this->redirectToRoute('admin_services_list');
        }

        // Supprimer les réservations "completed" avant de supprimer le service
        $completedReservations = array_filter($reservations, function($reservation) {
            return $reservation->getStatus() === 'completed';
        });
        
        foreach ($completedReservations as $reservation) {
            $entityManager->remove($reservation);
        }
        
        // Supprimer le service
        $entityManager->remove($service);
        $entityManager->flush();
        
        $message = 'Service supprimé avec succès !';
        if (count($completedReservations) > 0) {
            $message .= ' (' . count($completedReservations) . ' réservation(s) complétée(s) supprimée(s) automatiquement)';
        }
        $this->addFlash('success', $message);
        return $this->redirectToRoute('admin_services_list');
    }
}
