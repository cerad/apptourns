<?php

namespace Cerad\Bundle\AppBundle\Action\Home;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\Controller as View;

class HomeView extends View
{   
    // Not sure
    protected $tplName = '@CeradApp/Home/HomeIndex.html.twig';
    
    public function renderResponse(Request $request)
    {
        $requestAttributes = $request->attributes;
        
        $model      = $requestAttributes->get('model');
        $userPerson = $requestAttributes->get('userPerson');
        
        $modelProjects = $model->getProjects();
        $viewProjects = array();
        foreach($modelProjects as $modelProject)
        {
            $params = array('_project' => $modelProject->getSlugPrefix(), '_person' => $userPerson->getId());
            
            $registerUrl = $this->generateUrl('cerad_person__project_person__register',$params);
            
            $viewProjects[] = array('desc' => $modelProject->getDesc(), 'registerUrl' => $registerUrl);
        }
        $tplData = array();
        $tplData['projects'] = $viewProjects;
        
        $tplName = $requestAttributes->has('_template') ? $requestAttributes->get('_template') : $this->tplName;
        
        return $this->regularResponse($tplName,$tplData);
    }
}