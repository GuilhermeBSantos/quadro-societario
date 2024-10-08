<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use App\Response\ErrorsFormResponse;
use App\Response\JsonApiResponse;
use App\Validator\UsersValidator;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserController extends AbstractController
{
    private $jwtTokenManager;
    private $entityManager;
    private $passwordManager;
    private $tokenStorage;

    public function __construct(JWTTokenManagerInterface $jwtTokenManager,
                                EntityManagerInterface $entityManager, 
                                UserPasswordHasherInterface $passwordManager,
                                TokenStorageInterface $tokenStorage)
    {
        $this->jwtTokenManager = $jwtTokenManager;
        $this->entityManager = $entityManager;
        $this->passwordManager = $passwordManager;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/user/list', name: 'app_user_list', methods: ['GET'])]
    public function list(Request $request, UsersRepository $repository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Número de itens por página

        $paginator = $repository->findPaginated($page, $limit);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        $items = array_map(function ($user) {
            return $user->toArray();
        }, $paginator->getIterator()->getArrayCopy());

        return JsonApiResponse::paginated($items, $page, $totalPages);
    }

    #[Route('/user/first-access', name: 'app_user_first_access', methods: ['POST'])]
    public function first_access(UsersRepository $repository, Request $request): JsonResponse
    {
        if($repository->countUsersByFirstUse() > 0){
            $error_message = "Já existe um administrador cadastrado no sistema";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $data = $request->getContent();

        $userEntity = new Users();
        
        $form = $this->createForm(UsersType::class, $userEntity);
        $form->submit(json_decode($data, true));
        
        if ($form->isSubmitted() && $form->isValid()) {
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            $userEntity->setPassword($this->passwordManager->hashPassword($userEntity, $form->get('password')->getData()));
            $userEntity->setRoles(['ROLE_ADMIN']);
            
            $userEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $userEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();
            
            $success_message = "Usuario cadastrado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
    }

    
    #[Route('/user/store', name: 'app_user_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = $request->getContent();

        $userEntity = new Users();
        
        $form = $this->createForm(UsersType::class, $userEntity);
        $form->submit(json_decode($data, true));
        
        if ($form->isSubmitted() && $form->isValid()) {
            $dateTimeZone = new DateTimeZone('America/Sao_Paulo');
            
            $userEntity->setPassword($this->passwordManager->hashPassword($userEntity, $form->get('password')->getData()));
            $userEntity->setRoles(['ROLE_ADMIN']);
            
            $userEntity->setCreatedAt(new DateTimeImmutable('now', $dateTimeZone));
            $userEntity->setUpdatedAt(new DateTimeImmutable('now', $dateTimeZone));

            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();
    
            $success_message = "Usuario cadastrado com sucesso";
            return JsonApiResponse::success($success_message, [], Response::HTTP_CREATED);
        }

        $error_message = ErrorsFormResponse::getFirstFormError($form);
        return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
    }

    #[Route('/user/login', name: 'app_user_login', methods: ['POST'])]
    public function login(Request $request, 
                            UsersRepository $repository, 
                            UserPasswordHasherInterface $passwordEncoder): JsonResponse
    {
        $data = $request->getContent();
        $data = json_decode($data, true);

        //Validação de campos
        $fields_validate = UsersValidator::validFields($data);
        if($fields_validate["status"] == 'error'){
            return new JsonResponse($fields_validate, $fields_validate['code']);
        }

        $email = $data['email'];
        $password = $data['password'];

        $user = $repository->findOneByEmail($email);
        if(!$user || !$passwordEncoder->isPasswordValid($user, $password)){
            $error_message = "E-mail ou senha invalidos";
            return JsonApiResponse::error($error_message, Response::HTTP_BAD_REQUEST);
        }

        $token = $this->jwtTokenManager->create($user);
        
        $success_message = "Usuario cadastrado com sucesso";
        return JsonApiResponse::success($success_message, ['token' => $token ], Response::HTTP_OK);
    }

    #[Route('/user/info', name: 'app_user_info', methods: ['GET'])]
    public function login_info(Security $security, Request $request)
    {
        // O LexikJWTAuthenticationBundle gera o JWT automaticamente quando o usuário é autenticado com sucesso
        $user = $security->getUser();
        
        if (!$user) {
            $error_message = "Credenciais invalidas ou expiradas, você devera fazer o login novamente";
            return JsonApiResponse::error($error_message, Response::HTTP_UNAUTHORIZED); 
        }

        $success_message = "Usuário encontrado";
        $data_request = ['status' => 'success', 'user' => $user->toArray()];
        return JsonApiResponse::success($success_message, $data_request, Response::HTTP_OK);
    }
}
