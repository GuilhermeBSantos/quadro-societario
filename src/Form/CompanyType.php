<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Aqui é validado as informações de empresa em caso de cadastro e edição
 */
class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fantasy_name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'O nome fantasia é obrigatório.']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'O nome fantasia é obrigatório.',
                    ]),
                ],
            ])
            ->add('company_name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'A razão social é obrigatória.']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'A razão social é obrigatório.',
                    ]),
                ],
            ])
            ->add('cnpj', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'O CNPJ é obrigatório.']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'O CNPJ é obrigatório.',
                    ]),
                ],
            ])
            ->add('opening_date', DateType::class, [
                'required' => true,
                'format' => 'yyyy-MM-dd',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'A data de abertura não é valida.'])
                ],
            ])
            ->add('phone_number', TextType::class, [
                'required' => true,
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
            ])
            ->add('invoicing', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'O valor de faturamento é obrigatorio.']),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'O valor de faturamento é inválido.',
                    ]),
                    new Assert\Range([
                        'min' => 0,
                        'minMessage' => 'Valor de faturamento deve ser maior que 0.'
                    ]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => Company::class,
            'csrf_protection' => false,
            'constraints' => [
                new UniqueEntity([
                    'entityClass' => Company::class,
                    'fields' => 'cnpj',
                    'message' => 'Já existe uma empresa com esse CNPJ.',
                ]),
            ],
        ]);
    }
}
