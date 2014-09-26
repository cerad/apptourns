<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPersons\Registered;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class RegisteredController extends ActionController
{   
    public function action(Request $request, $project)
    {   
        $tplData = array();
        $tplData['project'] = $project;
        
        $tplName = $request->attributes->get('_template');
        return $this->regularResponse($tplName, $tplData);

    }    
}
