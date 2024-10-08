<?php

namespace App\Form;

use App\Entity\Partner;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class PartnerType extends AbstractType
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
            ->add('cpf', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'O cpf é obrigatório']),
                    new Assert\Length([
                        'min' => 11,
                        'max' => 11,
                        'minMessage' => 'O cpf é obrigatório.',
                        'maxMessage' => 'Formato incorreto de CPF.',
                    ]),
                ],
            ])
            ->add('phone_number', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'O Telefone é obrigatório.']),
                    new Assert\Length([
                        'min' => 9,
                        'max' => 11,
                        'minMessage' => 'O numero de telefone é invalido.',
                        'maxMessage' => 'O numero de telefone é invalido.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'O numero de telefone é invalido',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Partner::class,
            'csrf_protection' => false,
            'constraints' => [
                new UniqueEntity([
                    'entityClass' => Partner::class,
                    'fields' => 'cpf',
                ]),
                new UniqueEntity([
                    'entityClass' => Partner::class,
                    'fields' => 'email',
                ]),
            ],
        ]);
    }
}
