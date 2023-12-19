<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
//        return [
//            IdField::new('id'),
//            TextField::new('title'),
//            TextEditorField::new('description'),
//        ];

//              yield AssociationField::new('company')->autocomplete()->setFormTypeOption('by_reference', false);

        yield EmailField::new('email');
        yield TextField::new('password')->hideOnIndex();
        yield TextField::new('firstName');
        yield TextField::new('lastName');
        yield ChoiceField::new('roles')->allowMultipleChoices()->setChoices([
            'Customer' => User::ROLE_CUSTOMER,
            'CEO' => User::ROLE_CEO,
        ]);
        yield AssociationField::new('company')->autocomplete()->setFormTypeOption('by_reference', false);
        yield AssociationField::new('department')->autocomplete()->setFormTypeOption('by_reference', false);
    }
}
