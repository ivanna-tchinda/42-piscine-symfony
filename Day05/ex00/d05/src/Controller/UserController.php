<?php
namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\DriverManager;


class UserController extends AbstractController
{
	#[Route('/e00/create_table', name: 'table')]
	public function create_table(): Response
	{
		$connectionParams = [
			'dbname' => 'ex00',
			'user' => 'root',
			'password' => '1234',
			'host' => 'localhost',
			'driver' => 'pdo_mysql',
		];
		$conn = DriverManager::getConnection($connectionParams);
		$schemaManager = $conn->createSchemaManager();
		$sql = "CREATE TABLE users(
			id int PRIMARY KEY,
			username varchar(255) UNIQUE,
			name varchar(255),
			email varchar(255) UNIQUE,
			enable BOOL,
			birthdate DATETIME UNIQUE,
			address LONGTEXT
);";
		if(!$schemaManager->tableExists('users')){
			$conn->executeQuery($sql);
			return new Response("Table users created!");	
		}
		return new Response("Table users already exists");
	}

	#[Route('/e00', name: 'index')]
	public function index(Connection $connection): Response
	{
		$message = '';
		return $this->render('base.html.twig', [
			'message' => $message
		]);

	}
}
