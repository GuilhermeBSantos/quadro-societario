<?php

namespace App\Form;

use App\Entity\PartnerCompany;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Aqui é validado as informações de relação entre socio e empresa
 */
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
