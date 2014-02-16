<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson\Register;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Cerad\Bundle\AppBundle\Action\ProjectPerson\ProjectPersonModel;

class ProjectPersonRegisterController
{
    protected $router;
    protected $templating;
    
    public function setRouter    (RouterInterface $router)     { $this->router     = $router;     }
    public function setTemplating(EngineInterface $templating) { $this->templating = $templating; }
    
    public function action(Request $request, ProjectPersonModel $model, FormInterface $form)
    {   
        die('register.action');
    }    
}
