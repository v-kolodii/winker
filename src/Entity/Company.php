<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['company:read','department:read']],
        ),
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 20,
            normalizationContext: ['groups' => ['company:read','department:read']],
        )
    ],
    order: ['id' => 'ASC']
)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['company:read', 'department:read', 'user:list', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'department:read', 'user:list', 'user:read'])]
    private ?string $name = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $db_url = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Department::class, orphanRemoval: true)]
    private Collection $departments;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: User::class)]
    private Collection $users;

    #[ORM\Column]
    #[Groups(['company:read', 'department:read', 'user:list', 'user:read'])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['company:read', 'department:read', 'user:list', 'user:read'])]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['company:read'])]
    private ?string $imagePath1 = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['company:read'])]
    private ?string $imagePath2 = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['company:read'])]
    private ?string $imagePath3 = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['company:read'])]
    private ?string $imagePath4 = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['company:read'])]
    private ?string $imagePath5 = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['company:read'])]
    private ?string $imagePath6 = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['company:read'])]
    private ?string $imagePath7 = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(['company:read'])]
    private ?string $imagePath8 = null;


    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->departments = new ArrayCollection();
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


    public function getDbUrl(): ?string
    {
        return $this->db_url;
    }

    public function setDbUrl(?string $db_url): static
    {
        $this->db_url = $db_url;

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }


    public function addDepartment(Department $department): static
    {
        if (!$this->departments->contains($department)) {
            $this->departments->add($department);
            $department->setCompany($this);
        }

        return $this;
    }

    public function removeDepartment(Department $department): static
    {
        if ($this->departments->removeElement($department)) {
            // set the owning side to null (unless already changed)
            if ($department->getCompany() === $this) {
                $department->setCompany(null);
            }
        }

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
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
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

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

    public function getDepartmentsCount(): int
    {
        return $this->departments->count();
    }

    public function getImagePath1(): ?string
    {
        return $this->imagePath1;
    }

    public function setImagePath1(?string $imagePath1): self
    {
        $this->imagePath1 = $imagePath1;

        return $this;
    }

    public function getImagePath2(): ?string
    {
        return $this->imagePath2;
    }

    public function setImagePath2(?string $imagePath2): self
    {
        $this->imagePath2 = $imagePath2;

        return $this;
    }

    public function getImagePath3(): ?string
    {
        return $this->imagePath3;
    }

    public function setImagePath3(?string $imagePath3): self
    {
        $this->imagePath3 = $imagePath3;

        return $this;
    }

    public function getImagePath4(): ?string
    {
        return $this->imagePath4;
    }

    public function setImagePath4(?string $imagePath4): self
    {
        $this->imagePath4 = $imagePath4;

        return $this;
    }

    public function getImagePath5(): ?string
    {
        return $this->imagePath5;
    }

    public function setImagePath5(?string $imagePath5): self
    {
        $this->imagePath5 = $imagePath5;

        return $this;
    }

    public function getImagePath6(): ?string
    {
        return $this->imagePath6;
    }

    public function setImagePath6(?string $imagePath6): self
    {
        $this->imagePath6 = $imagePath6;

        return $this;
    }

    public function getImagePath7(): ?string
    {
        return $this->imagePath7;
    }

    public function setImagePath7(?string $imagePath7): self
    {
        $this->imagePath7 = $imagePath7;

        return $this;
    }

    public function getImagePath8(): ?string
    {
        return $this->imagePath8;
    }

    public function setImagePath8(?string $imagePath8): self
    {
        $this->imagePath8 = $imagePath8;

        return $this;
    }
}
