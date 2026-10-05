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
	public function check_user_id(int $id, Connection $connection): bool
	{
		$userExists = false;
		$schemaManager = $connection->createSchemaManager();
		$sql = "SELECT * FROM users WHERE id=:id;";
		if($schemaManager->tableExists('users')){
			$stmt = $connection->executeQuery($sql, [
				'id' => $id,
			]);
			$result =  $stmt->fetchAllAssociative();
			$userExists = $result ? true : false;
		}


		return $userExists;

	}

	#[Route('/ex06/update/{id}', name: 'update_user')]
	public function update_user(Request $request, Connection $connection, int $id): Response
	{
		$sql = "SELECT * FROM users WHERE id=:id;";

		$schemaManager = $connection->createSchemaManager();
		$user = null;
		if($schemaManager->tableExists('users')){
			$stmt = $connection->executeQuery($sql, [
				'id' => $id,
			]);
			$user =  $stmt->fetchAllAssociative();
			$message = '';
			$form = $this->createFormBuilder()
			->add('username', TextType::class, [ 'data' => $user[0]['username']])
			->add('name', TextType::class, [ 'data' => $user[0]['name']])
			->add('email', TextType::class, [ 'data' => $user[0]['email']])
			->add('enable', ChoiceType::class, [
				'choices'  => [
					'Yes' => true,
					'No' => false,
				],
				'data' => $user[0]['enable']])
				->add('birthdate', DateType::class, [ 'data' => new \DateTime($user[0]['birthdate'])])
				->add('address', TextType::class, [ 'data' => $user[0]['address']])
				->add('save', SubmitType::class, ['label' => 'Update user'])
				->getForm();
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$user = $form->getData();
				$message = $this->edit_user($id, $user, $connection);
			}
		}
		return $this->render('form/update.html.twig',[
			'form' => $form,
			'message' => $message
		]);
	}

	#[Route('/ex06/delete/{id}', name: 'delete_id')]
	public function delete_user(int $id, Connection $connection): Response
	{
		$sql = "DELETE FROM users WHERE id=:id;";
		$schemaManager = $connection->createSchemaManager();
		$message = '';
		if($this->check_user_id($id, $connection))
		{
			$message = $connection->executeQuery($sql, [
				'id' => $id,
			]);
			return new Response("User with id $id has been deleted");
		}
		return new Response("user with id " . $id . " is not in the table");

	}

	#[Route('/ex06/show_users', name: 'show_users')]
	public function show_users(Connection $connection): Response
	{
		$sql = "SELECT * FROM users;";

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

	public function check_user(array $user, Connection $connection): bool
	{
		$userExists = false;
		$schemaManager = $connection->createSchemaManager();
		$sql = "SELECT * FROM users WHERE username=:username OR email=:email;";
		if($schemaManager->tableExists('users')){
			$stmt = $connection->executeQuery($sql, [
				'username' => $user['username'],
				'email' => $user['email'],
			]);
			$result =  $stmt->fetchAllAssociative();
			$userExists = $result ? true : false;
		}


		return $userExists;
	}

	public function edit_user(int $id, array $user, Connection $connection): string
	{
		$username = $user['username'];
		$name = $user['name'];
		$email = $user['email'];
		$enable = $user['enable'] == 1 ? '1' : '0';
		$birthdate = $user['birthdate']->format('Y-m-d');
		$address = $user['address'];

		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('users')){
			return "Table has not been created";
		}
		if(!$this->check_user($user, $connection)){
			return "User doesn't exist";
		}
		$sql = "UPDATE users SET username=:username, name=:name, email=:email, enable=:enable, birthdate=:birthdate, address=:address
		WHERE id=:id;";
		$connection->executeQuery($sql, [
			'username' => $username,
			'name' => $name,
			'email' => $email,
			'enable' => $enable,
			'birthdate' => $birthdate,
			'address' => $address,
			'id' => $id
		]);
		return "User ".$username. " has been updated!";

	}

	public function create_user(array $user, Connection $connection): string
	{
		$username = $user['username'];
		$name = $user['name'];
		$email = $user['email'];
		$enable = $user['enable'] == 1 ? '1' : '0';
		$birthdate = $user['birthdate']->format('Y-m-d');
		$address = $user['address'];

		$schemaManager = $connection->createSchemaManager();
		if(!$schemaManager->tableExists('users')){
			return "Table has not been created";
		}
		if($this->check_user($user, $connection)){
			return "User already exists";
		}
		$sql = "INSERT INTO users(username, name, email, enable, birthdate, address) VALUES (:username,	
			:name,
			:email,
			:enable,
			:birthdate,
			:address);";
		$connection->executeQuery($sql, [
			'username' => $username,
			'name' => $name,
			'email' => $email,
			'enable' => $enable,
			'birthdate' => $birthdate,
			'address' => $address
		]);
		return "User ".$username. " has been created!";

	}

	#[Route('/ex06/create_form', name: 'form')]
	public function create_form(Request $request, Connection $connection): Response
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
			$message = $this->create_user($user, $connection);
		}
		return $this->render('form/form.html.twig',[
			'form' => $form,
			'message' => $message
		]);
	}

	#[Route('/ex06/create_table', name: 'table')]
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

	#[Route('/ex06', name: 'index')]
	public function index(): Response
	{
		return $this->render('base.html.twig');

	}
}
