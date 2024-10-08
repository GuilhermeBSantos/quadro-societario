<?php

namespace App\Entity;

use App\Repository\PartnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
class Partner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 11)]
    private ?string $cpf = null;

    #[ORM\Column(length: 11)]
    private ?string $phone_number = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PartnerCompany>
     */
    #[ORM\OneToMany(targetEntity: PartnerCompany::class, mappedBy: 'partner_id')]
    private Collection $partnerCompanies;

    public function __construct()
    {
        $this->partnerCompanies = new ArrayCollection();
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

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

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

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): static
    {
        $this->cpf = $cpf;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, PartnerCompany>
     */
    public function getPartnerCompanies(): Collection
    {
        return $this->partnerCompanies;
    }

    public function addPartnerCompany(PartnerCompany $partnerCompany): static
    {
        if (!$this->partnerCompanies->contains($partnerCompany)) {
            $this->partnerCompanies->add($partnerCompany);
            $partnerCompany->setPartnerId($this);
        }

        return $this;
    }

    public function removePartnerCompany(PartnerCompany $partnerCompany): static
    {
        if ($this->partnerCompanies->removeElement($partnerCompany)) {
            // set the owning side to null (unless already changed)
            if ($partnerCompany->getPartnerId() === $this) {
                $partnerCompany->setPartnerId(null);
            }
        }

        return $this;
    }


    /**
     * Converte a entidade usuario para um array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'last_name' => $this->getLastName(),
            'email' => $this->getEmail(),
            'cpf' => $this->getCpf(),
            'phone_number' => $this->getPhoneNumber(),
            'created_at' => $this->getCreatedAt(),
        ];
    }

}
