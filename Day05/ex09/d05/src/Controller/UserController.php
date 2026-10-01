<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Person;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use App\Entity\Address;
use App\Entity\BankAccount;
use App\Form\PersonType;
use App\Form\AddressType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Form\BankAccountType;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManagerInterface;

final class UserController extends AbstractController
{
	#[Route('/create_table', name: 'create_table')]
	public function create_table(Connection $connection): Response
	{
		try {
			$schemaManager = $connection->createSchemaManager();
			if($schemaManager->tablesExist('persons') || $schemaManager->tablesExist('addresses') | $schemaManager->tablesExist('bank_accounts')) {
				return new Response("Tables already exists");
			}
			$process1 = new Process(['php', $this->getParameter('console_path'), 'make:migration']);
			$process1->run();

			if (!$process1->isSuccessful()) {
				return new Response($process1->getErrorOutput(), 500);
			}

			$process2 = new Process(['php', $this->getParameter('console_path'), 'doctrine:migrations:migrate', '--no-interaction']);
			$process2->run();

			if (!$process2->isSuccessful()) {
				return new Response($process2->getErrorOutput(), 500);
			}

			return new Response(
				"All tables have been created"
			);

		} catch (\Throwable $th) {
			throw new Exception($th);

		}
	}

	#[Route('/show_bank_account', name: 'show_bankaccount')]
	public function show_bankaccount(EntityManagerInterface $entityManager): Response
	{

		$bank_accounts = $entityManager->getRepository(BankAccount::class)->findAll();
		if (!$bank_accounts) {
			return new Response("No bank_accounts registered in table");
		}

		return $this->render('table/bank_account.html.twig',[
			'bank_accounts' => $bank_accounts
		]);
	}

	#[Route('/show_address', name: 'show_address')]
	public function show_address(EntityManagerInterface $entityManager): Response
	{

		$addresses = $entityManager->getRepository(Address::class)->findAll();
		if (!$addresses) {
			return new Response("No addresses registered in table");
		}

		return $this->render('table/address.html.twig',[
			'addresses' => $addresses
		]);
	}

	#[Route('/edit/{id}', name: 'edit_person', requirements: ['id' => '\d+'])]
	public function edit_person(int $id, EntityManagerInterface $entityManager, Request $request): Response
	{
		$person = $entityManager->getRepository(Person::class)->find($id);
		$message = '';
		if(!$person)
		{
			return new Response("Person with id ".$id." does not exists in table person");
		}
		$form = $this->createForm(PersonType::class, $person);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$person = $form->getData();

			$entityManager->persist($person);
			$entityManager->flush();
			$message = "Person with id ".$id." has been updated";


		}

		return $this->render('form/index.html.twig', [
			'form' => $form,
			'message' => $message
		]);
	}

	#[Route('/delete/{id}', name: 'delete_person', requirements: ['id' => '\d+'])]
	public function delete_person(int $id, EntityManagerInterface $entityManager): Response
	{
		$person = $entityManager->getRepository(Person::class)->find($id);
		if($person){
			$entityManager->remove($person);
			$entityManager->flush();
			return new Response("Person with id ".$id." has been deleted");
		}
		return new Response("Person with id ".$id." is not in the table");
	}

	#[Route('/show_person', name: 'show_persons')]
	public function show_persons(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();

		if (!$schemaManager->tablesExist(['persons'])) {
			return new Response("Table persons doesn't exist");
		}

		$columns = $schemaManager->listTableColumns('persons');

		$columnNames = [];

		foreach ($columns as $column) {
			$columnNames[] = $column->getName();
		}

		$persons = $connection
			->executeQuery('SELECT * FROM persons')
			->fetchAllAssociative();

		return $this->render('table/person.html.twig', [
			'columns' => $columnNames,
			'persons' => $persons,
		]);
	}

	#[Route('/bank_account', name: 'app_bankaccount')]
	public function bank_account_form(Request $request, EntityManagerInterface $entityManager): Response
	{
		$bank_account = new BankAccount();

		$form = $this->createForm(BankAccountType::class, $bank_account);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$bank_account = $form->getData();
			$bank_account_exists = $entityManager->getRepository(BankAccount::class)->findByNumber($bank_account->getNumber());
			if(!$bank_account_exists){

				$entityManager->persist($bank_account);
				$entityManager->flush();
				return new Response("Bank account ". $bank_account->getNumber()." has been created");
			}
			return new Response("Bank account ".$bank_account->getNumber()." already exists");


		}

		return $this->render('form/bank_account.html.twig', [
			'form' => $form,
			'message' => ''
		]);
	}
	#[Route('/address', name: 'app_address')]
	public function address_form(Request $request, EntityManagerInterface $entityManager): Response
	{
		$address = new Address();

		$form = $this->createForm(AddressType::class, $address);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$address = $form->getData();
			$address_exists = $entityManager->getRepository(Address::class)->findByAddress($address->getAddress());
			if(!$address_exists){

				$entityManager->persist($address);
				$entityManager->flush();
				return new Response("Address ". $address->getAddress()." has been created");
			}
			return new Response("address ".$address->getAddress()." already exists");


		}

		return $this->render('form/address.html.twig', [
			'form' => $form,
			'message' => ''
		]);
	}

	#[Route('/person', name: 'app_person')]
	public function person(Request $request, EntityManagerInterface $entityManager): Response
	{
		$person = new Person();

		$form = $this->createForm(PersonType::class, $person);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$person = $form->getData();
			$person_exists = $entityManager->getRepository(Person::class)->findByUsername($person->getUsername())
				|| $entityManager->getRepository(Person::class)->findByUsername($person->getEmail());
			if(!$person_exists){

				$entityManager->persist($person);
				$entityManager->flush();
				return new Response("Person ". $person->getUsername()." has been created");
			}
			return new Response("Person ".$person->getUsername()." already exists");


		}

		return $this->render('form/index.html.twig', [
			'form' => $form,
			'message' => ''
		]);
	}

	#[Route('/ex09', name: 'index')]
	public function index(Request $request, EntityManagerInterface $entityManager): Response
	{
		return $this->render('base.html.twig');
	}
}
