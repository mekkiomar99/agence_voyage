<?php

namespace App\Controller;

use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ServiceRepository $serviceRepository): Response
    {
        $services = $serviceRepository->findAll();

        return $this->render('home/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/service/{id}', name: 'app_service_show')]
    public function show(int $id, ServiceRepository $serviceRepository): Response
    {
        $service = $serviceRepository->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service not found');
        }

        return $this->render('home/service_detail.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/home', name: 'app_home_redirect')]
    public function homeRedirect(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
