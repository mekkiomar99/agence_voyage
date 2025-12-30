<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class PaymentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Payment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('reservation', 'Réservation'),
            MoneyField::new('amount', 'Montant')->setCurrency('EUR'),
            TextField::new('method', 'Méthode'),
            ChoiceField::new('status', 'Statut')->setChoices([
                'En attente' => 'pending',
                'Payée' => 'paid',
                'Échouée' => 'failed',
            ]),
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
        ];
    }
}
