<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\PartnerCompanyRepository;
use App\Response\ErrorsFormResponse;
use App\Response\JsonApiResponse;
use App\Validator\CompanyValidator;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CompanyController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/company/list', name: 'app_company_list', methods: ['GET'])]
    public function list(Request $request, CompanyRepository $companyRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Número de itens por página

        $paginator = $companyRepository->findPaginated($page, $limit);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        $items = array_map(function ($company) {
            return $company->toArray();
        }, $paginator->getIterator()->getArrayCopy());

        return new JsonResponse(JsonApiResponse::paginated($items, $page, $totalPages), Response::HTTP_OK);
    }

    #[Route('/company/show/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show($id, CompanyRepository $companyRepository, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        $company = $companyRepository->findOneById($id);

        if(!$company){
            return new JsonResponse(
                JsonApiResponse::error(
                    Response::HTTP_BAD_REQUEST,
                    'Empresa não encontrada'
                ), 
            Response::HTTP_BAD_REQUEST);
        }

        $company_data = $company->toArray();
        $company_data['partners'] = $partnerCompanyRepository->findPartnerByCompany($company);
        
        return new JsonResponse(
            JsonApiResponse::success(
                Response::HTTP_OK,
                $company_data,
                'Empresa encontrada com sucesso'
            ), 
        Response::HTTP_OK, [], false);
    }

    #[Route('/company/store', name: 'app_company_store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = CompanyValidator::validFields($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }
        
        $companyEntity = new Company();

        $openingDateString = $data['opening_date'];
        $openingDate = \DateTime::createFromFormat('Y-m-d', $openingDateString);
        $companyEntity->setOpeningDate($openingDate);
        

        $form = $this->createForm(CompanyType::class, $companyEntity);
        $form->submit($data);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            $companyEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $companyEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            $this->entityManager->persist($companyEntity);
            $this->entityManager->flush();
    
            return new JsonResponse(
                            JsonApiResponse::success(Response::HTTP_CREATED, [], 'Empresa cadastrada com sucesso'), 
                        Response::HTTP_CREATED, [], false);
        }

        return new JsonResponse(
            JsonApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                ErrorsFormResponse::getFirstFormError($form)
            ), 
        Response::HTTP_BAD_REQUEST);

    }

    
    #[Route('/company/update/{id}', name: 'app_company_update', methods: ['PUT'])]
    public function update($id, Request $request, CompanyRepository $companyRepository): Response
    {
        $companyEntity = $companyRepository->findOneById($id);

        $data = $request->getContent();
        $data = json_decode($data, true);

        
        $form = $this->createForm(CompanyType::class, $companyEntity);
        $form->submit($data);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            $companyEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $companyEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            $this->entityManager->persist($companyEntity);
            $this->entityManager->flush();
    
            return new JsonResponse(
                            JsonApiResponse::success(Response::HTTP_OK,[],'Empresa editada com sucesso'), 
                        Response::HTTP_OK, [], false);
        }

        return new JsonResponse(
                    JsonApiResponse::error(Response::HTTP_BAD_REQUEST,ErrorsFormResponse::getFirstFormError($form)), 
                Response::HTTP_BAD_REQUEST);

    }

    #[Route('/company/delete/{id}', name: 'app_company_delete', methods: ['DELETE'])]
    public function delete($id, CompanyRepository $companyRepository): Response
    {
        $companyEntity = $companyRepository->findOneById($id);

        if(!$companyEntity){
            return new JsonResponse(
                    JsonApiResponse::error(Response::HTTP_BAD_REQUEST, 'Empresa não encontrada'),
                Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->remove($companyEntity);
        $this->entityManager->flush();

        
        return new JsonResponse(
            JsonApiResponse::success(Response::HTTP_OK,[],'Empresa excluida com sucesso'), 
        Response::HTTP_OK, [], false);
    }
}
