<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson\Register;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\Controller;

use Cerad\Bundle\AppBundle\Action\ProjectPerson\ProjectPersonModel;

class ProjectPersonRegisterController extends Controller
{   
    public function action(Request $request, ProjectPersonModel $model, FormInterface $form)
    {   
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model->processRegistration();
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
            
            return $this->redirect('cerad_app__home');
        }
        
        $tplData = array();
        $tplData['form']    = $form->createView();
        $tplData['person']  = $model->getPerson();
        $tplData['project'] = $model->getProject();
        
        $tplName = $request->attributes->get('_template');
        return $this->regularResponse($tplName, $tplData);

    }    
}
