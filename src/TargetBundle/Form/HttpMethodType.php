<?php

namespace TargetBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Vitaly Dergunov
 */
class HttpMethodType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('httpMethodName', TextType::class)
            ->add('companies', EntityType::class, [
                'placeholder' => '-- Выберите компанию --',
                'class' => \TargetBundle\Entity\TargetCompany::class,
                'multiple' => true,
                'choice_label' => 'companyName',
                'attr' => [
                    'size' => 10,
                    'style' => 'height: 100%;',
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Разрешить HTTP-метод доступа в TargetMail?',
                'required' => true,
                'data' => true,
                'label_attr' => [
                    'style' => 'display:inline-block;',
                ],
                'attr' => [
                    'class' => 'regular-checkbox big-checkbox',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить http метод',
                'attr' => [
                    'class' => 'regular-buton',
                ],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => \TargetBundle\Entity\TargetHttpMethod::class,
        ]);
    }
}
