<?php

namespace App\Controller\Admin;

use App\Entity\Department;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DepartmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Department::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($this->getUser()?->getCompany()) {
            $response->andWhere('entity.company = :company')->setParameter('company', $this->getUser()->getCompany());
        }

        return $response;
    }



    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Департамент')
            ->setEntityLabelInPlural('Департаменти')
                ->setSearchFields(['name', 'company', 'head'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
            return $filters
                    ->add(EntityFilter::new('company'))
                    ->add(EntityFilter::new('head'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {

        yield TextField::new('name');
        yield AssociationField::new('head')->autocomplete();
        yield AssociationField::new('company')->autocomplete();
        yield AssociationField::new('parent')->autocomplete();
        yield TextEditorField::new('description');
        yield CollectionField::new('departments')->hideOnForm();
    }
}
