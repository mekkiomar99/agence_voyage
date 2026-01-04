<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ServiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre'),
            TextEditorField::new('description', 'Description'),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            \EasyCorp\Bundle\EasyAdminBundle\Field\TextField::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
            ImageField::new('imageFilename', 'Fichier image')
                ->setBasePath('/uploads/services/')
                ->onlyOnIndex(),
            BooleanField::new('available', 'Disponible'),
        ];
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Service $service */
        $service = $entityInstance;
        
        // Vérifier s'il y a des réservations associées à ce service
        $reservationRepository = $entityManager->getRepository(\App\Entity\Reservation::class);
        $reservations = $reservationRepository->findBy(['service' => $service]);
        $nonCompletedReservations = array_filter($reservations, function($reservation) {
            return $reservation->getStatus() !== 'completed';
        });
        
        if (count($nonCompletedReservations) > 0) {
            $this->addFlash('danger', 'Impossible de supprimer ce service car il existe ' . count($nonCompletedReservations) . ' réservation(s) non complétée(s). Seules les réservations avec le statut "completed" permettent la suppression du service.');
            throw new AccessDeniedHttpException('Impossible de supprimer ce service car il existe des réservations non complétées.');
        }

        // Supprimer les réservations "completed" avant de supprimer le service
        $completedReservations = array_filter($reservations, function($reservation) {
            return $reservation->getStatus() === 'completed';
        });
        
        foreach ($completedReservations as $reservation) {
            $entityManager->remove($reservation);
        }
        
        // Procéder à la suppression normale du service
        parent::deleteEntity($entityManager, $entityInstance);
        
        // Afficher un message si des réservations ont été supprimées
        if (count($completedReservations) > 0) {
            $this->addFlash('success', count($completedReservations) . ' réservation(s) complétée(s) supprimée(s) automatiquement.');
        }
    }
}
