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
	public function db_connection(): Connection
	{
		$dsnParser = new DsnParser();
		$connectionParams = $dsnParser
			->parse($this->getParameter('databaseUrl'));
		$conn = DriverManager::getConnection($connectionParams);
		return $conn;
	}


	public function get_columns(string $table_name): array
	{
		$sql = "DESCRIBE $table_name";

		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$result = array();
		if($schemaManager->tableExists($table_name)){
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
		}
		return $result;
	}

	#[Route('/show_table/persons', name: 'table_persons')]
	public function table_persons(Request $request): Response
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if($schemaManager->tableExists('addresses')){
			$sql = "SELECT * FROM addresses";
			$stmt = $conn->executeQuery($sql);
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
			$message = $this->create_user($person);
		}

		$columns_name = $this->get_columns("persons");
		$sql = "SELECT * FROM persons";
		$schemaManager = $conn->createSchemaManager();
		$stmt = $conn->executeQuery($sql);
		$result =  $stmt->fetchAllAssociative();
		var_dump($result_addresses);
		return $this->render('tables/persons/index.html.twig', [
			'columns_name' => $columns_name,
			'all_persons' => $result,
			'form' => $form
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

	public function check_address(array $address): bool
	{
		$addressExists = false;
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$sql = "SELECT * FROM addresses WHERE address='".$address['address']."';";
		if($schemaManager->tableExists('addresses')){
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();

			$addressExists = $result ? true : false;
		}


		return $addressExists;
	}


	public function create_user(array $user): string
	{
		$username = $user['username'];
		$name = $user['name'];
		$email = $user['email'];
		$enable = $user['enable'] == 1 ? '1' : '0';
		$birthdate = $user['birthdate']->format('Y-m-d');

		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if(!$schemaManager->tableExists('persons')){
			return "Table has not been created";
		}
		if($this->check_user($user)){
			return "Person already exists";
		}
		$sql = "INSERT INTO persons(username, name, email, enable, birthdate) VALUES ('".$username."','".
			$name."','".
			$email."','".
			$enable."','".
			$birthdate.
			"');";
		$conn->executeQuery($sql);
		return "Person ".$username. " has been created!";;

	}

	public function create_address(array $address): string
	{
		$address_name = $address['address'];

		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if(!$schemaManager->tableExists('addresses')){
			return "Table has not been created";
		}
		if($this->check_address($address)){
			return "Address already exists";
		}
		$sql = "INSERT INTO addresses(address) VALUES ('".$address_name."');";
		$conn->executeQuery($sql);
		return "Address ".$address_name. " has been created!";;

	}


	public function create_table_addresses(): string
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$sql = "CREATE TABLE addresses(
			address_id int AUTO_INCREMENT PRIMARY KEY,
			address varchar(255) UNIQUE
);";
		if(!$schemaManager->tableExists('addresses')){
			$conn->executeQuery($sql);
			return "Successfully created table addresses!";
		}
		return "Failed creating table addresses";

	}

	#[Route('/show_table/addresses')]
	public function table_addresses(Request $request): Response
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$message = '';
		if($schemaManager->tableExists('addresses')){
			$sql = "SELECT * FROM addresses";
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
			$columns_name = $this->get_columns("addresses");
			$form = $this->createFormBuilder()
		->add('address', TextType::class)
		->add('save', SubmitType::class, ['label' => 'Create Address'])
		->getForm();
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$address = $form->getData();
				$message = $this->create_address($address);
			}
		}
		else
			return new Response($this->create_table_addresses());
		return $this->render('show_all/index.html.twig', [
			'columns_name' => $columns_name,
			'result' => $result,
			'table_name' => 'Addresses',
			'form' => $form,
			'message' => $message
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
			FOREIGN KEY (address_id) REFERENCES addresses(address_id));";
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
