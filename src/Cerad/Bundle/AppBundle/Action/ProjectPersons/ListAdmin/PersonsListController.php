<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPersons\ListAdmin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class PersonsListController extends ActionController
{   
    public function action(Request $request, PersonsListModel $model, FormInterface $form)
    {   
        die('persons.list.controller');
        
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
