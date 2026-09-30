<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('name', TextType::class)
            ->add('email', TextType::class)
	    ->add('enable', ChoiceType::class, [
		    'choices' => [
			    'Yes' => true,
			    'No' => false
		    ]
	    ])
	    ->add('birthdate', DateType::class)
            ->add('address', TextType::class)
            ->add('save', SubmitType::class)
        ;
    }
}
