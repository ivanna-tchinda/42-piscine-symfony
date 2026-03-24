<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\BankAccount;
use App\Entity\Person;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('name')
            ->add('email')
            ->add('enable')
            ->add('birthdate')
            ->add('mobile')
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'id',
            ])
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
                'choice_label' => 'id',
	    ])
	    ->add('save', SubmitType::class, ['label' => 'Create Person'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
