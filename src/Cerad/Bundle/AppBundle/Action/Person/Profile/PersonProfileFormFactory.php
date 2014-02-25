<?php

namespace Cerad\Bundle\AppBundle\Action\Person\Profile;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameAndEmailUniqueConstraint;

use Symfony\Component\Validator\Constraints\Email    as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank as NotBlankConstraint;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

use Cerad\Bundle\CoreBundle\Events\PersonEvents;

use Cerad\Bundle\CoreBundle\Event\FindFormTypeEvent;

class PersonProfileFormFactory extends ActionFormFactory
{
    public function create(Request $request, PersonProfileModel $model)
    {   
        // Need to form type for the default fed role
        $event = new FindFormTypeEvent($model->personFed->getFedRole());
        $this->dispatcher->dispatch(PersonEvents::FindFedKeyFormType,$event);
        $fedKeyFormType = $event->getFormType();
        
        
        $actionRoute = $request->attributes->get('_route');
        $actionUrl   = $this->generateUrl($actionRoute,array('_person' => $model->person->getId()));
        
        $formOptions = array(
            'cascade_validation' => true,
            'method' => 'POST',
            'action' => $actionUrl,
            'attr'   => array('class' => 'cerad_common_form1'),
        );
        
        $builder = $this->formFactory->create('form',$model,$formOptions);
        
        $builder->add('person',   new FormType\PersonFormType());
        $builder->add('personFed',new FormType\PersonFedFormType());
        
        $builder->add('update', 'submit', array(
            'label' => 'Update Information',
            'attr'  => array('class' => 'submit'),
        ));
        
        return $builder;       
     }
}
