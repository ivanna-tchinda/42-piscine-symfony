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

	#[Route('/ex02/show_users', name: 'show_users')]
	public function show_users(Connection $connection): Response
	{
		$sql = "SELECT * FROM users";
		
		$schemaManager = $connection->createSchemaManager();
		$result = null;
		if($schemaManager->tableExists('users')){
			$stmt = $connection->executeQuery($sql);
			$result =  $stmt->fetchAllAssociative();
		}

		return $this->render('form/users.html.twig', [
			'all_users' => $result
		]);
	}

	public function check_user(User $user, Connection $connection): bool
	{
		$userExists = false;
		$schemaManager = $connection->createSchemaManager();
		$sql = "SELECT * FROM users WHERE username=:username OR email=:email;";
		if($schemaManager->tableExists('users')){
			$stmt = $connection->executeQuery($sql, [
				'username' => $user->getUsername(),
				'email' => $user->getEmail(),
			]);
			$result =  $stmt->fetchAllAssociative();
			$userExists = $result ? true : false;
		}


		return $userExists;
	}

	public function create_user(User $user, Connection $connection): string
	{
		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('users')){
			return "Table has not been created";
		}
		if($this->check_user($user, $connection)){
			return "User already exists";
		}
		$sql = "INSERT INTO users
			(username, name, email, enable, birthdate, address)
			VALUES
			(:username, :name, :email, :enable, :birthdate, :address)";
			$connection->executeQuery($sql, [
			$username = $user->getUsername(),
			$name = $user->getName(),
			$email = $user->getEmail(),
			$enable = $user->getEnable() == 1 ? '1' : '0',
			$birthdate = $user->getBirthdate()->format('Y-m-d'),
			$address = $user->getAddress(),
		]);
		return "User ".$username. " has been created!";;

	}

	#[Route('/ex02/create_form', name: 'form')]
	public function create_form(Request $request, Connection $connection): Response
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
			$message = $this->create_user($user, $connection);
		}
		return $this->render('form/form.html.twig',[
			'form' => $form,
			'message' => $message
		]);
	}

	#[Route('/ex02/create_table', name: 'table')]
	public function create_table(Connection $connection): Response
	{
		$schemaManager = $connection->createSchemaManager();
		$sql = "CREATE TABLE users (
			id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
			username VARCHAR(255) UNIQUE,
			name VARCHAR(255),
			email VARCHAR(255) UNIQUE,
			enable BOOLEAN,
			birthdate TIMESTAMP,
			address TEXT
		);";
		$message = "Table users already exists";
		if(!$schemaManager->tableExists('users')){
			$connection->executeQuery($sql);
			$message = "Table users created!";	
		}
		return $this->render('form/index.html.twig', [
			'message' => $message
		]);
	}

	#[Route('/ex02', name: 'index')]
	public function index(): Response
	{
		return $this->render('base.html.twig');

	}
}
