<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Reservation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PaymentController extends AbstractController
{
    #[Route('/reservation/{id}/payment', name: 'app_reservation_payment')]
    #[IsGranted('ROLE_USER')]
    public function payment(int $id, ManagerRegistry $doctrine): Response
    {
        $reservation = $doctrine->getRepository(Reservation::class)->find($id);
        if (!$reservation) {
            throw $this->createNotFoundException('Reservation not found');
        }

        // verify ownership
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this reservation');
        }

        $em = $doctrine->getManager();

        $payment = $reservation->getPayment();
        if (!$payment) {
            $payment = new Payment();
            $amount = (float) $reservation->getService()->getPrice() * $reservation->getQuantity();
            $payment->setReservation($reservation);
            $payment->setAmount((string) $amount);
            $payment->setStatus('pending');
            $em->persist($payment);
            $em->flush();
        }

        return $this->render('payment/payment.html.twig', ['payment' => $payment]);
    }

    #[Route('/payment/{id}/pay', name: 'app_payment_pay', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function pay(int $id, Request $request, ManagerRegistry $doctrine, EntityManagerInterface $em): Response
    {
        $payment = $doctrine->getRepository(Payment::class)->find($id);
        if (!$payment) {
            throw $this->createNotFoundException('Payment not found');
        }

        // verify ownership
        if ($payment->getReservation()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot perform this action');
        }

        // Simulate payment success
        $payment->setStatus('paid');
        $em->flush();

        $this->addFlash('success', 'Paiement effectué avec succès.');
        return $this->redirectToRoute('app_reservation_detail', ['id' => $payment->getReservation()->getId()]);
    }
}
