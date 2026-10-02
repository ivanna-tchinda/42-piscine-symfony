<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

final class TablesController extends AbstractController
{
	#[Route('/filter_sort', name: 'filter_sort')]
	public function filter_sort(Connection $connection, Request $request)
	{
		$sql = "SELECT * FROM persons";
		$schemaManager = $connection->createSchemaManager();
		$stmt = $connection->executeQuery($sql);
		$persons =  $stmt->fetchAllAssociative();
		//FILTER FORM
		$form_filter = $this->createFormBuilder()
	       ->add('type', ChoiceType::class, [
				'choices' => [
					'Filter' => "Filter",
					'Sort' => "Sort",
				]])
		   ->getForm();
		$form_filter->handleRequest($request);
		if ($form_filter->isSubmitted() && $form_filter->isValid()) {
			$person = $form_filter->getData();
			$message = $this->create_user($connection, $person);
		}

		//SORT FORM
		$form_sort = $this->createFormBuilder()
	       ->add('type', ChoiceType::class, [
				'choices' => [
					'ASC' => "ASC",
					'DESC' => "DESC",
				]])
		   ->getForm();
		$form_sort->handleRequest($request);
		if ($form_sort->isSubmitted() && $form_sort->isValid()) {
			$person = $form_sort->getData();
			$message = $this->create_user($connection, $person);
		}

		$columns = $schemaManager->listTableColumns('persons');
		$columnNames = [];
		foreach ($columns as $column) {
    		$columnNames[] = $column->getName();
		}
		return $this->render('tables/sorted.html.twig', [
			'columns_name' => $columnNames,
			'all_persons' => $persons,
			'form_sort' => $form_sort,
			'form_filter' => $form_filter
		]);
	}

	#[Route('/show_table/persons', name: 'table_persons')]
	public function table_persons(Connection $connection, Request $request): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if($schemaManager->tableExists('addresses')){
			$sql = "SELECT * FROM addresses";
			$stmt = $connection->executeQuery($sql);
			$result_addresses =  $stmt->fetchAllKeyValue();
		}

		$person = array();
		$form = $this->createFormBuilder()
	       ->add('username', TextType::class)
	       ->add('name', TextType::class)
	       ->add('email', TextType::class)
	       ->add('enable', ChoiceType::class, [
		       'choices'  => [
			       'Yes' => true,
			       'No' => false,
		       ],])
		       ->add('address', ChoiceType::class, ['choices' => $result_addresses])
		       ->add('birthdate', DateType::class)
		       ->add('save', SubmitType::class, ['label' => 'Create Person'])
		       ->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$person = $form->getData();
			$message = $this->create_user($connection, $person);
		}
		$columns = $schemaManager->listTableColumns('persons');
		$columnNames = [];
		foreach ($columns as $column) {
    		$columnNames[] = $column->getName();
		}
		$sql = "SELECT * FROM persons";
		$schemaManager = $connection->createSchemaManager();
		$stmt = $connection->executeQuery($sql);
		$result =  $stmt->fetchAllAssociative();
		return $this->render('tables/persons/index.html.twig', [
			'columns_name' => $columnNames,
			'all_persons' => $result,
			'form' => $form
		]);
	}

	public function check_user(Connection $connection, array $user): bool
	{
		$userExists = false;
		$schemaManager = $connection->createSchemaManager();
		$sql = "SELECT * FROM users WHERE username='".$user['username']."' OR email='".$user['email']."';";
		if($schemaManager->tableExists('users')){
			$stmt = $connection->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();

			$userExists = $result ? true : false;
		}


		return $userExists;	
	}

	public function check_address(Connection $connection, array $address): bool
	{
		$addressExists = false;
		$schemaManager = $connection->createSchemaManager();
		$sql = "SELECT * FROM addresses WHERE address='".$address['address']."';";
		if($schemaManager->tableExists('addresses')){
			$stmt = $connection->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();

			$addressExists = $result ? true : false;
		}


		return $addressExists;
	}


	public function create_user(Connection $connection, array $user): string
	{
		$username = $user['username'];
		$name = $user['name'];
		$email = $user['email'];
		$enable = $user['enable'] == 1 ? '1' : '0';
		$birthdate = $user['birthdate']->format('Y-m-d');

		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('persons')){
			return "Table has not been created";
		}
		if($this->check_user($connection, $user)){
			return "Person already exists";
		}
		$sql = "INSERT INTO persons(username, name, email, enable, birthdate) VALUES ('".$username."','".
			$name."','".
			$email."','".
			$enable."','".
			$birthdate.
			"');";
		$connection->executeQuery($sql);
		return "Person ".$username. " has been created!";;

	}

	public function create_address(Connection $connection, array $address): string
	{
		$address_name = $address['address'];

		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('addresses')){
			return "Table has not been created";
		}
		if($this->check_address($connection, $address)){
			return "Address already exists";
		}
		$sql = "INSERT INTO addresses(address) VALUES ('".$address_name."');";
		$connection->executeQuery($sql);
		return "Address ".$address_name. " has been created!";;

	}


	public function create_table_addresses(Connection $connection): string
	{
		$schemaManager = $connection->createSchemaManager();
		$sql = "CREATE TABLE addresses(
			id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
			address varchar(255) UNIQUE
		);";
		if(!$schemaManager->tableExists('addresses')){
			$connection->executeQuery($sql);
			return "Successfully created table addresses!";
		}
		return "Failed creating table addresses";

	}

	#[Route('/show_table/addresses', name: 'table_addresses')]
	public function table_addresses(Connection $connection, Request $request): Response
	{
		$schemaManager = $connection->createSchemaManager();
		$message = '';
		if($schemaManager->tableExists('addresses')){
			$sql = "SELECT * FROM addresses";
			$stmt = $connection->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
			$columns = $schemaManager->listTableColumns('persons');
			$columnNames = [];
			foreach ($columns as $column) {
    			$columnNames[] = $column->getName();
			}
			$form = $this->createFormBuilder()
		->add('address', TextType::class)
		->add('save', SubmitType::class, ['label' => 'Create Address'])
		->getForm();
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$address = $form->getData();
				$message = $this->create_address($connection, $address);
			}
		}
		else
			return new Response($this->create_table_addresses($connection));
		return $this->render('show_all/index.html.twig', [
			'columns_name' => $columnNames,
			'result' => $result,
			'table_name' => 'Addresses',
			'form' => $form,
			'message' => $message
		]);

	}

	public function create_table_bank_accounts(Connection $connection): string
	{
		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('persons'))
			return "Cannot create table bank_accounts if table persons is not created, go to route /home";
		$sql = "CREATE TABLE bank_accounts(
			id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
			person_id int UNIQUE,
			name varchar(255) UNIQUE,
			card_id int UNIQUE,
			FOREIGN KEY (person_id) REFERENCES persons(id)
		);";
		if(!$schemaManager->tableExists('bank_accounts')){
			$connection->executeQuery($sql);
			return "Successfully created table bank_accounts!";
		}
		return "Failed creating table bank_accounts";

	}

	#[Route('/show_table/bank_accounts', name: 'table_bank_accounts')]
	public function bank_accounts(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if($schemaManager->tableExists('bank_accounts')){
			$sql = "SELECT * FROM bank_accounts";
			$stmt = $connection->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
			$columns = $schemaManager->listTableColumns('bank_accounts');
			$columnNames = [];
			foreach ($columns as $column) {
    			$columnNames[] = $column->getName();
			}
		}
		else
			return new Response($this->create_table_bank_accounts($connection));
		return $this->render('show_all/bank.html.twig', [
			'columns_name' => $columnNames,
			'result' => $result,
			'table_name' => 'Bank Accounts'
		]);

	}


	#[Route('/home', name: 'homepage')]
	public function homepage(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('addresses'))
			return new Response("Cannont create table persons if table addresses is not created, go to route /show_table/addresses");
		$sql = "CREATE TABLE persons(
			id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
			address_id int UNIQUE,
			username varchar(255) UNIQUE,
			name varchar(255),
			email varchar(255) UNIQUE,
			enable BOOL,
			birthdate TIMESTAMP,
			FOREIGN KEY (address_id) REFERENCES addresses(id));";
		$message = "Table persons already exists";
		if(!$schemaManager->tableExists('persons')){
			$connection->executeQuery($sql);
			$message = "Table persons created!";
		}

		return $this->render('home/index.html.twig', [
			'message' => $message
		]);
	}

	#[Route('/ex11', name: 'index')]
	public function index(): Response
	{
		return $this->render('tables/index.html.twig');
	}
}
