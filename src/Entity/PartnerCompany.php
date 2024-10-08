<?php

namespace App\Entity;

use App\Repository\PartnerCompanyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnerCompanyRepository::class)]
class PartnerCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $participation = null;

    #[ORM\ManyToOne(inversedBy: 'partnerCompanies')]
    private ?Company $company_id = null;

    #[ORM\ManyToOne(inversedBy: 'partnerCompanies')]
    private ?Partner $partner_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipation(): ?string
    {
        return $this->participation;
    }

    public function setParticipation(string $participation): static
    {
        $this->participation = $participation;

        return $this;
    }

    public function getCompanyId(): ?Company
    {
        return $this->company_id;
    }

    public function setCompanyId(?Company $company_id): static
    {
        $this->company_id = $company_id;

        return $this;
    }

    public function getPartnerId(): ?Partner
    {
        return $this->partner_id;
    }

    public function setPartnerId(?Partner $partner_id): static
    {
        $this->partner_id = $partner_id;

        return $this;
    }
}
