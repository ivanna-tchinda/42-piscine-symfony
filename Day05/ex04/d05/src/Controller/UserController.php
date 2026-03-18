<?php
namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

class UserController extends AbstractController
{
	public function check_user_id(int $id): bool
	{
		$userExists = false;
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$sql = "SELECT * FROM users WHERE id=$id";
		if($schemaManager->tableExists('users')){
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
			$userExists = $result ? true : false;
		}


		return $userExists;

	}
	#[Route('/e04/delete{id}', name: 'delete_id')]
	public function delete_user(int $id): Response
	{
		$sql = "DELETE FROM users WHERE id=" . $id;
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$message = '';
		if($this->check_user_id($id))
		{
			$message = $conn->executeQuery($sql);
			return new Response("User with id $id has been deleted");
		}
		return new Response("user with id " . $id . " is not in the table");

	}
	public function db_connection(): Connection
	{
		$connectionParams = [
			'dbname' => 'ex04',
			'user' => 'root',
			'password' => '1234',
			'host' => 'localhost',
			'driver' => 'pdo_mysql',
		];
		$conn = DriverManager::getConnection($connectionParams);

		return $conn;
	}

	#[Route('/e04/show_users', name: 'show_users')]
	public function show_users(): Response
	{
		$sql = "SELECT * FROM users";

		$conn = $this->db_connection();

		$schemaManager = $conn->createSchemaManager();
		$result = null;
		if($schemaManager->tableExists('users')){
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
		}

		return $this->render('form/users.html.twig', [
			'all_users' => $result
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

	public function create_user(array $user): string
	{
		$username = $user['username'];
		$name = $user['name'];
		$email = $user['email'];
		$enable = $user['enable'] == 1 ? '1' : '0';
		$birthdate = $user['birthdate']->format('Y-m-d');
		$address = $user['address'];

		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		if(!$schemaManager->tableExists('users')){
			return "Table has not been created";
		}
		if($this->check_user($user)){
			return "User already exists";
		}
		$sql = "INSERT INTO users(username, name, email, enable, birthdate, address) VALUES ('".$username."','".
			$name."','".
			$email."','".
			$enable."','".
			$birthdate."','".
			$address.
			"');";
		$conn->executeQuery($sql);
		return "User ".$username. " has been created!";;

	}

	#[Route('/e04/create_form', name: 'form')]
	public function create_form(Request $request): Response
	{
		$user = array();
		$message = '';
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
		return $this->render('form/form.html.twig',[
			'form' => $form,
			'message' => $message
		]);
	}

	#[Route('/e04/create_table', name: 'table')]
	public function create_table(): Response
	{
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$sql = "CREATE TABLE users(
			id int AUTO_INCREMENT PRIMARY KEY,
			username varchar(255) UNIQUE,
			name varchar(255),
			email varchar(255) UNIQUE,
			enable BOOL,
			birthdate DATETIME,
			address LONGTEXT
);";
		$message = "Table users already exists";
		if(!$schemaManager->tableExists('users')){
			$conn->executeQuery($sql);
			$message = "Table users created!";	
		}
		return $this->render('form/index.html.twig', [
			'message' => $message
		]);
	}

	#[Route('/e04', name: 'index')]
	public function index(Connection $connection): Response
	{
		return $this->render('base.html.twig');

	}
}
