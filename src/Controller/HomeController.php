<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
   #[Route('/', name: 'landing_page')]
public function index(): Response
{
    return $this->render('landing/index.html.twig');
}

#[Route('/faq', name: 'FAQ_page')]
public function faq(): Response
{
    return $this->render('landing/faq.html.twig');
}
}