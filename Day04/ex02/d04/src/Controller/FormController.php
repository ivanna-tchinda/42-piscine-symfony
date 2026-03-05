<?php

namespace App\Controller;

use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;


class FormController extends AbstractController
{
	#[Route('/e02/', name: 'index')]
	public function new(Request $request): Response
	{
		$messageEntity = new Message();

		$form = $this->createFormBuilder($messageEntity)
            		->add('message', TextType::class)
			->add('isTimestamp', ChoiceType::class, [
				'choices' => [
					'Yes' => true,
					'No' => false,
				]
			])
            		->add('save', SubmitType::class, ['label' => 'Create Message'])
			->getForm();
		$form->handleRequest($request);
        	if ($form->isSubmitted() && $form->isValid()) {
            		$messageEntity = $form->getData();
			
			$filesystem = new Filesystem();
			$rootPath = '../';
			$filename = $rootPath . $this->getParameter('filename');
			try {
				if($filesystem->exists($filename) == false){
					$filesystem->touch($filename);
				}
				$filesystem->appendToFile($filename, "\n" . $messageEntity->getMessage() . "\n");
				if($messageEntity->getIsTimestamp())
				{
					$filesystem->appendToFile($filename, new \DateTime()->format('d-m-Y')."\n");
				}
			} catch (IOExceptionInterface $exception) {
    				echo "An error occurred while creating your directory at ".$exception->getPath();
			}
			
			return $this->render('base.html.twig', [
				'form' => $form,
				'message' => $messageEntity->getMessage(),
				'timestamp' => $messageEntity->getIsTimestamp()
			]);
        	}
		return $this->render('base.html.twig', [
			'form' => $form,
			'message' => null,
			'timestamp' => false
		]);
	}
}
