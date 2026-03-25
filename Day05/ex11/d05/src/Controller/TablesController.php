<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

final class TablesController extends AbstractController
{
	public function db_connection(): Connection
	{
		$dsnParser = new DsnParser();
		$connectionParams = $dsnParser
			->parse($this->getParameter('databaseUrl'));
		$conn = DriverManager::getConnection($connectionParams);
		return $conn;
	}

	#[Route('/add_column', name: 'add_column')]
	public function add_column(Request $request): Response
	{
		$column_data = array();
		$form = $this->createFormBuilder()
	       ->add('column_name', TextType::class)
	       ->add('data_type', TextType::class)
	       ->add('save', SubmitType::class, ['label' => 'Add column'])
	       ->getForm();

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$column_data = $form->getData();
			return $this->redirectToRoute('add_column_data', [
				'column_name' => $column_data['column_name'],
				'data_type' => $column_data['data_type']
			]);
		}	
		return $this->render('tables/persons/new-column-form.html.twig', [
			'form' => $form
		]);
	}

	#[Route('/add_column/{column_name}/{data_type}', name: 'add_column_data')]
	public function add_column_data(string $column_name, string $data_type): Response
	{
		$sql = "ALTER TABLE persons ADD $column_name $data_type";

		$conn = $this->db_connection();
		$message = "Column successfully added in table persons";
		$schemaManager = $conn->createSchemaManager();
		$result = null;
		try {
			$stmt = $conn->executeQuery($sql);
		} catch (\Doctrine\DBAL\Exception\DriverException $e)
		{
			return new Response("Failed creating a column");
		}
		return $this->render('tables/message.html.twig', [
			'message' => $message
		]);


	}

	public function get_columns(string $table_name): array
	{
		$sql = " SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'$table_name'
";

		$conn = $this->db_connection();

		$schemaManager = $conn->createSchemaManager();
		$result = null;
		if($schemaManager->tableExists('persons')){
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
		}

		return $result;

	}

	#[Route('/show_table/persons', name: 'table_persons')]
	public function table_persons(): Response
	{
		$sql = "SELECT * FROM persons";

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
		       ->add('birthdate', DateType::class)
		       ->add('address', TextType::class)
		       ->add('save', SubmitType::class, ['label' => 'Create User'])
		       ->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$user = $form->getData();
			$message = $this->create_user($user);
		}

		$conn = $this->db_connection();
		$columns_name = $this->get_columns("persons");
		$schemaManager = $conn->createSchemaManager();
		$stmt = $conn->executeQuery($sql);
		$result =  $stmt->fetchAllAssociative();

		return $this->render('tables/persons/index.html.twig', [
			'columns_name' => $columns_name,
			'all_persons' => $result,
		]);
	}

	public function check_user(array $user): bool
	{
		$userExists = false;
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$sql = "SELECT * FROM users WHERE username='".$user['username']."' OR email='".$user['email']."';";
		if($schemaManager->tableExists('users')){
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
			$userExists = $result ? true : false;
		}


		return $userExists;	
	}

	public function create_table_addresses(): string
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$sql = "CREATE TABLE addresses(
			address_id int AUTO_INCREMENT PRIMARY KEY,
			address varchar(255) UNIQUE
);";
		if(!$schemaManager->tableExists('bank_accounts')){
			$conn->executeQuery($sql);
			return "Successfully created table bank_accounts!";
		}
		return "Failed creating table bank_accounts";

	}

	#[Route('/show_table/addresses')]
	public function table_addresses(): Response
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if($schemaManager->tableExists('addresses')){
			$sql = "SELECT * FROM addresses";
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
			$columns_name = $this->get_columns("addresses");
		}
		else
			return new Response($this->create_table_addresses());
		return $this->render('show_all/index.html.twig', [
			'columns_name' => $columns_name,
			'result' => $result,
			'table_name' => 'Addresses'
		]);

	}

	public function create_table_bank_accounts(): string
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if(!$schemaManager->tableExists('persons'))
			return "Cannot create table bank_accounts if table persons is not created, go to route /home";
		$sql = "CREATE TABLE bank_accounts(
			bank_account_id int AUTO_INCREMENT PRIMARY KEY,
			person_id int UNIQUE,
			name varchar(255) UNIQUE,
			card_id int UNIQUE,
			FOREIGN KEY (person_id) REFERENCES persons(person_id)
);";
		if(!$schemaManager->tableExists('bank_accounts')){
			$conn->executeQuery($sql);
			return "Successfully created table bank_accounts!";
		}
		return "Failed creating table bank_accounts";

	}

	#[Route('/show_table/bank_accounts')]
	public function bank_accounts(): Response
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if($schemaManager->tableExists('bank_accounts')){
			$sql = "SELECT * FROM bank_accounts";
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
			$columns_name = $this->get_columns("bank_accounts");
		}
		else
			return new Response($this->create_table_bank_accounts());
		return $this->render('show_all/index.html.twig', [
			'columns_name' => $columns_name,
			'result' => $result,
			'table_name' => 'Bank Accounts'
		]);

	}


	#[Route('/home', name: 'homepage')]
	public function homepage(): Response
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if(!$schemaManager->tableExists('addresses'))
			return new Response("Cannont create table persons if table addresses is not created, go to route /show_table/addresses");
		$sql = "CREATE TABLE persons(
			person_id int AUTO_INCREMENT PRIMARY KEY,
			address_id int UNIQUE,
			username varchar(255) UNIQUE,
			name varchar(255),
			email varchar(255) UNIQUE,
			enable BOOL,
			birthdate DATETIME,
			FOREIGN KEY (address_id) REFERENCES addresses(address_id)
);";
		$message = "Table persons already exists";
		if(!$schemaManager->tableExists('persons')){
			$conn->executeQuery($sql);
			$message = "Table persons created!";
		}

		return $this->render('home/index.html.twig', [
			'message' => $message
		]);
	}

	#[Route('/tables', name: 'app_tables')]
	public function index(): Response
	{
		return $this->render('tables/index.html.twig', [
			'controller_name' => 'TablesController',
		]);
	}
}
