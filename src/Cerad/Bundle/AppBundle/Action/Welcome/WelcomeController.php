<?php

namespace Cerad\Bundle\AppBundle\Action\Welcome;

use Symfony\Component\HttpFoundation\Request;

//  Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\Controller;

class WelcomeController extends Controller
{
    public function action(Request $request, $model)
    {   
        // Special case for session timeouts
        if ($this->isGranted('ROLE_USER'))
        {
            return $this->redirectResponse('cerad_app__home');
        }
        $tplData = array();
        $tplData['projects'] = $model->getProjects();
        
        $tplName = $request->attributes->get('_template');
        
        return $this->regularResponse($tplName,$tplData);
    }    
}
