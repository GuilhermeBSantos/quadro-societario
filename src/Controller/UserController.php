<?php

namespace App\Controller;

use DateTimeImmutable;
use DateTimeZone;
use App\Entity\Users;
use App\Form\UsersType;
use App\Form\UsersTypeByUpdate;
use App\Repository\UsersRepository;
use App\Response\ErrorsFormResponse;
use App\Response\JsonApiResponse;
use App\Validator\UsersValidator;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Essa classe é responsavel pelo controle de usuarios
 */
class UserController extends AbstractController
{
    private $jwtTokenManager;
    private $entityManager;
    private $passwordManager;

    public function __construct(JWTTokenManagerInterface $jwtTokenManager,
                                EntityManagerInterface $entityManager, 
                                UserPasswordHasherInterface $passwordManager)
    {
        $this->jwtTokenManager = $jwtTokenManager;
        $this->entityManager = $entityManager;
        $this->passwordManager = $passwordManager;
    }
    
    /**
     * Essa função lista os usuários cadastradas por paginação
     */
    #[Route('/user/list', name: 'app_user_list', methods: ['GET'])]
    public function list(Request $request, UsersRepository $repository): Response
    {
        //Aqui criei uma paginação tendo 10 por pagina
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Número de itens por página

        //Aqui vai para o repository para buscar por paginação e, se o usuario enviar um valor para buscar, vai ser buscado.
        $paginator = $repository->findPaginated($page, $limit);
        $totalItems = count($paginator); // retorna a quantidade de itens
        $totalPages = ceil($totalItems / $limit); //retorna quantidade de paginas

        //Mapeis os dados e transforma em array
        $items = array_map(function ($user) {
            return $user->toArray();
        }, $paginator->getIterator()->getArrayCopy());

        //Uso um JSON de paginação personalizada
        return JsonApiResponse::paginated($items, $page, $totalPages);
    }
    
    /**
     * Essa função cadastra o primeiro acesso do usuario
     */
    #[Route('/user/first-access', name: 'app_user_first_access', methods: ['POST'])]
    public function first_access(UsersRepository $repository, Request $request): JsonResponse
    {
        //Se já existir um usuario cadastrado, vai dar um erro
        if($repository->countUsersByFirstUse() > 0){
            $error_message = "Já existe um administrador cadastrado no sistema";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $data = $request->getContent();

        //Instancia um novo usuário
        $userEntity = new Users();
        
        //Criado um formulario personalizado a fim de tratar esses dados corretamente
        $form = $this->createForm(UsersType::class, $userEntity);
        $form->submit(json_decode($data, true));
        
        //Se a validação retornar sucesso, entro nesse if
        if ($form->isSubmitted() && $form->isValid()) {
            //Aqui eu pego a data atual
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            //Coloca uma criptografia para a senha
            $userEntity->setPassword($this->passwordManager->hashPassword($userEntity, $form->get('password')->getData()));

            //Como primeiro acesso eu deixo padrão como administrador
            $userEntity->setRoles(['ROLE_ADMIN']);
            
            //E converto essa data no formato correto para os campos informando a data de criação e alteração
            $userEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $userEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            //Aqui eu persisto os dados no banco de dados
            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();
            
            //Uso um retorno JSON de sucesso personalizado
            $success_message = "Usuario cadastrado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        //Aqui e retornado os erros do formulario
        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Essa função cadastra uma novo usuário
     */
    #[Route('/user/store', name: 'app_user_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $data_validate = json_decode($data, true);

        //Instancia um novo usuário
        $userEntity = new Users();
        
        //Criado um formulario personalizado a fim de tratar esses dados corretamente
        $form = $this->createForm(UsersType::class, $userEntity);
        $form->submit(json_decode($data, true));
        
        //Se a validação retornar sucesso, entro nesse if
        if ($form->isSubmitted() && $form->isValid()) {
            //Aqui valida se o acesso foi enviado na requisição
            if(!array_key_exists("roles", $data_validate) || !$data_validate["roles"]){
                $error_message = "O acesso é obrigatório";
                return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
            }

            //Aqui valida se o acesso é valido
            if($data_validate['roles'] != 'ROLE_USER' && $data_validate['roles'] != 'ROLE_ADMIN'){
                $error_message = "O acesso é inválido";
                return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
            }

            //Aqui eu pego a data atual
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            //Coloca uma criptografia para a senha
            $userEntity->setPassword($this->passwordManager->hashPassword($userEntity, $form->get('password')->getData()));
            
            //Insere a permissão na entidade
            $userEntity->setRoles([$data_validate['roles']]);
            
            //E converto essa data no formato correto para os campos informando a data de criação e alteração
            $userEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $userEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            //Aqui eu persisto os dados no banco de dados
            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();
            
            //Uso um retorno JSON de sucesso personalizado
            $success_message = "Usuario cadastrado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        //Aqui e retornado os erros do formulario
        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
    }
    
    /**
     * Essa função cadastra uma novo usuário
     */
    #[Route('/user/update/{id}', name: 'app_company_update', methods: ['PUT'])]
    public function update($id, Request $request, UsersRepository $repository): Response
    {
        //Aqui usa a classe de Repositorio para buscar o usuário por ID
        $userEntity = $repository->findOneById($id);

        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Aqui eu criei duas classes para validação, se a senha for enviada para edição validar, se não for enviada, não validar
        if(array_key_exists("password", $data) && $data["password"]){
            $form = $this->createForm(UsersType::class, $userEntity);
        }
        else{
            $form = $this->createForm(UsersTypeByUpdate::class, $userEntity);
        }
        $form->submit($data);
        
        
        //Se a validação retornar sucesso, entro nesse if
        if ($form->isSubmitted() && $form->isValid()) {
            //Aqui valida se o acesso foi enviado na requisição
            if(!array_key_exists("roles", $data) || !$data["roles"]){
                $error_message = "O acesso é obrigatório";
                return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
            }

            //Aqui valida se o acesso é valido
            if($data['roles'] != 'ROLE_USER' && $data['roles'] != 'ROLE_ADMIN'){
                $error_message = "O acesso é inválido";
                return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
            }
            
            //Coloca uma criptografia para a senha caso a senha for enviada
            if(array_key_exists("password", $data) && $data["password"]){
                $userEntity->setPassword($this->passwordManager->hashPassword($userEntity, $form->get('password')->getData()));
            }
            
            //Insere a permissão na entidade
            $userEntity->setRoles([$data['roles']]);

            //Aqui eu pego a data atual
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            
            //E converto essa data no formato correto para os campos informando a data de alteração
            $userEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            //Aqui eu persisto os dados no banco de dados
            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();
            
            //Uso um retorno JSON de sucesso personalizado
            $success_message = "Usuario cadastrado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        //Aqui e retornado os erros do formulario
        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_CREATED);

    }
    
    /**
     * Essa função realiza o login
     */
    #[Route('/user/login', name: 'app_user_login', methods: ['POST'])]
    public function login(Request $request, UsersRepository $repository, UserPasswordHasherInterface $passwordEncoder): JsonResponse
    {
        //É transformados as requisições de "form-type" em array, para ser tratados corretamente
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = UsersValidator::validFields($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        $email = $data['email'];
        $password = $data['password'];

        //Busca o usuario por email
        $user = $repository->findOneByEmail($email);

        //Se usuario pelo email não for achado, ou a senha não for equivalente, retornar um erro
        if(!$user || !$passwordEncoder->isPasswordValid($user, $password)){
            $error_message = "E-mail ou senha invalidos";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Gera um token JWT
        $token = $this->jwtTokenManager->create($user);
        
        //Uso um retorno JSON de sucesso personalizado com o token JWT
        $success_message = "Usuario cadastrado com sucesso";
        return JsonApiResponse::success($success_message, ['token' => $token ], Response::HTTP_OK);
    }
    
    /**
     * Essa função realiza o login
     */
    #[Route('/user/info', name: 'app_user_info', methods: ['GET'])]
    public function login_info(Security $security, Request $request)
    {
        // O usuario é pego de acordo com o Bearer Token JWT
        $user = $security->getUser();
        
        //Caso expirado ou não encontrado, retornar erro
        if (!$user) {
            $error_message = "Credenciais invalidas ou expiradas, você devera fazer o login novamente";
            return JsonApiResponse::error($error_message, Response::HTTP_UNAUTHORIZED); 
        }

        //Retorno JSON de sucesso personalizado com informações do usuario por token
        $success_message = "Usuário encontrado";
        $data_request = ['status' => 'success', 'user' => $user->toArray()];
        return JsonApiResponse::success($success_message, $data_request, Response::HTTP_OK);
    }

    
    /**
     * Essa função deleta um usuario
     */
    #[Route('/user/delete/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete($id, UsersRepository $repository): Response
    {
        //Aqui usa a classe de Repositorio para buscar o usuário por ID
        $userEntity = $repository->findOneById($id);

        //Caso usuario não for achado, retornar erro
        if(!$userEntity){
            $error_message = "Usuário não encontrado";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        //Persistir remoção no banco de dados
        $this->entityManager->remove($userEntity);
        $this->entityManager->flush();
        
        //Retorno JSON de sucesso personalizado com informações do usuario por token
        $success_message = "Usuário excluido com sucesso";
        return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
    }
}
