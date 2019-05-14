<?php

namespace TargetBundle\Form;

use Symfony\Component\Form\AbstractType;
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
class CompanyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('companyName', TextType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить компанию',
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
            'data_class' => \TargetBundle\Entity\TargetCompany::class,
        ]);
    }
}
