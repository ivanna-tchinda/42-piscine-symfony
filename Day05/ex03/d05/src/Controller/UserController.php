<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;

final class UserController extends AbstractController
{
	#[Route('/show_users', name: 'show_users')]
	public function show_users(EntityManagerInterface $entityManager): Response
	{

		$users = $entityManager->getRepository(User::class)->findAll();
		if (!$users) {
			throw $this->createNotFoundException(
				'No users found'
			);
		}

		return $this->render('users/index.html.twig',[
			'users' => $users
		]);
	}

	#[Route('/user', name: 'app_user')]
	public function index(Request $request, EntityManagerInterface $entityManager): Response
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
}
