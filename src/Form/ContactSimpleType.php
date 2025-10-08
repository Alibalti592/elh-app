<?php

namespace App\Form;

use App\Model\ContactFormObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactSimpleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class ,[
                'label' => 'Nom'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('content', TextareaType::class, array(
                'label' => 'Message',
                'required' => true
            ))
            //HONEYPOT SPAM
            ->add('phone', TextType::class, [
                "label" => "Téléphone",
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'phone',
                    'tabindex' => -1,
                    'autocomplete' => 'off'
                ]
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ContactFormObject::class
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'contact_form';
    }
}