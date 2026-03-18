<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

final class UserController extends AbstractController
{
	#[Route('/success', name: 'success')]
	public function success(): Response
	{
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
	}
	#[Route('/create_table', name: 'create_table')]
	public function index(): Response
	{
		return $this->render('base.html.twig');
	}
}
