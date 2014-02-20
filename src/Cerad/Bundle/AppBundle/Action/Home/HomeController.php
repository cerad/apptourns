<?php

namespace Cerad\Bundle\AppBundle\Action\Home;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\Controller;

class HomeController extends Controller
{
    public function action(Request $request, HomeModel $model)
    {   
        // Special case for session timeouts
        if (!$this->isGranted('ROLE_USER'))
        {
            return $this->redirectResponse('cerad_app__welcome');
        }
        
        $tplData = array();
        $tplData['projects'] = $model->getProjects();
        
        $tplName = $request->attributes->get('_template');
        
        return $this->renderResponse($tplName,$tplData);
    }    
}
