<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fantasy_name = null;

    #[ORM\Column(length: 255)]
    private ?string $company_name = null;

    #[ORM\Column(length: 14)]
    private ?string $cnpj = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $opening_date = null;

    #[ORM\Column(length: 11)]
    private ?string $phone_number = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $invoicing = null;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PartnerCompany>
     */
    #[ORM\OneToMany(targetEntity: PartnerCompany::class, mappedBy: 'company_id')]
    private Collection $partnerCompanies;

    public function __construct()
    {
        $this->partnerCompanies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFantasyName(): ?string
    {
        return $this->fantasy_name;
    }

    public function setFantasyName(string $fantasy_name): static
    {
        $this->fantasy_name = $fantasy_name;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(string $company_name): static
    {
        $this->company_name = $company_name;

        return $this;
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(string $cnpj): static
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    public function getOpeningDate(): ?\DateTimeInterface
    {
        return $this->opening_date;
    }

    public function setOpeningDate(\DateTimeInterface $opening_date): static
    {
        $this->opening_date = $opening_date;

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
            $partnerCompany->setCompanyId($this);
        }

        return $this;
    }

    public function removePartnerCompany(PartnerCompany $partnerCompany): static
    {
        if ($this->partnerCompanies->removeElement($partnerCompany)) {
            // set the owning side to null (unless already changed)
            if ($partnerCompany->getCompanyId() === $this) {
                $partnerCompany->setCompanyId(null);
            }
        }

        return $this;
    }

    public function getInvoicing()
    {
        return $this->invoicing;
    }

    public function setInvoicing($invoicing)
    {
        $this->invoicing = $invoicing;

        return $this;
    }

    /**
     * Get the value of createdAt
     */ 
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of updatedAt
     */ 
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     *
     * @return  self
     */ 
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    /**
     * Converte a entidade usuario para um array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'fantasy_name' => $this->getFantasyName(),
            'company_name' => $this->getCompanyName(),
            'cnpj' => $this->getCnpj(),
            'opening_date' => $this->getOpeningDate(),
            'invoicing' => $this->getInvoicing(),
            'phone_number' => $this->getPhoneNumber(),
            'created_at' => $this->getCreatedAt()
        ];
    }
}
