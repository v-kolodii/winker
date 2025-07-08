<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontendController extends AbstractController
{
    #[Route('/{reactRouting}', name: 'react_app', requirements: ['reactRouting' => '^(?!admin|api|privacy-policy|login|logout|register|_wdt|_profiler).+'], defaults: ['reactRouting' => null])]
    public function index(): Response
    {
        return new BinaryFileResponse(
            $this->getParameter('kernel.project_dir') . '/public/react/index.html'
        );
    }
}
