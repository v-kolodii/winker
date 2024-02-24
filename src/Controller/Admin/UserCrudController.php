<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CEO')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($this->getUser()?->getCompany()) {
            $response->andWhere('entity.company = :company')->setParameter('company', $this->getUser()->getCompany());
        }

        return $response;
    }

    public function configureFields(string $pageName): iterable
    {
        $roleListChoices = [
            'EMPLOYEE' => User::ROLE_CUSTOMER,
            'CEO' => User::ROLE_CEO,
        ];

        if ($this->isGranted('ROLE_ADMIN')) {
            $roleListChoices = [
                'EMPLOYEE' => User::ROLE_CUSTOMER,
                'CEO' => User::ROLE_CEO,
                'ADMIN' => User::ROLE_ADMIN,
            ];
        }

        yield TextField::new('email');
        yield TextField::new('password')->hideOnIndex();
        yield TextField::new('firstName');
        yield TextField::new('lastName');
        yield ChoiceField::new('roles')->allowMultipleChoices()->setChoices($roleListChoices);
        yield AssociationField::new('company')->autocomplete()
            ->setQueryBuilder(function (QueryBuilder $qb) {
                if ($this->getUser()?->getCompany()) {
                    $qb->andWhere('entity.id = :id')
                        ->setParameter('id', $this->getUser()?->getCompany()->getId());
                }
            });
        yield AssociationField::new('department')->autocomplete()
            ->setQueryBuilder(function (QueryBuilder $qb) {
                if ($this->getUser()?->getCompany()) {
                    $qb->andWhere('entity.company = :company')
                        ->setParameter('company', $this->getUser()?->getCompany()->getId());
                }
            });
    }
}
