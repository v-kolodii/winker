<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiResource\State\Providers\DepartmentCompanyProvider;
use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => 'department:read'],
        ),
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 20,
            normalizationContext: ['groups' => 'department:read'],
            provider: DepartmentCompanyProvider::class,
        )
    ],
    order: ['id' => 'DESC']
)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['department:read', 'user:list', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['department:read', 'user:list', 'user:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 1026, nullable: true)]
    #[Groups(['department:read'])]
    private ?string $description = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[Groups(['department:read'])]
    private ?User $head = null;

    #[ORM\ManyToOne(inversedBy: 'departments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['department:read'])]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'departments')]
    #[Groups(['department:read', 'user:list', 'user:read'])]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $departments;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: User::class)]
    private Collection $users;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->created_at = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(self $department): static
    {
        if (!$this->departments->contains($department)) {
            $this->departments->add($department);
            $department->setParent($this);
        }

        return $this;
    }

    public function removeDepartment(self $department): static
    {
        if ($this->departments->removeElement($department)) {
            // set the owning side to null (unless already changed)
            if ($department->getParent() === $this) {
                $department->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setDepartment($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getDepartment() === $this) {
                $user->setDepartment(null);
            }
        }

        return $this;
    }

    public function getHead(): ?User
    {
        return $this->head;
    }

    public function setHead(?User $head): static
    {
        $this->head = $head;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
