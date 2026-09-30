<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class TablesController extends AbstractController
{

	#[Route('/add_column_persons', name: 'add_column_persons')]
	public function add_column_persons(Connection $connection, Request $request): Response
	{
		$column_data = array();
		$message = '';
		$form = $this->createFormBuilder()
	       ->add('column_name', TextType::class)
	       ->add('data_type', ChoiceType::class, [
                'label' => 'Column type',
                'choices' => [
                    'VARCHAR' => 'VARCHAR(255)',
                    'INTEGER' => 'INTEGER',
                    'BOOLEAN' => 'BOOLEAN',
                    'DATE' => 'DATE',
                    'TIMESTAMP' => 'TIMESTAMP',
                    'TEXT' => 'TEXT',
                    'FLOAT' => 'FLOAT',
                ],
            ])
	       ->add('save', SubmitType::class, ['label' => 'Add column'])
	       ->getForm();

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$column_data = $form->getData();

			$column_name = $column_data['column_name'];
			$data_type = $column_data['data_type'];

			if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column_name)) {
				return new Response('Invalid column name');
			}

			$sql = "ALTER TABLE persons
                ADD COLUMN $column_name $data_type";
			$connection->executeStatement($sql);

        	$message = "Column $column_name has been added";
		}
		return $this->render('form/add_column.html.twig', [
			'form' => $form,
			'message' => $message,
		]);


	}

	#[Route('/show_persons', name: 'show_persons')]
	public function show_persons(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if($schemaManager->tableExists('persons')){
			$sql = "SELECT * FROM persons";
			$stmt = $connection->executeQuery($sql);
			$persons =  $stmt->fetchAllAssociative();
			$columns = $schemaManager->listTableColumns('persons');
			$columnNames = [];
			foreach ($columns as $column) {
    			$columnNames[] = $column->getName();
			}
		}
		else{
			return $this->render('home/index.html.twig', [
				'message' => "Cannont create table persons if table addresses is not created"
			]);
		}

		return $this->render('tables/persons/index.html.twig', [
			'columns' => $columnNames,
			'persons' => $persons,
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

	#[Route('/create_addresses', name: 'create_addresses')]
	public function create_table_addresses(Connection $connection): Response
	{
		try {
			$schemaManager = $connection->createSchemaManager();
			if(!$schemaManager->tableExists('addresses')){
				$sql = "CREATE TABLE addresses(
				id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
				address varchar(255) UNIQUE
				);";
				$connection->executeQuery($sql);
				return new Response("Successfully created table addresses!");

			}
			return new Response("Table address already exists");
		} catch (\Throwable $th) {
			throw $th;
		}
	}

	#[Route('/show_addresses', name: 'show_addresses')]
	public function show_addresses(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if($schemaManager->tableExists('addresses')){
			$sql = "SELECT * FROM addresses";
			$stmt = $connection->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
		}
		else
			return new Response("Table addresses does not exists");
		return $this->render('tables/addresses/index.html.twig', [
			'result' => $result,
			'addresses' => $result
		]);

	}

	#[Route('/create_bank', name: 'create_bank')]
	public function create_bank(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('persons')){
			return new Response("Cannot create table bank_accounts if table persons is not created, go to route /home");
		}

		else if($schemaManager->tableExists('bank_accounts')){
			return new Response("Table bank_accounts already exists");
		}
		else{
			$sql = "CREATE TABLE bank_accounts(
				id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
				person_id int UNIQUE,
				name varchar(255) UNIQUE,
				card_id int UNIQUE,
				FOREIGN KEY (person_id) REFERENCES persons(id)
			);";
			$connection->executeQuery($sql);
			return new Response("Successfully created table bank_accounts!");
		}
		return new Response("Failed creating table bank_accounts");
	}

	#[Route('/show_bank', name: 'show_bank')]
	public function show_bank(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if($schemaManager->tableExists('bank_accounts')){
			$sql = "SELECT * FROM bank_accounts";
			$stmt = $connection->executeQuery($sql);
			$accounts =  $stmt->fetchAllAssociative();
			$columns = $schemaManager->listTableColumns('bank_accounts');
			$columnNames = [];
			foreach ($columns as $column) {
    			$columnNames[] = $column->getName();
			}
		}
		else{
			return $this->render('home/index.html.twig', [
				'message' => "Cannont create table bank_accounts if table persons is not created"
			]);
		}

		return $this->render('tables/bank_accounts/index.html.twig', [
			'columns' => $columnNames,
			'bank_accounts' => $accounts,
		]);

	}

	#[Route('/create_persons', name: 'create_persons')]
	public function create_persons(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('addresses')){
			return $this->render('home/index.html.twig', [
				'message' => "Cannont create table persons if table addresses is not created"
			]);
		}
		if(!$schemaManager->tableExists('persons')){
			$sql = "CREATE TABLE persons(
				id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
				address_id int,
				username varchar(255) UNIQUE,
				name varchar(255),
				email varchar(255) UNIQUE,
				enable BOOL,
				birthdate TIMESTAMP,
				FOREIGN KEY (address_id) REFERENCES addresses(id)
			);";
			$connection->executeQuery($sql);
			$message = "Table persons created!";
		}
		else{
			return $this->render('home/index.html.twig', [
				'message' => "Table persons already exists"
			]);
		}
		return $this->render('home/index.html.twig', [
			'message' => $message
		]);
	}

	#[Route('/ex08', name: 'homepage')]
	public function index(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		$addresses = $schemaManager->tableExists('addresses');
		$persons = $schemaManager->tableExists('persons');
		$bank_accounts = $schemaManager->tableExists('bank_accounts');
		return $this->render('base.html.twig', [
			'addresses' => $addresses,
			'persons' => $persons,
			'bank_accounts' => $bank_accounts,
		]);
	}
}
