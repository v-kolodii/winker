<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class CompanyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($this->getUser()?->getCompany()) {
            $response->andWhere('entity.id = :id')->setParameter('id', $this->getUser()->getCompany()->getId());
        }

        return $response;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
             IdField::new('id')
                ->onlyOnIndex(),
             TextField::new('name'),
             Field::new('created_at')
                ->hideOnForm(),
            IntegerField::new('departmentsCount')
                ->hideOnForm(),
            TextField::new('db_url')->hideOnForm(),
            Field::new('isActive'),
            Field::new('imagePath1')->hideOnIndex(),
            Field::new('imagePath2')->hideOnIndex(),
            Field::new('imagePath3')->hideOnIndex(),
            Field::new('imagePath4')->hideOnIndex(),
            Field::new('imagePath5')->hideOnIndex(),
            Field::new('imagePath6')->hideOnIndex(),
            Field::new('imagePath7')->hideOnIndex(),
            Field::new('imagePath8')->hideOnIndex(),
        ];
    }
}
