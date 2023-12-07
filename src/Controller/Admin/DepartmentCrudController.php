<?php

namespace App\Controller\Admin;

use App\Entity\Department;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class DepartmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Department::class;
    }



    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Company Department')
            ->setEntityLabelInPlural('Company Departments')
                ->setSearchFields(['name', 'company', 'head_id'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
            return $filters
                    ->add(EntityFilter::new('company'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
//        return [
//            IdField::new('id'),
//            TextField::new('title'),
//            TextEditorField::new('description'),
//        ];

//              yield AssociationField::new('company');
               yield TextField::new('name');
               yield TextEditorField::new('description');
//               yield TextareaField::new('text')
//                       ->hideOnIndex()
               ;
    }
    // todo add user model and create repositories for entities

}
