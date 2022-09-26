<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontpageController extends AbstractController
{

    #[Route('/')]
    public function frontpage(): Response
    {
        return $this->render('frontpage/home.html.twig', [
            'title' => 'SR - Secret API(v1)',
        ]);
    }
}