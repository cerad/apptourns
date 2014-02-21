<?php

namespace Cerad\Bundle\AppBundle\Action\UserPerson\Create;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class UserPersonCreateController extends ActionController
{   
    public function action(Request $request, UserPersonCreateModel $model, FormInterface $form)
    {   
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model->process();
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
            
            return $this->redirect('cerad_app__home');
        }
        
        $tplData = array();
        $tplData['form'] = $form->createView();
        
        $tplName = $request->attributes->get('_template');
        return $this->regularResponse($tplName, $tplData);

    }    
}
