<?php

namespace Cerad\Bundle\AppBundle\Action\Person\Profile;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class PersonProfileController extends ActionController
{   
    public function action(Request $request, PersonProfileModel $model, FormInterface $form)
    {   
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model->process();
            
          //$formAction = $form->getConfig()->getAction();
          //return $this->redirectResponse($formAction);  // To form
            
            $personId = $model->getPerson()->getId();
            return $this->redirectResponse('cerad_person__person__profile',array('_person' => $personId));
        }
        
        $tplData = array();
        $tplData['form']   = $form->createView();
        $tplData['person'] = $model->person;
        $tplData['homeUrl'] = $this->generateUrl('cerad_app__home');
        
        $tplName = $request->attributes->get('_template');
        return $this->regularResponse($tplName, $tplData);

    }    
}
