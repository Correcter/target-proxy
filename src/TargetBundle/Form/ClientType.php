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
 * https://www.adayinthelifeof.nl/2014/03/19/dynamic-form-modification-in-symfony2/
 * http://symfony.com/doc/current/form/dynamic_form_modification.html.
 *
 * @author Vitaly Dergunov
 */
class ClientType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('clientName', TextType::class, [
                'label' => 'Заполните имя агентства',
            ])
            ->add('agencySecret', TextType::class, [
                'label' => 'Заполните secret агентства',
            ])
            ->add('login', TextType::class, [
                'label' => 'Логин',
            ])
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
            ->add('isSandbox', CheckboxType::class, [
                'label' => 'Тестовый аккаунт TargetMail?',
                'label_attr' => [
                    'style' => 'display:inline-block;',
                ],
                'attr' => [
                    'class' => 'regular-checkbox big-checkbox',
                ],
                'required' => false,
                'data' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить Агентство',
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
            'data_class' => \TargetBundle\Entity\TargetClient::class,
        ]);
    }
}
