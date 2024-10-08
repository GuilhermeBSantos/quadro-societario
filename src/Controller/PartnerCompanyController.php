<?php

namespace App\Controller;

use App\Entity\PartnerCompany;
use App\Form\PartnerCompanyType;
use App\Repository\CompanyRepository;
use App\Repository\PartnerCompanyRepository;
use App\Repository\PartnerRepository;
use App\Response\ErrorsFormResponse;
use App\Response\JsonApiResponse;
use App\Validator\PartnerCompanyValidator;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class PartnerCompanyController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/partner_company/store-by-cpf', name: 'app_partner_company_store_cpf')]
    public function storeByCpf(
                        Request $request,
                        PartnerCompanyRepository $partnerCompanyRepository,
                        PartnerRepository $partnerRepository,
                        CompanyRepository $companyRepository
                    ): JsonResponse
    {
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = PartnerCompanyValidator::validFieldsByCpf($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        $company = $companyRepository->findOneById($data["company_id"]);
        if(!$company){
            return JsonApiResponse::error("Empresa Inválida", Response::HTTP_BAD_REQUEST);
        }
        
        $partner = $partnerRepository->findOneByCpf($data["cpf"]);
        if(!$partner){
            $error_message = "Sócio Inválido";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $data["partner_id"] = $partner->getId();


        if($partnerCompanyRepository->checkPartnerCompanyExists($data)){
            $error_message = "Já existe uma sociedade deste sócio para com essa empresa";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $totalParticipationCompany = $partnerCompanyRepository->getTotalParticipationByCompany($company->getId()) + $data["participation"];

        if($totalParticipationCompany > 100){
            $error_message = "As participações da sociedade já excederam 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $partnerCompanyEntity = new PartnerCompany();

        $partnerCompanyEntity->setCompanyId($company);
        $partnerCompanyEntity->setPartnerId($partner);
        $partnerCompanyEntity->setParticipation($data["participation"]);

        $this->entityManager->persist($partnerCompanyEntity);
        $this->entityManager->flush();

        $success_message = "Sociedade cadastrada com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }

    #[Route('/partner_company/store-by-cnpj', name: 'app_partner_company_store_cnpj')]
    public function storeByCnpj(
                        Request $request,
                        PartnerCompanyRepository $partnerCompanyRepository,
                        PartnerRepository $partnerRepository,
                        CompanyRepository $companyRepository
                    ): JsonResponse
    {
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = PartnerCompanyValidator::validFieldsCNPJ($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        $company = $companyRepository->findOneByCnpj($data["cnpj"]);
        if(!$company){
            $error_message = "Empresa Inválida";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $data["company_id"] = $company->getId();
        
        $partner = $partnerRepository->findOneById($data["partner_id"]);
        if(!$partner){
            $error_message = "Sócio Inválido";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        if($partnerCompanyRepository->checkPartnerCompanyExists($data)){
            $error_message = "Já existe uma sociedade deste sócio para com essa empresa";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $totalParticipationCompany = $partnerCompanyRepository->getTotalParticipationByCompany($company->getId()) + $data["participation"];

        if($totalParticipationCompany > 100){
            $error_message = "As participações da sociedade já excederam 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $partnerCompanyEntity = new PartnerCompany();

        $partnerCompanyEntity->setCompanyId($company);
        $partnerCompanyEntity->setPartnerId($partner);
        $partnerCompanyEntity->setParticipation($data["participation"]);

        $this->entityManager->persist($partnerCompanyEntity);
        $this->entityManager->flush();

        $success_message = "Sociedade cadastrada com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }


    #[Route('/partner_company/store', name: 'app_partner_company_store')]
    public function store(
                        Request $request,
                        PartnerCompanyRepository $partnerCompanyRepository,
                        PartnerRepository $partnerRepository,
                        CompanyRepository $companyRepository
                    ): JsonResponse
    {
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = PartnerCompanyValidator::validFields($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        $company = $companyRepository->findOneById($data["company_id"]);
        if(!$company){
            $error_message = "Empresa Inválida";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }
        
        $partner = $partnerRepository->findOneById($data["partner_id"]);
        if(!$partner){
            $error_message = "Sócio Inválido";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        if($partnerCompanyRepository->checkPartnerCompanyExists($data)){
            $error_message = "Já existe uma sociedade deste sócio para com essa empresa";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $totalParticipationCompany = $partnerCompanyRepository->getTotalParticipationByCompany($company->getId()) + $data["participation"];

        if($totalParticipationCompany > 100){
            $error_message = "As participações da sociedade já excederam 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $partnerCompanyEntity = new PartnerCompany();

        $partnerCompanyEntity->setCompanyId($company);
        $partnerCompanyEntity->setPartnerId($partner);
        $partnerCompanyEntity->setParticipation($data["participation"]);

        $this->entityManager->persist($partnerCompanyEntity);
        $this->entityManager->flush();


        $success_message = "Sociedade cadastrada com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }

    
    #[Route('/partner_company/update/{id}', name: 'app_partner_company_store')]
    public function update($id,
                        Request $request,
                        PartnerCompanyRepository $partnerCompanyRepository
                    ): JsonResponse
    {
        $partnerCompanyEntity = $partnerCompanyRepository->findOneById($id);

        if(!$partnerCompanyEntity){
            $error_message = "Sociedade não encontrada";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $data = $request->getContent();
        $data = json_decode($data, true);

        if(!array_key_exists("participation", $data) || !$data["participation"] || is_numeric($data["participation"]) == false){
            $error_message = "A Participação é obrigatória";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        if(floatval($data["participation"]) > 100){
            $error_message = "A Sociedade não pode ser maior que 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }


        $totalParticipationCompany = ($partnerCompanyRepository->getTotalParticipationByCompany($partnerCompanyEntity->getCompanyId()) - $partnerCompanyEntity->getParticipation()) + $data["participation"];

        if($totalParticipationCompany > 100){
            $error_message = "As participações da sociedade já excederam 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $partnerCompanyEntity->setParticipation($data["participation"]);

        $this->entityManager->persist($partnerCompanyEntity);
        $this->entityManager->flush();

        $success_message = "Sociedade editada com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }

    #[Route('/partner_company/delete/{id}', name: 'app_partner_company_delete', methods: ['DELETE'])]
    public function delete($id, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        $companyEntity = $partnerCompanyRepository->findOneById($id);

        if(!$companyEntity){
            $error_message = "Sociedade não encontrada";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->remove($companyEntity);
        $this->entityManager->flush();

        
        $success_message = "Sociedade removida com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }
}
