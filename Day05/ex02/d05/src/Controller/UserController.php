<?php
namespace App\Controller;

use App\Entity\User;
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
	public function db_connection(): Connection
	{
		$connectionParams = [
			'dbname' => 'ex02',
			'user' => 'root',
			'password' => '1234',
			'host' => 'localhost',
			'driver' => 'pdo_mysql',
		];
		$conn = DriverManager::getConnection($connectionParams);

		return $conn;
	}

	#[Route('/e02/show_users', name: 'show_users')]
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

	public function check_user(User $user): bool
	{
		$userExists = false;
		$conn = $this->db_connection();
		$schemaManager = $conn->createSchemaManager();
		$sql = "SELECT * FROM users WHERE username='".$user->getUsername()."' OR email='".$user->getEmail()."';";
		if($schemaManager->tableExists('users')){
			$stmt = $conn->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
			$userExists = $result ? true : false;
		}


		return $userExists;
	}

	public function create_user(User $user): string
	{
		$username = $user->getUsername();
		$name = $user->getName();
		$email = $user->getEmail();
		$enable = $user->getEnable() == 1 ? '1' : '0';
		$birthdate = $user->getBirthdate()->format('Y-m-d');
		$address = $user->getAddress();

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

	#[Route('/e02/create_form', name: 'form')]
	public function create_form(Request $request): Response
	{
		$user = new User();
		$message = '';
		$form = $this->createFormBuilder($user)
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

	#[Route('/e02/create_table', name: 'table')]
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

	#[Route('/e02', name: 'index')]
	public function index(Connection $connection): Response
	{
		return $this->render('base.html.twig');

	}
}
