<?php

namespace App\Entity;

use App\Enum\Hours;
use App\Enum\Position;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\Table(name: 'employees')]
#[UniqueEntity(
    fields: ['email'],
    message: 'This email is already used.'
)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $employedSince = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $employedUntil = null;

    #[ORM\Column(enumType: Hours::class)]
    #[Assert\NotNull]
    private ?Hours $hours = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $salary = null;

    #[ORM\Column(enumType: Position::class)]
    #[Assert\NotNull]
    private ?Position $position = null;

    #[ORM\ManyToOne(
        targetEntity: self::class,
        inversedBy: 'employees'
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $manager = null;

    #[ORM\OneToMany(
        mappedBy: 'manager',
        targetEntity: self::class
    )]
    private Collection $employees;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function getEmployedSince(): ?\DateTimeInterface
    {
        return $this->employedSince;
    }

    public function setEmployedSince(
        \DateTimeInterface $employedSince
    ): static {
        $this->employedSince = $employedSince;
        return $this;
    }

    public function getEmployedUntil(): ?\DateTimeInterface
    {
        return $this->employedUntil;
    }

    public function setEmployedUntil(
        ?\DateTimeInterface $employedUntil
    ): static {
        $this->employedUntil = $employedUntil;
        return $this;
    }

    public function getHours(): ?Hours
    {
        return $this->hours;
    }

    public function setHours(Hours $hours): static
    {
        $this->hours = $hours;
        return $this;
    }

    public function getSalary(): ?int
    {
        return $this->salary;
    }

    public function setSalary(int $salary): static
    {
        $this->salary = $salary;
        return $this;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getManager(): ?self
    {
        return $this->manager;
    }

    public function setManager(?self $manager): static
    {
        $this->manager = $manager;
        return $this;
    }

    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): static
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->setManager($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): static
    {
        if (
            $this->employees->removeElement($employee)
            && $employee->getManager() === $this
        ) {
            $employee->setManager(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}