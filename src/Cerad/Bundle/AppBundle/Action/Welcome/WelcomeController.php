<?php

namespace Cerad\Bundle\AppBundle\Action\Welcome;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class WelcomeController
{
    protected $router;
    protected $templating;
    
    public function setRouter    (RouterInterface $router)     { $this->router     = $router;     }
    public function setTemplating(EngineInterface $templating) { $this->templating = $templating; }
    
    public function action(Request $request, WelcomeModel $model)
    {   
        $tplData = array();
        $tplData['projects'] = $model->getProjects();
        
        $tplName = $request->attributes->get('_template');
        return $this->templating->renderResponse($tplName,$tplData);
    }    
}
