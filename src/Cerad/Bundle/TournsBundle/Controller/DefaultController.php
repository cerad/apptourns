<?php

namespace Cerad\Bundle\TournsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CeradTournsBundle:Default:index.html.twig', array('name' => $name));
    }
}
