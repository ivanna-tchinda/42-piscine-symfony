<?php
namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\DriverManager;


class UserController extends AbstractController
{
	#[Route('/ex00/create_table', name: 'table')]
	public function create_table(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		$sql = "CREATE TABLE users(
			id int PRIMARY KEY,
			username TEXT UNIQUE,
			name TEXT,
			email TEXT UNIQUE,
			enable BOOL,
			birthdate TIMESTAMP,
			address varchar(255)
);";
		if(!$schemaManager->tableExists('users')){
			$connection->executeQuery($sql);
			return new Response("Table users created!");	
		}
		return new Response("Table users already exists");
	}

	#[Route('/ex00', name: 'index')]
	public function index(Connection $connection): Response
	{
		$message = 'Create table';
		return $this->render('base.html.twig', [
			'message' => $message
		]);

	}
}
