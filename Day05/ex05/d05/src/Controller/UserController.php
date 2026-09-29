<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Process\Process;

final class UserController extends AbstractController
{
	#[Route('/delete/{id}', name: 'delete_user')]
	public function delete_user(EntityManagerInterface $entityManager, int $id): Response
	{
		try {
			$user = $entityManager->getRepository(User::class)->find($id);
			$entityManager->remove($user);
			$entityManager->flush();
		} catch (\Throwable $th) {
			//throw $th;
			throw new HttpException(500, $th);
		}

		return $this->render('delete_user.html.twig',[
			'user' => $user->getUsername()
		]);
	}

	#[Route('/show_users', name: 'show_users')]
	public function show_users(EntityManagerInterface $entityManager): Response
	{

		try {
			$users = $entityManager->getRepository(User::class)->findAll();
		} catch (\Throwable $th) {
			//throw $th;
			throw new HttpException(500, "users table doesn't exist");
		}

		return $this->render('users/index.html.twig',[
			'users' => $users
		]);
	}

	#[Route('/user', name: 'app_user')]
	public function user(Request $request, EntityManagerInterface $entityManager): Response
	{
		$user = new User();

		$form = $this->createForm(UserType::class, $user);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$user = $form->getData();
			$user_exists = $entityManager->getRepository(User::class)->findByUsername($user->getUsername())
			       	|| $entityManager->getRepository(User::class)->findByUsername($user->getEmail());
			if(!$user_exists){

				$entityManager->persist($user);
				$entityManager->flush();
				return new Response("User ". $user->getUsername()." has been created");
			}
			return new Response("User ".$user->getUsername()." already exists");


		}

		return $this->render('user/index.html.twig', [
			'form' => $form,
		]);
	}

	#[Route('/create_table', name: 'create_table')]
	public function create_table(): Response
	{
		try {
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
				"Table users has been created"
			);

		} catch (\Throwable $th) {
			throw new Exception($th);

		}
	}

	#[Route('/ex05', name: 'create_table')]
	public function index(): Response
	{
		return $this->render('base.html.twig');
	}
}
