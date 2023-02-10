<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RgpdController extends AbstractController
{
    /**
     * Cette fonction affiche la page de Politique de confidentialité.
     * 
     * @Route("/Politique-de-Confidentialité", name="app_politique_confidentialité")
     */
    public function PolitiqueConfidential(): Response
    {
        return $this->render('front/footer/Politique_De_Confidentialité.html.twig');
    }

    /**
     * Cette fonction affiche la page de conditions d'utilisation.
     * 
     * @Route("/conditions-utilisation", name="app_conditions_utilisation")
     */
    public function ConditionsUtilisation(): Response
    {
        return $this->render('front/footer/Conditions_Utilisation.html.twig');
    }

    /**
     * Cette fonction affiche la page des mentions légales.
     * 
     * @Route("/mentions-legales", name="app_mentions_legales")
     */
    public function MentionsLegales(): Response
    {
        return $this->render('front/footer/Mentions_Legales.html.twig');
    }

    /**
     * Cette fonction affiche la page d'aides.
     * 
     * @Route("/Aides", name="app_aides")
     */
    public function Aides(): Response
    {
        return $this->render('front/footer/aides.html.twig');
    }
}
