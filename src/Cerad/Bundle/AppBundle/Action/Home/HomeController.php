<?php

namespace Cerad\Bundle\AppBundle\Action\Home;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\Controller;

class HomeController extends Controller
{
    public function action(Request $request, $model, $userPerson = null)
    {   
        // Special case for session timeouts
        if (!$this->isGranted('ROLE_USER'))
        {
            return $this->redirectResponse('cerad_app__welcome');
        }
        return;
        
        $projects = $model->getProjects();
        foreach($projects as $project)
        {
            $params = array('_project' => $project->getSlugPrefix(),'_person' => $userPerson->getId());
            $project->registerUrl = $this->generateUrl('cerad_person__project_person__register',$params);
        }
        $tplData = array();
        $tplData['projects']   = $projects;
        $tplData['userPerson'] = $request->attributes->get('userPerson');
        
        $tplName = $request->attributes->get('_template');
        
        return $this->renderResponse($tplName,$tplData);
    }    
}
