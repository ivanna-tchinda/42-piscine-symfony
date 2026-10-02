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
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;
use App\Entity\User;

final class TablesController extends AbstractController
{
	public function get_columns(Connection $connection, string $table_name): array
	{
		$sql = " SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'$table_name'
";

		$schemaManager = $connection->createSchemaManager();
		$result = array();
		if($schemaManager->tableExists($table_name)){
			$stmt = $connection->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
		}

		return $result;

	}

	public function insert_sql_table(Connection $connection, array $users): void
	{
		$this->create_sql_table($connection);

		$sql = "INSERT INTO users_sql (username, name) VALUES ";

		foreach ($users as $user) {
			$username = $user->getUsername();
			$name = $user->getName();

			$sql .= "('$username', '$name'),";
		}

		$sql = rtrim($sql, ',');

		$connection->executeStatement($sql);
	}

	public function create_orm_table(Connection $connection): Response
	{
		try {
			$schemaManager = $connection->createSchemaManager();
			if($schemaManager->tablesExist(['users_orm'])){
				return new Response("Table users_orm already exists");
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
				"Table users_orm has been created"
			);

		} catch (\Throwable $th) {
			throw new Exception($th);

		}
	}

	public function insert_orm_table(Connection $connection, array $users, EntityManagerInterface $entityManager): void
	{
		$this->create_orm_table($connection);
    
		foreach($users as $user)
		{
			$entityManager->persist($user);
			$entityManager->flush();
		}
	}

	#[Route('/read_and_insert', name: 'read_and_insert')]
	public function read_and_insert(Connection $connection, EntityManagerInterface $entityManager): Response
	{
		chmod('file.txt', 0755);
		$file = file_get_contents('file.txt');
		if(!$file)
			return new Response("Can't read file");
		$users = array();
		$lines = explode(PHP_EOL, $file);
		foreach($lines as $line)
		{
			$user_infos = explode(";", $line);
			if(count($user_infos) != 2)
				break;
			$user = new User();
			$user->setUsername($user_infos[0]);
			$user->setName($user_infos[1]);
			array_push($users, $user);
		}
		$this->insert_orm_table($connection, $users, $entityManager);
		$this->insert_sql_table($connection, $users);

		return new Response("Users have been inserted in tables with ORM and SQL");
	}

	#[Route('/ex10', name: 'index')]
	public function index(): Response
	{
		return $this->render('/pages/index.html.twig');
	}

	#[Route('/show_users_orm', name: 'table_users_orm')]
	public function show_users_orm(Connection $connection, EntityManagerInterface $entityManager): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tablesExist(['users_orm'])){
			return new Response("Table users_orm hasn't been created yet");
		}
		$users = $entityManager->getRepository(User::class)->findAll();
		if (!$users) {
			return new Response("No users registered in ORM table");
		}

		return $this->render('tables/orm/users.html.twig',[
			'users' => $users
		]);
	}

	#[Route('/show_users_sql', name: 'table_users_sql')]
	public function table_users_sql(Connection $connection, Request $request): Response
	{
		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tablesExist(['users_sql'])){
			return new Response("Table users_orm hasn't been created yet");
		}
		$sql = "SELECT * FROM users_sql";

		$user = array();
		$form = $this->createFormBuilder()
	       ->add('username', TextType::class)
	       ->add('name', TextType::class)
	       ->add('save', SubmitType::class, ['label' => 'Create User'])
	       ->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$user = $form->getData();
			$message = $this->create_user($user);
		}
		$columns_name = $this->get_columns($connection,"users_sql");
		$schemaManager = $connection->createSchemaManager();
		$stmt = $connection->executeQuery($sql);
		$result =  $stmt->fetchAllAssociative();

		return $this->render('tables/sql/users.html.twig', [
			'columns_name' => $columns_name,
			'all_users' => $result,
		]);
	}

	public function create_sql_table(Connection $connection): Response
	{
		if ($connection->createSchemaManager()->tableExists('users_sql')) {
			return new Response("Table users_sql already exists");
		}

		$sql = "CREATE TABLE users_sql (
			id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
			username VARCHAR(255),
			name VARCHAR(255)
		)";

		$connection->executeStatement($sql);

		return new Response("Table users_sql created!");
	}
}
