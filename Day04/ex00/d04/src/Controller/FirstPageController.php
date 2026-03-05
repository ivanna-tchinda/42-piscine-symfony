<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FirstPageController
{
	#[Route('/e00/firstpage', name: 'firstpage')]
	public function helloworld(): Response
	{
		return new Response('<html><body><p>Hello World!</p></body></html>');
	}
}
