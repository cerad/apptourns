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
            $registerUrl = $this->generateUrl('cerad_person__project_person__register',array(
                '_project' => $modelProject->getSlugPrefix(), 
                '_person'  => $userPerson->getId()
            ));   
            $personsUrl = $this->generateUrl('cerad_person_admin__project_persons__list',array(
                '_project' => $modelProject->getSlug(),
            ));
            $viewProjects[] = array(
                'desc'        => $modelProject->getDesc(), 
                'registerUrl' => $registerUrl,
                'personsUrl'  => $personsUrl,
            );
        }
        $tplData = array();
        $tplData['projects'] = $viewProjects;
        
        $tplName = $requestAttributes->has('_template') ? $requestAttributes->get('_template') : $this->tplName;
        
        return $this->regularResponse($tplName,$tplData);
    }
}