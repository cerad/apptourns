<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson\Register;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\FormType\DynamicFormType;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

use Cerad\Bundle\AppBundle\Action\ProjectPerson\ProjectPersonModel;

class ProjectPersonRegisterFormFactory extends ActionFormFactory
{
    public function create(Request $request, ProjectPersonModel $model)
    {   
      //$person     = $model->getPerson();
        $project    = $model->getProject();
        $personPlan = $model->getPersonPlan();
        
        $actionRoute = $request->attributes->get('_route');
        
        $attrs = array(
            'id'    => $actionRoute,
            'class' => $actionRoute . ' cerad_common_form1');
        
        $builder = $this->formFactory->createBuilder('form',$personPlan, array('attr' => $attrs));
        
        $actionUrl = $this->router->generate($actionRoute,array
        (
            '_person'  => $request->attributes->get('_person'),
            '_project' => $request->attributes->get('_project'),
        ));
        $builder->setAction($actionUrl);
        
        $basicType = new DynamicFormType('basic',$project->getPlan());
        $builder->add('basic',$basicType, array('label' => false));
       
        // Buttons and such
        $builder->add('register', 'submit', array(
            'label' => 'Email Application',
            'attr'  => array('class' => 'submit'),
        ));  
//        $builder->add( 'reset','reset', array(
//            'label' => 'Reset',
//            'attr'  => array('class' => 'submit'),
//        ));  
        return $builder->getForm();
    }
}
