<?php

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
class Certification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'certifications')]
    private ?User $User = null;

    #[ORM\ManyToOne(targetEntity: Cursus::class, nullable:true)]
    private ?Cursus $cursus = null;

    #[ORM\ManyToOne(targetEntity: Lessons::class, nullable:true)]
    private ?Lessons $lesson = null;

    #[ORM\Column(type:'datetime')]
    private \DateTimeInterface $dateObtained;

    public function __construct()
    {
        $this->dateObtained = new \DateTime();
    }

///////////////////////////////////////////////////////////Getters & Setters////////////////////////////////////////////////////////////

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getCursus(): ?getCursus
    {
        return $this->cursus;
    }

    public function setCursus(?Cursus $cursus):static
    {
        $this->cursus = $cursus;
        return $this;
    }

    public function getLesson(): ?Lessons
    {
        return $this->lesson;
    }

    public function setLesson(?Lessons $lesson):static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function getDateObtained(): \DateTimeInterface
    {
        return $this->dateObtained;
    }

    public function setDateObtained(\DateTimeInterface $dateObtained): static
    {
        $this->dateObtained = $dateObtained;
        return $this;
    }
}
