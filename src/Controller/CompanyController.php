<?php

namespace App\Controller;

use DateTimeImmutable;
use DateTimeZone;
use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\PartnerCompanyRepository;
use App\Response\ErrorsFormResponse;
use App\Response\JsonApiResponse;
use App\Validator\CompanyValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Essa classe é responsavel pelo CRUD de empresas (Edição, Adição, Exclusão e Consulta)
 */
class CompanyController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Essa função lista as empresas cadastradas por paginação, também é realizado uma busca por parametros personalizados
     */
    #[Route('/company/list', name: 'app_company_list', methods: ['GET'])]
    public function list(Request $request, CompanyRepository $companyRepository): Response
    {
        //Aqui criei uma paginação tendo 10 por pagina
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        //Aqui criei uma busca, o "key" se refere ao campo que você quer buscar, por exemplo "company_name", e o "search" ao valor que você quer buscar, exemplo "Itaú"
        $key = $request->query->getString('key');
        $search = $request->query->getString('search');

        //Aqui vai para o repository para buscar por paginação e, se o usuario enviar um valor para buscar, vai ser buscado.
        $paginator = $companyRepository->findPaginated($page, $limit, $key, $search);
        $totalItems = count($paginator); // retorna a quantidade de itens
        $totalPages = ceil($totalItems / $limit); //retorna quantidade de paginas

        //Mapeis os dados e transforma em array
        $items = array_map(function ($company) {
            return $company->toArray();
        }, $paginator->getIterator()->getArrayCopy());

        //Uso um JSON de paginação personalizada
        return JsonApiResponse::paginated($items, $page, $totalPages);
    }

    /**
     * Essa função exibe detalhes de determinada empresa cadastrada, juntamente com todos os socios
     */
    #[Route('/company/show/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show($id, CompanyRepository $companyRepository, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        //Aqui usa a classe de Repositorio para buscar a empresa por ID
        $company = $companyRepository->findOneById($id);

        //Validação: se a empresa não existir, retornar um Json personalizado informando que empresa não foi encontrada
        if(!$company){
            $error_message = 'Empresa não encontrada';
            return JsonApiResponse::error($error_message, Response::HTTP_CREATED);
        }

        //Para tratar os dados e pegar um atributo, transformei a entidade em um array
        $company_data = $company->toArray();

        
        //Aqui eu setei no array os socios, pegando no repositorio todos os socios vinculados a empresa
        $company_data['partners'] = $partnerCompanyRepository->findPartnerByCompany($company);
    

        //Uso um JSON de sucesso personalizado
        $success_message = "Empresa encontrada com sucesso";
        return JsonApiResponse::success($success_message, $company_data, Response::HTTP_CREATED);
    }

    /**
     * Essa função cadastra uma nova empresa
     */
    #[Route('/company/store', name: 'app_company_store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos que foram impossibilitados de tratar no DataType
        $fields_validate = CompanyValidator::validFields($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }
        
        //Instancio uma nova Entidade de empresa para cadastro
        $companyEntity = new Company();

        //É tratado a data de abertura da empresa, a fim de ser passado para a entidade no formato correto
        $openingDateString = $data['opening_date'];
        $openingDate = \DateTime::createFromFormat('Y-m-d', $openingDateString);
        $companyEntity->setOpeningDate($openingDate);
        
        //Criado um formulario personalizado a fim de tratar esses dados corretamente
        $form = $this->createForm(CompanyType::class, $companyEntity);
        $form->submit($data);
        
        //Se a validação retornar sucesso, entro nesse if
        if ($form->isSubmitted() && $form->isValid()) {
            //Aqui eu pego a data atual
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            //E converto essa data no formato correto para os campos informando a data de criação e alteração
            $companyEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $companyEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            //Aqui eu persisto os dados no banco de dados
            $this->entityManager->persist($companyEntity);
            $this->entityManager->flush();
    
            //Uso um retorno JSON de sucesso personalizado
            $success_message = "Empresa cadastrada com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        //Aqui e retornado os erros do formulario
        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_CREATED);

    }
    
    /**
     * Essa função edita uma empresa
     */
    #[Route('/company/update/{id}', name: 'app_company_update', methods: ['PUT'])]
    public function update($id, Request $request, CompanyRepository $companyRepository): Response
    {
        //Aqui usa a classe de Repositorio para buscar a empresa por ID
        $companyEntity = $companyRepository->findOneById($id);

        //Validação: se a empresa não existir, retornar um Json personalizado informando que empresa não foi encontrada
        if(!$companyEntity){
            $error_message = 'Empresa não encontrada';
            return JsonApiResponse::error($error_message, Response::HTTP_CREATED);
        }

        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos que foram impossibilitados de tratar no DataType
        $fields_validate = CompanyValidator::validFields($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        //Criado um formulario personalizado a fim de tratar esses dados corretamente
        $form = $this->createForm(CompanyType::class, $companyEntity);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            //Aqui eu pego a data atual
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            //E converto essa data no formato correto para os campos informando a data de alteração
            $companyEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            //Aqui eu persisto os dados no banco de dados
            $this->entityManager->persist($companyEntity);
            $this->entityManager->flush();
    
            //Uso um retorno JSON de sucesso personalizada
            $success_message = "Empresa editada com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        //Aqui e retornado os erros do formulario
        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_CREATED);

    }

    /**
     * Essa função deleta uma empresa
     */
    #[Route('/company/delete/{id}', name: 'app_company_delete', methods: ['DELETE'])]
    public function delete($id, CompanyRepository $companyRepository, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        //Aqui usa a classe de Repositorio para buscar a empresa por ID
        $companyEntity = $companyRepository->findOneById($id);

        //Validação: se a empresa não existir, retornar um Json personalizado informando que empresa não foi encontrada
        if(!$companyEntity){
            $error_message = 'Empresa não encontrada';
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //E feito uma busca de todos os socios vinculados
        $items = $partnerCompanyRepository->findPartnerByCompany($companyEntity);

        //Caso encontrado relações entre empresa e socio, essas relações são deletadas
        foreach ($items as $item) {
            $companyPartnerEntity = $partnerCompanyRepository->findOneById((int)$item['PC_ID']);
            $this->entityManager->remove($companyPartnerEntity);
        }

        //Aqui eu persisto a remoção no banco de dados
        $this->entityManager->remove($companyEntity);
        $this->entityManager->flush();

        //Uso um retorno JSON de sucesso personalizada
        $success_message = "Empresa excluida com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }
}
