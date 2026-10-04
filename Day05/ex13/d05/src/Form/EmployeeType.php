<?php

namespace App\Form;

use App\Entity\Employee;
use App\Enum\Hours;
use App\Enum\Position;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('firstname', TextType::class)

            ->add('lastname', TextType::class)

            ->add('email', EmailType::class)

            ->add('birthdate', DateType::class, [
                'widget' => 'single_text'
            ])

            ->add('active', CheckboxType::class, [
                'required' => false
            ])

            ->add('employedSince', DateType::class, [
                'widget' => 'single_text'
            ])

            ->add('employedUntil', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])

            ->add('hours', ChoiceType::class, [
                'choices' => [
                    '8 hours' => Hours::FULL_TIME,
                    '6 hours' => Hours::PART_TIME,
                    '4 hours' => Hours::HALF_TIME
                ]
            ])

            ->add('salary', IntegerType::class)

            ->add('position', ChoiceType::class, [
                'choices' => [
                    'Manager' =>
                        Position::MANAGER,

                    'Account Manager' =>
                        Position::ACCOUNT_MANAGER,

                    'QA Manager' =>
                        Position::QA_MANAGER,

                    'Dev Manager' =>
                        Position::DEV_MANAGER,

                    'CEO' =>
                        Position::CEO,

                    'COO' =>
                        Position::COO,

                    'Backend Developer' =>
                        Position::BACKEND_DEV,

                    'Frontend Developer' =>
                        Position::FRONTEND_DEV,

                    'QA Tester' =>
                        Position::QA_TESTER
                ]
            ])

            ->add('manager', EntityType::class, [
                'class' => Employee::class,
                'choice_label' => function (
                    Employee $employee
                ): string {
                    return $employee->getFirstname()
                        . ' '
                        . $employee->getLastname();
                },
                'required' => false,
                'placeholder' => 'No manager'
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Save'
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Employee::class
        ]);
    }
}