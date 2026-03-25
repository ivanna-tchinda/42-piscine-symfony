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
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

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
		$sql = " SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'$table_name'
";

		$conn = $this->db_connection();

		$schemaManager = $conn->createSchemaManager();
		$result = array();
		if($schemaManager->tableExists($table_name)){
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
		}

		return $result;

	}

	public function insert_sql_table(array $users): void
	{
		$conn = $this->db_connection();

		$sql = "INSERT INTO users_sql (username, name) VALUES";
		foreach($users as $user)
		{
			$username = $user->getUsername();
			$name = $user->getName();
			$sql .= "('$username', '$name'),";
		}
		$sql = trim($sql, ",");
		$sql .= ";";
		if($conn->createSchemaManager()->tableExists('users_sql'))
			$conn->executeQuery($sql);

	}

	public function insert_orm_table(array $users, EntityManagerInterface $entityManager): void
	{
		foreach($users as $user)
		{
			$entityManager->persist($user);
			$entityManager->flush();
		}
	}

	#[Route('/read_and_insert', name: 'read_and_insert')]
	public function read_and_insert(EntityManagerInterface $entityManager): Response
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
		$this->insert_sql_table($users);
		$this->insert_orm_table($users, $entityManager);

		return new Response("Users have been inserted in tables with ORM and SQL");
	}

	#[Route('/home', name: 'home')]
	public function home(): Response
	{
		return $this->render('/pages/index.html.twig');
	}

	#[Route('/show_users_orm', name: 'table_users_orm')]
	public function show_users_orm(EntityManagerInterface $entityManager): Response
	{

		$users = $entityManager->getRepository(User::class)->findAll();
		if (!$users) {
			return new Response("No users registered in ORM table");
		}

		return $this->render('tables/orm/users.html.twig',[
			'users' => $users
		]);
	}

	#[Route('/show_users_sql', name: 'table_users_sql')]
	public function table_users_sql(Request $request): Response
	{
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

		$conn = $this->db_connection();
		$columns_name = $this->get_columns("users_sql");
		$schemaManager = $conn->createSchemaManager();
		$stmt = $conn->executeQuery($sql);
		$result =  $stmt->fetchAllAssociative();

		return $this->render('tables/sql/users.html.twig', [
			'columns_name' => $columns_name,
			'all_users' => $result,
		]);
	}

	#[Route('/create_sql_table', name: 'create_sql_table')]
	public function create_sql_table(): Response
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if($schemaManager->tableExists('users'))
			return new Response("Table users_sql already exists, go to route /show_users_sql");
		$sql = "CREATE TABLE users_sql(
			id int AUTO_INCREMENT PRIMARY KEY,
			username varchar(255),
			name varchar(255));";
		$conn->executeQuery($sql);
		return new Response("Table users_sql created!");

	}
}
