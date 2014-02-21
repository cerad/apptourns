<?php

namespace Cerad\Bundle\AppBundle\Action\UserPerson\Create;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameAndEmailUniqueConstraint;

use Symfony\Component\Validator\Constraints\Email    as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank as NotBlankConstraint;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

use Cerad\Bundle\CoreBundle\Events\PersonEvents;

use Cerad\Bundle\CoreBundle\Event\FindFormTypeEvent;

class UserPersonCreateFormFactory extends ActionFormFactory
{
    public function create(Request $request, UserPersonCreateModel $model)
    {   
        // Need to form type for the default fed role
        $event = new FindFormTypeEvent($model->fedRole);
        $this->dispatcher->dispatch(PersonEvents::FindFedKeyFormType,$event);
        $fedKeyFormType = $event->getFormType();
        
        $formOptions = array(
            'cascade_validation' => true,
            'attr' => array('class' => 'cerad_common_form1'),
        );
        $constraintOptions = array();
        
        $builder = $this->formFactory->createBuilder('form',$model,$formOptions);
        
        // Hack to trim off _form sub routes
        $actionRoute = $request->attributes->get('_route');
      //if (substr($actionRoute,-5) == '_form') $actionRoute = substr($actionRoute,0,strlen($actionRoute)-5);
        
        $builder->setAction($this->generateUrl($actionRoute));
        
        $builder->add('fedKey',$fedKeyFormType, array(
            'required' => false,
        ));
        $builder->add('email','email', array(
            'required' => true,
            'label'    => 'Arbiter Email',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
                new EmailConstraint   ($constraintOptions),
                new UsernameAndEmailUniqueConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
         $builder->add('name','text', array(
            'required' => true,
            'label'    => 'Your Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('password', 'repeated', array(
            'type'     => 'password',
            'label'    => 'Zayso Password',
            'required' => true,
            'attr'     => array('size' => 20),
            
            'invalid_message' => 'The password fields must match.',
            'constraints'     => new NotBlankConstraint($constraintOptions),
            'first_options'   => array('label' => 'Zayso Password'),
            'second_options'  => array('label' => 'Zayso Password(confirm)'),
            
            'first_name'  => 'pass1',
            'second_name' => 'pass2',
        ));
        $builder->add('create', 'submit', array(
            'label' => 'Create Account',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
