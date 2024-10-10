<?php

namespace App\Controller;

use DateTimeImmutable;
use DateTimeZone;
use App\Entity\Partner;
use App\Form\PartnerType;
use App\Response\JsonApiResponse;
use App\Response\ErrorsFormResponse;
use App\Repository\PartnerCompanyRepository;
use App\Repository\PartnerRepository;
use App\Validator\CPFValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Essa classe é responsavel pelo CRUD de empresas (Edição, Adição, Exclusão e Consulta)
 */
class PartnerController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Essa função lista as empresas cadastradas por paginação, também é realizado uma busca por parametros personalizados
     */
    #[Route('/partner/list', name: 'app_partner_list', methods: ['GET'])]
    public function list(Request $request, PartnerRepository $partnerRepository): Response
    {
        //Aqui criei uma paginação tendo 10 por pagina
        $page = $request->query->getInt('page', 1);
        $limit = 10;

        //Aqui criei uma busca, o "key" se refere ao campo que você quer buscar, por exemplo "name", e o "search" ao valor que você quer buscar, exemplo "Guilherme"
        $key = $request->query->getString('key');
        $search = $request->query->getString('search');

        //Aqui vai para o repository para buscar por paginação e, se o usuario enviar um valor para buscar, vai ser buscado.
        $paginator = $partnerRepository->findPaginated($page, $limit, $key, $search);
        $totalItems = count($paginator); // retorna a quantidade de itens
        $totalPages = ceil($totalItems / $limit); //retorna quantidade de paginas

        //Mapeis os dados e transforma em array
        $items = array_map(function ($partner) {
            return $partner->toArray();
        }, $paginator->getIterator()->getArrayCopy());

        //Uso um JSON de paginação personalizada
        return JsonApiResponse::paginated($items, $page, $totalPages);
    }


    /**
     * Essa função exibe detalhes de determinado sócio cadastrado, juntamente com todas as relações com as empresas
     */
    #[Route('/partner/show/{id}', name: 'app_partner_show', methods: ['GET'])]
    public function show($id, PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        //Aqui usa a classe de Repositorio para buscar o sócio por ID
        $partner = $partnerRepository->findOneById($id);

        //Validação: se o sócio não existir, retornar um Json personalizado informando que o sócio não foi encontrada
        if(!$partner){
            $error_message = "Socio não encontrado";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Para tratar os dados e pegar um atributo, transformei a entidade em um array
        $partner_data = $partner->toArray();

        //Aqui eu setei no array as empresas, pegando no repositorio todas as empresas vinculados ao sócio
        $partner_data['companies'] = $partnerCompanyRepository->findCompanyByPartner($partner);
        
        //Uso um JSON de sucesso personalizado
        $success_message = "Socio encontrado com sucesso";
        return JsonApiResponse::success($success_message, $partner_data, Response::HTTP_CREATED);
    }

    /**
     * Essa função cadastra uma nova empresa
     */
    #[Route('/partner/store', name: 'app_partner_store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Instancio uma nova Entidade de sócio para cadastro
        $partnerEntity = new Partner();

        //Criado um formulario personalizado a fim de tratar esses dados corretamente
        $form = $this->createForm(PartnerType::class, $partnerEntity);
        $form->submit($data);
        
        //Se a validação retornar sucesso, entro nesse if
        if ($form->isSubmitted() && $form->isValid()) {
            //Aqui eu pego a data atual
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');

            //Aqui é validado o CPF
            if(CPFValidator::isValidCpf($partnerEntity->getCpf()) == false){
                $error_message = "Este CPF não é valido";
                return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
            }

            //E converto essa data no formato correto para os campos informando a data de criação e alteração
            $partnerEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $partnerEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            //Aqui eu persisto os dados no banco de dados
            $this->entityManager->persist($partnerEntity);
            $this->entityManager->flush();

            //Uso um retorno JSON de sucesso personalizado
            $success_message = "Socio cadastrado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        //Aqui e retornado os erros do formulario
        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Essa função cadastra uma novo sócio
     */
    #[Route('/partner/update/{id}', name: 'app_partner_update', methods: ['PUT'])]
    public function update($id, Request $request, PartnerRepository $partnerRepository): Response
    {
        //Aqui usa a classe de Repositorio para buscar o sócio por ID
        $partnerEntity = $partnerRepository->findOneById($id);

        //Validação: se o sócio não existir, retornar um Json personalizado informando que o sócio não foi encontrada
        if(!$partnerEntity){
            $error_message = "Socio não encontrado";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Criado um formulario personalizado a fim de tratar esses dados corretamente
        $form = $this->createForm(PartnerType::class, $partnerEntity);
        $form->submit($data);
        
        //Se a validação retornar sucesso, entro nesse if
        if ($form->isSubmitted() && $form->isValid()) {
            //Aqui eu pego a data atual
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');

            //Aqui é validado o CPF
            if(CPFValidator::isValidCpf($partnerEntity->getCpf()) == false){
                $error_message = "Este cpf não é valido";
                return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
            }

            //E converto essa data no formato correto para os campos informando a data de criação e alteração
            $partnerEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            //Aqui eu persisto os dados no banco de dados
            $this->entityManager->persist($partnerEntity);
            $this->entityManager->flush();
    
            //Uso um retorno JSON de sucesso personalizado
            $success_message = "Socio editado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        //Aqui e retornado os erros do formulario
        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);

    }
    
    /**
     * Essa função deleta um sócio
     */
    #[Route('/partner/delete/{id}', name: 'app_partner_delete', methods: ['DELETE'])]
    public function delete($id, PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository): Response
    {
        //Aqui usa a classe de Repositorio para buscar o sócio por ID
        $partnerEntity = $partnerRepository->findOneById($id);

        //Validação: se o sócio não existir, retornar um Json personalizado informando que o sócio não foi encontrada
        if(!$partnerEntity){
            $error_message = "Sócio não encontrado";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }
        
        //Caso encontrado relações entre empresa e socio, essas relações são deletadas
        $items = $partnerCompanyRepository->findCompanyByPartner($partnerEntity);

        //Caso encontrado relações entre empresa e socio, essas relações são deletadas
        foreach ($items as $item) {
            $companyPartnerEntity = $partnerCompanyRepository->findOneById((int)$item['PC_ID']);
            $this->entityManager->remove($companyPartnerEntity);
        }

        //Aqui eu persisto a remoção no banco de dados
        $this->entityManager->remove($partnerEntity);
        $this->entityManager->flush();

        //Uso um retorno JSON de sucesso personalizada
        $success_message = "Socio excluido com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }
}