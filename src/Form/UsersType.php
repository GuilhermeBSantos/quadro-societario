<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Aqui é validado as informações de um usuário
 */
class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'O nome é obrigatório.']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'O nome é obrigatório.',
                    ]),
                ],
            ])
            ->add('last_name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'O sobrenome é obrigatório.']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'O sobrenome é obrigatório.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'O e-mail é obrigatório.']),
                    new Assert\Email(['message' => 'O e-mail "{{ value }}" é invalido.']),
                ],
            ])
            ->add('password', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'A senha não é valida.']),
                    new Assert\Length([
                        'min' => 7,
                        'minMessage' => 'A senha deve ter pelo menos {{ limit }} caracteres.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'A senha deve conter pelo menos uma letra maiúscula.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Users::class,
            'csrf_protection' => false,
            'constraints' => [
                new UniqueEntity([
                    'entityClass' => Users::class,
                    'fields' => 'email',
                    'message' => 'Este e-mail já está em uso. Por favor, verifique e tente novamente'
                ]),
            ],
        ]);
    }
}
