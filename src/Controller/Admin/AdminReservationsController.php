<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reservations', name: 'admin_reservations')]
#[IsGranted('ROLE_ADMIN')]
class AdminReservationsController extends AbstractController
{
    #[Route('', name: '')]
    public function list(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('admin/reservations_list.html.twig', ['reservations' => $reservations]);
    }

    #[Route('/{id}/status', name: '_change_status', methods: ['POST'])]
    public function changeStatus(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('change_reservation_status' . $reservation->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_reservations');
        }

        $status = $request->request->get('status');
        $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('admin_reservations');
        }

        $reservation->setStatus($status);
        $em->flush();

        $this->addFlash('success', 'Statut de la réservation mis à jour.');
        return $this->redirectToRoute('admin_reservations');
    }
}
