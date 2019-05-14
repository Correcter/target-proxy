<?php

namespace TargetBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Vitaly Dergunov
 */
class AgencyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('agencyName', TextType::class)
            ->add('client', EntityType::class, [
                'placeholder' => '-- Выберите агентство --',
                'class' => \TargetBundle\Entity\TargetClient::class,
                'choice_label' => 'clientName',
            ])
//            ->add('tokens', EntityType::class, [
//                'placeholder' => '-- Выберите токен --',
//                'class' => \TargetBundle\Entity\TargetClientToken::class,
//                'multiple' => true,
//                'required' => false,
//                'choice_label' => 'accessToken',
//                'attr' => [
//                    'size' => 10,
//                    'style' => 'height: 150px;',
//                ],
//            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить клиента',
                'attr' => [
                    'class' => 'regular-buton',
                ],
            ])
            ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => \TargetBundle\Entity\TargetAgency::class,
        ]);
    }
}
