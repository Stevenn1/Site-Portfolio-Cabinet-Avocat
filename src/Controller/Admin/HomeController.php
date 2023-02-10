<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * Cette fonction affiche la page d'accueil du site.
     * 
     * @IsGranted("ROLE_ADMIN")  
     * @Route("/admin", name="admin_home")
     */
    public function home()
    {
        return $this->render('admin/home/index.html.twig',);
    }
}
