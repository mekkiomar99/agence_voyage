<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Form\ServiceFormType;
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
    public function delete(Service $service, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($service);
        $entityManager->flush();
        $this->addFlash('success', 'Service supprimé avec succès !');
        return $this->redirectToRoute('admin_services_list');
    }
}
