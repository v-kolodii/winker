<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WinkerController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {

        return $this->render('index.html.twig');
    }

    #[Route('/privacy-policy', name: 'privacy_policy')]
    public function privacyPolicy(): Response
    {

        return $this->render('privacy.html.twig');
    }
}
