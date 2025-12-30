<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Utilisateur'),
            AssociationField::new('service', 'Service'),
            IntegerField::new('quantity', 'Quantité'),
            DateTimeField::new('dateFrom', 'Date de début'),
            DateTimeField::new('dateTo', 'Date de fin'),
            ChoiceField::new('status', 'Statut')->setChoices([
                'En attente' => 'pending',
                'Confirmée' => 'confirmed',
                'Annulée' => 'cancelled',
            ]),
        ];
    }
}
