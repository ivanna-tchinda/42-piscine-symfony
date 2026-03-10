<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class ColorsController extends AbstractController
{
	#[Route('/e03', name: 'index')]
	public function index(): Response
	{
		$color_titles = array(
			'black' => array('20','20','20'),
			'red' => array('20','0','0'),
			'blue' => array('0','0','20'),
			'green' => array('0','20','0')
		);
		$nb_of_colors = $this->getParameter('e03.number_of_colors');
		return $this->render('base.html.twig',[
			'color_titles' => $color_titles,
			'nb_of_colors' => $nb_of_colors
		]);
	}
}
