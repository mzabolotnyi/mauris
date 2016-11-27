<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Display homepage
     */
    public function indexAction()
    {
        $response = $this->render('AppBundle:Default:index.html.twig', [
            'base_dir' => __DIR__,
        ]);

        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }
}
