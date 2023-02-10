<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RdvController extends AbstractController
{
    /**
     * Cette fonction affiche la page rendez-vous.
     * 
     * @Route("/rendez-vous", name="app_rdv")
     */
    public function rdv()
    {
        return $this->render('front/rdv/rdv.html.twig');
    }

    /**
     * Cette fonction affiche la page de confirmation.
     * 
     * @Route("/confirmation", name="app_confirmation")
     */
    public function confirmation()
    {
        return $this->render('front/rdv/confirmation.html.twig');
    }
}
