<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Partner;
use App\Entity\PartnerCompany;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class PartnerCompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('participation', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'A Participação é obrigatorio.']),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'A Participação é inválido.',
                    ]),
                    new Assert\Range([
                        'min' => 0,
                        'minMessage' => 'A Participação deve ser maior que 0.'
                    ]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => PartnerCompany::class,
            'csrf_protection' => false
        ]);
    }
}
