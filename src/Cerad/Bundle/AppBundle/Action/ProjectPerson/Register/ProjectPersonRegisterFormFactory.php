<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson\Register;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\Form\FormFactoryInterface;

use Cerad\Bundle\AppBundle\Action\ProjectPerson\ProjectPersonModel;

use Cerad\Bundle\CoreBundle\FormType\DynamicFormType;

class ProjectPersonRegisterFormFactory
{
    protected $router;
    protected $formFactory;
    
    public function setRouter     (RouterInterface      $router)      { $this->router      = $router; }
    public function setFormFactory(FormFactoryInterface $formFactory) { $this->formFactory = $formFactory; }
    
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
