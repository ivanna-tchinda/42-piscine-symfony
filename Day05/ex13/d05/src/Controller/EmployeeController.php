<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

final class EmployeeController extends AbstractController
{
    /*
     * Create ORM table if it does not exist.
     */
    private function create_orm_table(
        Connection $connection
    ): void {
        $schemaManager = $connection->createSchemaManager();

        if ($schemaManager->tablesExist('employees')) {
            return;
        }

        $makeMigration = new Process([
            'php',
            $this->getParameter('console_path'),
            'make:migration'
        ]);

        $makeMigration->run();

        if (!$makeMigration->isSuccessful()) {
            throw new \RuntimeException(
                $makeMigration->getErrorOutput()
            );
        }

        $migrate = new Process([
            'php',
            $this->getParameter('console_path'),
            'doctrine:migrations:migrate',
            '--no-interaction'
        ]);

        $migrate->run();

        if (!$migrate->isSuccessful()) {
            throw new \RuntimeException(
                $migrate->getErrorOutput()
            );
        }
    }


    /*
     * READ
     */
    #[Route('/employees', name: 'employee_index')]
    public function index(
        EmployeeRepository $repository,
        Connection $connection
    ): Response {
        $this->create_orm_table($connection);

        $employees = $repository->findAll();

        return $this->render(
            'employee/index.html.twig',
            [
                'employees' => $employees
            ]
        );
    }


    /*
     * CREATE
     */
    #[Route('/employees/create', name: 'employee_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        Connection $connection
    ): Response {
        $this->create_orm_table($connection);

        $employee = new Employee();

        $form = $this->createForm(
            EmployeeType::class,
            $employee
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            try {
                $entityManager->persist($employee);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Employee successfully created.'
                );

                return $this->redirectToRoute(
                    'employee_index'
                );

            } catch (\Throwable $e) {

                $this->addFlash(
                    'error',
                    'Employee could not be created.'
                );
            }
        }

        return $this->render(
            'employee/form.html.twig',
            [
                'form' => $form,
                'title' => 'Create employee'
            ]
        );
    }


    /*
     * EDIT
     */
    #[Route(
        '/employees/{id}/edit',
        name: 'employee_edit',
        requirements: ['id' => '\d+']
    )]
    public function edit(
        int $id,
        Request $request,
        EmployeeRepository $repository,
        EntityManagerInterface $entityManager,
        Connection $connection
    ): Response {
        $this->create_orm_table($connection);

        $employee = $repository->find($id);

        if (!$employee) {

            $this->addFlash(
                'error',
                'Employee not found.'
            );

            return $this->redirectToRoute(
                'employee_index'
            );
        }

        $form = $this->createForm(
            EmployeeType::class,
            $employee
        );

        $form->handleRequest($request);

        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            try {
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Employee successfully updated.'
                );

                return $this->redirectToRoute(
                    'employee_index'
                );

            } catch (\Throwable $e) {

                $this->addFlash(
                    'error',
                    'Employee could not be updated.'
                );
            }
        }

        return $this->render(
            'employee/form.html.twig',
            [
                'form' => $form,
                'title' => 'Edit employee'
            ]
        );
    }


    /*
     * DELETE
     */
    #[Route(
        '/employees/{id}/delete',
        name: 'employee_delete',
        requirements: ['id' => '\d+']
    )]
    public function delete(
        int $id,
        EmployeeRepository $repository,
        EntityManagerInterface $entityManager,
        Connection $connection
    ): Response {
        $this->create_orm_table($connection);

        $employee = $repository->find($id);

        if (!$employee) {

            $this->addFlash(
                'error',
                'Employee not found.'
            );

            return $this->redirectToRoute(
                'employee_index'
            );
        }

        try {
            /*
             * Employees managed by this employee
             * would otherwise reference the deleted row.
             */
            foreach ($employee->getEmployees() as $managedEmployee) {
                $managedEmployee->setManager(null);
            }

            $entityManager->remove($employee);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Employee successfully deleted.'
            );

        } catch (\Throwable $e) {

            $this->addFlash(
                'error',
                'Employee could not be deleted.'
            );
        }

        return $this->redirectToRoute(
            'employee_index'
        );
    }
}