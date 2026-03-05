<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class MultiplePagesController extends AbstractController
{
	#[Route('/e01/', name: 'index')]
	function index(): Response
	{
		try{
			$list_of_articles = ['chat', 'canard', 'lama'];
			$main_page = 0;
			$page_title = 'index';
		$page_content = "<ul><li><a href='127.0.0.1:8000/e01/chat'>Chat</a></li><li><a href='127.0.0.1:8000/e01/lama'>Lama</a></li><li><a href='127.0.0.1:8000/e01/canard'>Canard</a></li></ul>";
			return $this->render('base.html.twig',[
				'list_of_articles' => $list_of_articles,
				'main_page' => $main_page,
				'page_title' => $page_title,
				'page_content' => $page_content
		]	
		);}
		catch(Exception $e) {
			throw $e;
		}
	}
	#[Route('/e01/chat', name: 'chat')]
	public function chat(): Response
	{
		$list_of_articles = ['chat', 'canard', 'lama'];
		$main_page = 1;
		$page_title = "Le chat";
		$page_content = "Le chat domestique (Felis catus ou Felis silvestris catus) est la forme domestique du chat sauvage Felis silvestris, une espèce de mammifères carnivores, de la famille des Félidés. Selon les résultats de travaux menés en 2006 et 2007, le chat domestique est une sous-espèce du chat sauvage issue d'ancêtres appartenant à la sous-espèce du chat sauvage d'Afrique (Felis silvestris lybica). Les premières domestications ont probablement lieu au Néolithique, entre 6000 et 8000 av. J.-C., dans le Croissant fertile, époque correspondant au début de la culture de céréales et à l'engrangement de réserves susceptibles d'être attaquées par des rongeurs, le chat devenant alors pour l'Homme un auxiliaire utile se prêtant à la domestication. Le chat domestique est l'un des principaux animaux de compagnie et compte aujourd'hui une cinquantaine de races différentes reconnues par les instances de certification. Dans de très nombreux pays, le chat entre dans le cadre de la législation sur les carnivores domestiques à l'instar du chien et du furet. Essentiellement territorial, le chat est un prédateur de petites proies comme les rongeurs ou les oiseaux. Les chats ont diverses vocalisations dont les ronronnements, les miaulements, les feulements ou les grognements, bien qu'ils communiquent principalement par des positions faciales et corporelles et des phéromones.
Tout d'abord vénéré par les Égyptiens, il est diabolisé en Europe au Moyen Âge et ne retrouve ses lettres de noblesse qu'au XVIIIe siècle. En Asie, le chat reste synonyme de chance, de richesse ou de longévité. Ce félin laisse son empreinte dans la culture populaire et artistique, tant au travers d'expressions populaires que de représentations diverses au sein de la littérature, de la peinture ou encore de la musique. À partir de la fin du XXe siècle, les dommages qu'il occasionne à la biodiversité sont mieux compris, et il fait partie des cent espèces envahissantes parmi les plus nuisibles du monde. ";
	return $this->render('base.html.twig', [
			'main_page' => $main_page,
			'list_of_articles' => $list_of_articles,
			'page_title' => $page_title,
			'page_content' => $page_content
		]);
	}

	#[Route('/e01/lama', name: 'lama')]
	public function lama(): Response
	{
		$main_page = 1;
		$list_of_articles = ['chat', 'canard', 'lama'];
		$page_title = "Le lama";
		$page_content = "Le lama blanc (Lama glama), ou plus simplement lama, est une espèce de Camélidés d'Amérique du Sud, mais ses origines lointaines ont été retracées jusqu'en Amérique du Nord, d'où il a disparu à la période de l'Éocène.

Il a été domestiqué de longue date à partir du guanaco.

La sélection par les éleveurs a donné plusieurs races ou variétés caractérisées par leur fourrure plus ou moins longue. 
Le terme « lama » est souvent utilisé de manière plus large pour s'appliquer aux quatre espèces animales proches qui constituent la branche sud-américaine des camélidés : le lama blanc lui-même, l'alpaga, le guanaco et la vigogne (voir le genre lama). Stricto sensu, malgré quelques croisements, le lama, animal domestique, a pour plus proche cousin le guanaco, animal sauvage, alors que l'alpaga, animal domestique, a pour plus proche cousin la vigogne, animal sauvage. ";
return $this->render('base.html.twig', [
	'main_page' => $main_page,
			'page_title' => $page_title,
			'list_of_articles' => $list_of_articles,
			'page_content' => $page_content
		]);
	}

	#[Route('/e01/canard', name: 'canard')]
	public function canard(): Response
	{
		$main_page = 1;
		$page_title = "Le canard";
		$list_of_articles = ['chat', 'canard', 'lama'];
		$page_content = "« Canard » est un terme générique qui désigne des oiseaux aquatiques ansériformes, au cou court, au large bec jaune, orangé aplati, aux très courtes pattes palmées et aux longues ailes pointues, domestiqués ou non.

Ils font pour la plupart partie de la famille des Anatidés. Ce mot désigne des espèces qui ne portent pas nécessairement un nom vernaculaire contenant le terme canard. En effet, certaines espèces qualifiées de canards sont désignées par des noms vernaculaires comportant des termes comme dendrocygnes, sarcelles, tadornes ou brassemers. Le canard sauvage est un oiseau migrateur. C'est du Canard colvert que sont issues de nombreuses races de canards domestiques. 
L'origine du terme canard n'est pas connue. Une orthographe connue du XIIIe siècle donne quanart. Il est probable que ce terme dérive d'une onomatopée, comme caqueter. Ce terme est aussi générique ; les espèces appelées « canard » peuvent être plus spécifiquement appelées pilet, sarcelle, tadorne…

Pour désigner son cri, on dit que le canard cancane et l'onomatopée « coin-coin » est utilisée pour décrire ses vocalisations.

Le canard femelle adulte est la cane ; le jeune canard, le caneton ; le canard sauvage de l'année, ne maîtrisant pas encore son vol, le halbran. ";
return $this->render('base.html.twig', [
	'main_page' => $main_page,
			'page_title' => $page_title,
			'list_of_articles' => $list_of_articles,
			'page_content' => $page_content
		]);
	}
}
