<?php

namespace Art\JotestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ArtJotestBundle:Default:index.html.twig', array('name' => $name));
    }
}
