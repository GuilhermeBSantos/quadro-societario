<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Form\PartnerType;
use App\Repository\PartnerCompanyRepository;
use App\Repository\PartnerRepository;
use App\Response\ErrorsFormResponse;
use App\Response\JsonApiResponse;
use App\Validator\CPFValidator;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PartnerController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/partner/list', name: 'app_partner_list', methods: ['GET'])]
    public function list(Request $request, PartnerRepository $partnerRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $key = $request->query->getString('key');
        $search = $request->query->getString('search');

        $paginator = $partnerRepository->findPaginated($page, $limit, $key, $search);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        $items = array_map(function ($partner) {
            return $partner->toArray();
        }, $paginator->getIterator()->getArrayCopy());

        return JsonApiResponse::paginated($items, $page, $totalPages);
    }

    #[Route('/partner/show/{id}', name: 'app_partner_show', methods: ['GET'])]
    public function show($id, PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        $partner = $partnerRepository->findOneById($id);

        if(!$partner){
            $error_message = "Socio não encontrado";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $partner_data = $partner->toArray();
        $partner_data['companies'] = $partnerCompanyRepository->findCompanyByPartner($partner);
        
        $success_message = "Socio encontrado com sucesso";
        return JsonApiResponse::success($success_message, $partner_data, Response::HTTP_CREATED);
    }

    #[Route('/partner/store', name: 'app_partner_store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $data = $request->getContent();
        $data = json_decode($data, true);

        $partnerEntity = new Partner();

        $form = $this->createForm(PartnerType::class, $partnerEntity);
        $form->submit($data);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');

            if(CPFValidator::isValidCpf($partnerEntity->getCpf()) == false){
                $error_message = "Este CPF não é valido";
                return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
            }

            $partnerEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $partnerEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            $this->entityManager->persist($partnerEntity);
            $this->entityManager->flush();

            $success_message = "Socio cadastrado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
    }

    #[Route('/partner/update/{id}', name: 'app_partner_update', methods: ['PUT'])]
    public function update($id, Request $request, PartnerRepository $partnerRepository): Response
    {
        $partnerEntity = $partnerRepository->findOneById($id);

        $data = $request->getContent();
        $data = json_decode($data, true);

        $form = $this->createForm(PartnerType::class, $partnerEntity);
        $form->submit($data);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');

            if(CPFValidator::isValidCpf($partnerEntity->getCpf()) == false){
                $error_message = "Este cpf não é valido";
                return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
            }
            
            $partnerEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $partnerEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            $this->entityManager->persist($partnerEntity);
            $this->entityManager->flush();
    
            $success_message = "Socio editado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);

    }
    
    #[Route('/partner/delete/{id}', name: 'app_partner_delete', methods: ['DELETE'])]
    public function delete($id, PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        $companyEntity = $partnerRepository->findOneById($id);

        if(!$companyEntity){
            $error_message = "Sócio não encontrado";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }
        //Remoção das sociedades
        $items = $partnerCompanyRepository->findCompanyByPartner($companyEntity);

        foreach ($items as $item) {
            $companyPartnerEntity = $partnerCompanyRepository->findOneById((int)$item['PC_ID']);
            $this->entityManager->remove($companyPartnerEntity);
        }

        $this->entityManager->remove($companyEntity);
        $this->entityManager->flush();

        
        $success_message = "Socio excluido com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }
}