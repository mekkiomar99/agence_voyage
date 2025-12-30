<?php

namespace App\Controller\Admin;

use App\Repository\ServiceRepository;
use App\Repository\ReservationRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        ServiceRepository $serviceRepository,
        ReservationRepository $reservationRepository,
        PaymentRepository $paymentRepository,
        UserRepository $userRepository
    ): Response {
        $stats = [
            'total_services' => count($serviceRepository->findAll()),
            'total_reservations' => count($reservationRepository->findAll()),
            'total_users' => count($userRepository->findAll()),
            'total_revenue' => $paymentRepository->getTotalRevenue(),
            'pending_reservations' => count($reservationRepository->findBy(['status' => 'pending'])),
        ];

        $recent_reservations = $reservationRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $services = $serviceRepository->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'recent_reservations' => $recent_reservations,
            'services' => $services,
        ]);
    }
}
