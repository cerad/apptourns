<?php

namespace Cerad\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CeradAppBundle:Default:index.html.twig', array('name' => $name));
    }
}
