<?php

namespace App\Controller;

use App\Entity\PartnerCompany;
use App\Repository\CompanyRepository;
use App\Repository\PartnerCompanyRepository;
use App\Repository\PartnerRepository;
use App\Response\JsonApiResponse;
use App\Validator\PartnerCompanyValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Essa classe é responsavel pelo controle de relações entre sócio e empresa
 */
class PartnerCompanyController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Essa função adiciona uma nova relação entre o socio e a empresa
     * E pego o ID da empresa, CPF do sócio e o valor de participação (no maximo à 100%)
     * Essa função é usada no front de detalhes da empresa, quando é necessario adicionar em novo sócio
     */
    #[Route('/partner_company/store-by-cpf', name: 'app_partner_company_store_cpf')]
    public function storeByCpf(
                        Request $request,
                        PartnerCompanyRepository $partnerCompanyRepository,
                        PartnerRepository $partnerRepository,
                        CompanyRepository $companyRepository
                    ): JsonResponse
    {
        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = PartnerCompanyValidator::validFieldsByCpf($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        //Validação: aqui e consultado no repositorio se a empresa com esse ID realmente existe, caso não, e retornado um erro
        $company = $companyRepository->findOneById($data["company_id"]);
        if(!$company){
            return JsonApiResponse::error("Empresa Inválida", Response::HTTP_BAD_REQUEST);
        }
        
        //Validação: aqui e consultado no repositorio se o socio com esse CPF realmente existe, caso não, e retornado um erro
        $partner = $partnerRepository->findOneByCpf($data["cpf"]);
        if(!$partner){
            $error_message = "Sócio Inválido";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Foi colocado no array das requisições a informação do ID do sócio encontrado
        $data["partner_id"] = $partner->getId();

        //Caso esse sócio já tiver uma relação com essa empresa, e retornado um erro
        if($partnerCompanyRepository->checkPartnerCompanyExists($data)){
            $error_message = "Já existe uma sociedade deste sócio para com essa empresa";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Aqui calcula o total de participação da empresa (por exemplo, se a empresa tiver 4 socios com 20% e a participação for 30%, daria 110% no total, portanto daria um erro)
        $totalParticipationCompany = $partnerCompanyRepository->getTotalParticipationByCompany($company->getId()) + $data["participation"];
        if($totalParticipationCompany > 100){
            $error_message = "As participações da sociedade já excederam 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Instanciada uma nova variavel e setado os valores
        $partnerCompanyEntity = new PartnerCompany();
        $partnerCompanyEntity->setCompanyId($company);
        $partnerCompanyEntity->setPartnerId($partner);
        $partnerCompanyEntity->setParticipation($data["participation"]);

        //Persistir relação no banco de dados
        $this->entityManager->persist($partnerCompanyEntity);
        $this->entityManager->flush();

        //Uso um retorno JSON de sucesso personalizado
        $success_message = "Sociedade cadastrada com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }

    /**
     * Essa função adiciona uma nova relação entre o socio e a empresa
     * E pego o ID do sócio, CNPJ da empresa e o valor de participação (no maximo à 100%)
     * Essa função é usada no front de detalhes do socio, quando é necessario adicionar uma relação com nova empresa
     */
    #[Route('/partner_company/store-by-cnpj', name: 'app_partner_company_store_cnpj')]
    public function storeByCnpj(
                        Request $request,
                        PartnerCompanyRepository $partnerCompanyRepository,
                        PartnerRepository $partnerRepository,
                        CompanyRepository $companyRepository
                    ): JsonResponse
    {
        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = PartnerCompanyValidator::validFieldsCNPJ($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        //Validação: aqui e consultado no repositorio se a empresa com esse CNPJ realmente existe, caso não, e retornado um erro
        $company = $companyRepository->findOneByCnpj($data["cnpj"]);
        if(!$company){
            $error_message = "Empresa Inválida";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Foi colocado no array das requisições a informação do ID do da empresa encontrada
        $data["company_id"] = $company->getId();
        
        //Validação: aqui e consultado no repositorio se o sócio com esse ID realmente existe, caso não, e retornado um erro
        $partner = $partnerRepository->findOneById($data["partner_id"]);
        if(!$partner){
            $error_message = "Sócio Inválido";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Caso esse sócio já tiver uma relação com essa empresa, e retornado um erro
        if($partnerCompanyRepository->checkPartnerCompanyExists($data)){
            $error_message = "Já existe uma sociedade deste sócio para com essa empresa";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Aqui calcula o total de participação da empresa (por exemplo, se a empresa tiver 4 socios com 20% e a participação for 30%, daria 110% no total, portanto daria um erro)
        $totalParticipationCompany = $partnerCompanyRepository->getTotalParticipationByCompany($company->getId()) + $data["participation"];
        if($totalParticipationCompany > 100){
            $error_message = "As participações da sociedade já excederam 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Instanciada uma nova variavel e setado os valores
        $partnerCompanyEntity = new PartnerCompany();
        $partnerCompanyEntity->setCompanyId($company);
        $partnerCompanyEntity->setPartnerId($partner);
        $partnerCompanyEntity->setParticipation($data["participation"]);

        //Persistir relação no banco de dados
        $this->entityManager->persist($partnerCompanyEntity);
        $this->entityManager->flush();

        //Uso um retorno JSON de sucesso personalizado
        $success_message = "Sociedade cadastrada com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }


    /**
     * Essa função adiciona uma nova relação entre o socio e a empresa
     * É pego o ID da empresa, ID do sócio e o valor de participação (no maximo à 100%)
     */
    #[Route('/partner_company/store', name: 'app_partner_company_store')]
    public function store(
                        Request $request,
                        PartnerCompanyRepository $partnerCompanyRepository,
                        PartnerRepository $partnerRepository,
                        CompanyRepository $companyRepository
                    ): JsonResponse
    {
        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = PartnerCompanyValidator::validFields($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        //Validação: aqui e consultado no repositorio se a empresa com esse ID realmente existe, caso não, e retornado um erro
        $company = $companyRepository->findOneById($data["company_id"]);
        if(!$company){
            $error_message = "Empresa Inválida";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }
        
        //Validação: aqui e consultado no repositorio se o sócio com esse ID realmente existe, caso não, e retornado um erro
        $partner = $partnerRepository->findOneById($data["partner_id"]);
        if(!$partner){
            $error_message = "Sócio Inválido";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Caso esse sócio já tiver uma relação com essa empresa, e retornado um erro
        if($partnerCompanyRepository->checkPartnerCompanyExists($data)){
            $error_message = "Já existe uma sociedade deste sócio para com essa empresa";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Aqui calcula o total de participação da empresa (por exemplo, se a empresa tiver 4 socios com 20% e a participação for 30%, daria 110% no total, portanto daria um erro)
        $totalParticipationCompany = $partnerCompanyRepository->getTotalParticipationByCompany($company->getId()) + $data["participation"];
        if($totalParticipationCompany > 100){
            $error_message = "As participações da sociedade já excederam 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Instanciada uma nova variavel e setado os valores
        $partnerCompanyEntity = new PartnerCompany();
        $partnerCompanyEntity->setCompanyId($company);
        $partnerCompanyEntity->setPartnerId($partner);
        $partnerCompanyEntity->setParticipation($data["participation"]);

        //Persistir relação no banco de dados
        $this->entityManager->persist($partnerCompanyEntity);
        $this->entityManager->flush();

        //Uso um retorno JSON de sucesso personalizado
        $success_message = "Sociedade cadastrada com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }
    
    /**
     * Essa função adiciona uma nova relação entre o socio e a empresa
     * É pego o ID da empresa, ID do sócio e o valor de participação (no maximo à 100%)
     */
    #[Route('/partner_company/update/{id}', name: 'app_partner_company_store')]
    public function update($id,
                        Request $request,
                        PartnerCompanyRepository $partnerCompanyRepository
                    ): JsonResponse
    {
        //Aqui usa a classe de Repositorio para buscar a relação por ID
        $partnerCompanyEntity = $partnerCompanyRepository->findOneById($id);
        if(!$partnerCompanyEntity){
            $error_message = "Sociedade não encontrada";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Aqui é validado se existe foi enviado a participação na requisição
        if(!array_key_exists("participation", $data) || !$data["participation"] || is_numeric($data["participation"]) == false){
            $error_message = "A Participação é obrigatória";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Aqui é validado se a participação excede 100
        if(floatval($data["participation"]) > 100){
            $error_message = "A Sociedade não pode ser maior que 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }


        //Aqui calcula o total de participação da empresa (por exemplo, se a empresa tiver 4 socios com 20% e a participação for 30%, daria 110% no total, portanto daria um erro)
        $totalParticipationCompany = ($partnerCompanyRepository->getTotalParticipationByCompany($partnerCompanyEntity->getCompanyId()) - $partnerCompanyEntity->getParticipation()) + $data["participation"];
        if($totalParticipationCompany > 100){
            $error_message = "As participações da sociedade já excederam 100%";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $partnerCompanyEntity->setParticipation($data["participation"]);

        //Persistir relação no banco de dados
        $this->entityManager->persist($partnerCompanyEntity);
        $this->entityManager->flush();


        //Uso um retorno JSON de sucesso personalizado
        $success_message = "Sociedade editada com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }

    /**
     * Essa função deleta uma relação entre empresa e sócio
     */
    #[Route('/partner_company/delete/{id}', name: 'app_partner_company_delete', methods: ['DELETE'])]
    public function delete($id, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        //Aqui usa a classe de Repositorio para buscar a relação por ID
        $companyEntity = $partnerCompanyRepository->findOneById($id);
        if(!$companyEntity){
            $error_message = "Sociedade não encontrada";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Aqui usa a classe de Repositorio para buscar a relação por ID
        $this->entityManager->remove($companyEntity);
        $this->entityManager->flush();

        
        //Uso um retorno JSON de sucesso personalizado
        $success_message = "Sociedade removida com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }
}
