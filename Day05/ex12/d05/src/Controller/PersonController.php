<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\BankAccount;
use App\Entity\Person;
use App\Repository\PersonRepository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

final class PersonController extends AbstractController
{
    /*
     * Create ORM tables from entities.
     */
    public function create_orm_tables(Connection $connection): void
    {
        $schemaManager = $connection->createSchemaManager();

        if (
            $schemaManager->tablesExist([
                'persons',
                'addresses',
                'bank_accounts'
            ])
        ) {
            return;
        }

        $process1 = new Process([
            'php',
            $this->getParameter('console_path'),
            'make:migration'
        ]);

        $process1->run();

        if (!$process1->isSuccessful()) {
            throw new \RuntimeException(
                $process1->getErrorOutput()
            );
        }

        $process2 = new Process([
            'php',
            $this->getParameter('console_path'),
            'doctrine:migrations:migrate',
            '--no-interaction'
        ]);

        $process2->run();

        if (!$process2->isSuccessful()) {
            throw new \RuntimeException(
                $process2->getErrorOutput()
            );
        }
    }


    /*
     * Add Person + Address + BankAccount.
     */
    #[Route('/persons/add', name: 'person_add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        Connection $connection
    ): Response
    {
        $this->create_orm_tables($connection);

        $form = $this->createFormBuilder()

            ->add('username', TextType::class)

            ->add('name', TextType::class)

            ->add('email', TextType::class)

            ->add('enable', CheckboxType::class, [
                'required' => false
            ])

            ->add('adult', CheckboxType::class, [
                'required' => false
            ])

            ->add('birthdate', DateType::class, [
                'widget' => 'single_text'
            ])

            ->add('mobile', TextType::class)

            ->add('address', TextType::class)

            ->add('bank_number', IntegerType::class)

            ->add('save', SubmitType::class, [
                'label' => 'Create person'
            ])

            ->getForm();


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();


            /*
             * Address
             */
            $address = new Address();

            $address->setAddress(
                $data['address']
            );


            /*
             * Person
             */
            $person = new Person();

            $person->setUsername(
                $data['username']
            );

            $person->setName(
                $data['name']
            );

            $person->setEmail(
                $data['email']
            );

            $person->setEnable(
                $data['enable']
            );

            $person->setAdult(
                $data['adult']
            );

            $person->setBirthdate(
                $data['birthdate']
            );

            $person->setMobile(
                $data['mobile']
            );

            $person->setAddress(
                $address
            );


            /*
             * BankAccount
             */
            $bankAccount = new BankAccount();

            $bankAccount->setNumber(
                $data['bank_number']
            );

            /*
             * Establish the bidirectional
             * OneToOne relationship.
             */
            $person->setBankAccount(
                $bankAccount
            );


            /*
             * Persist everything through ORM.
             */
            $entityManager->persist($address);
            $entityManager->persist($person);
            $entityManager->persist($bankAccount);

            $entityManager->flush();


            return $this->redirectToRoute(
                'persons'
            );
        }


        return $this->render(
            'person/add.html.twig',
            [
                'form' => $form
            ]
        );
    }


    /*
     * Display persons using:
     *
     * JOIN
     * CONDITION
     * SORT
     *
     * through ORM only.
     */
    #[Route('/persons', name: 'persons')]
    public function persons(
        Request $request,
        PersonRepository $personRepository,
        Connection $connection
    ): Response
    {
        $this->create_orm_tables($connection);


        /*
         * Request parameters.
         */
        $adult = $request->query->get('adult');

        $sort = $request->query->get(
            'sort',
            'name'
        );

        $order = strtoupper(
            $request->query->get(
                'order',
                'ASC'
            )
        );


        /*
         * Validate adult.
         */
        if ($adult === '1') {

            $adult = true;

        } elseif ($adult === '0') {

            $adult = false;

        } else {

            $adult = null;
        }


        /*
         * Validate sorting column.
         */
        $allowedSorts = [
            'name',
            'username',
            'birthdate'
        ];

        if (
            !in_array(
                $sort,
                $allowedSorts,
                true
            )
        ) {
            $sort = 'name';
        }


        /*
         * Validate sorting direction.
         */
        $allowedOrders = [
            'ASC',
            'DESC'
        ];

        if (
            !in_array(
                $order,
                $allowedOrders,
                true
            )
        ) {
            $order = 'ASC';
        }


        /*
         * Special repository query.
         */
        $persons = $personRepository->findPersons(
            $adult,
            $sort,
            $order
        );


        return $this->render(
            'person/index.html.twig',
            [
                'persons' => $persons,
                'adult' => $adult,
                'sort' => $sort,
                'order' => $order
            ]
        );
    }
}