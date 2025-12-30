<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ServiceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReservationController extends AbstractController
{
    #[Route('/reserve/{id}', name: 'app_reserve', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reserve(int $id, Request $request, ServiceRepository $serviceRepository, ManagerRegistry $doctrine): Response
    {
        $service = $serviceRepository->find($id);
        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        $user = $this->getUser();
        $quantity = (int) $request->request->get('quantity', 1);
        $dateFrom = $request->request->get('dateFrom');
        $dateTo = $request->request->get('dateTo');

        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setService($service);
        $reservation->setQuantity($quantity);
        // If the service is available, auto-confirm and proceed to payment
        if ($service->isAvailable()) {
            $reservation->setStatus('confirmed');
        } else {
            $reservation->setStatus('pending');
        }

        if ($dateFrom) {
            $reservation->setDateFrom(new \DateTime($dateFrom));
        }
        if ($dateTo) {
            $reservation->setDateTo(new \DateTime($dateTo));
        }

        $em = $doctrine->getManager();
        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation effectuée avec succès!');

        if ($reservation->getStatus() === 'confirmed') {
            return $this->redirectToRoute('app_reservation_payment', ['id' => $reservation->getId()]);
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/reservation/{id}', name: 'app_reservation_detail')]
    #[IsGranted('ROLE_USER')]
    public function detail(int $id, ManagerRegistry $doctrine): Response
    {
        $reservation = $doctrine->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Reservation not found');
        }

        // Verify ownership
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this reservation');
        }

        return $this->render('reservation/detail.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
