<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson\Register;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\Form\FormFactoryInterface;

use Cerad\Bundle\AppBundle\Action\ProjectPerson\ProjectPersonModel;

class ProjectPersonRegisterFormFactory
{
    protected $router;
    protected $formFactory;
    
    public function setRouter     (RouterInterface      $router)      { $this->router      = $router; }
    public function setFormFactory(FormFactoryInterface $formFactory) { $this->formFactory = $formFactory; }
    
    public function create(Request $request, ProjectPersonModel $model)
    {   
        // The 'form' is actually the type
        $builder = $this->formFactory->createBuilder('form',$model);
        return $builder->getForm();
die('form.factory');
        $actionRoute = $request->attributes->get('_route');
        $actionUrl = $this->router->generate($actionRoute,array
        (
            '_game'    => $model->game->getNum(),
            '_project' => $request->attributes->get('_project'),
        ));
        $builder->setAction($actionUrl);
        
        $builder->add('num','text',array(
            'label'     => 'Game Number',
            'read_only' => true,
            'attr'      => array('size' => 4),
        ));
        $builder->add('dtBeg','datetime',array(
            'label' => 'Date Time',
            'minutes' => array(0,5,10,15,20,25,30,35,40,45,50,55),
        ));
        /* Needs more work
        $builder->add('dateBegin','date',array(
            'label' => 'Date',
        ));
        $builder->add('timeBegin','time',array(
            'label' => 'Time Begin',
            'minutes' => array(0,5,10,15,20,25,30,35,40,45,50,55),
        ));
         * */
         
        $builder->add('field', 'entity', array(
            'class'    => 'Cerad\Bundle\GameBundle\Entity\GameField',
            'property' => 'name',
            'choices'  => $model->getGameFields(),
        ));
        $builder->add('levelKey','text',array(
            'label'     => 'Level',
            'read_only' => true,
            'attr'      => array('size' => 20),
        ));
        $builder->add('group','text',array(
            'label'     => 'Group',
            'read_only' => true,
            'attr'      => array('size' => 20),
        ));
        $teamNameChoices = $model->getTeamNameChoices();
        $gameTeamFormType = new GameUpdateByScorerTeamFormType($teamNameChoices);
        
        $builder->add('homeTeam',$gameTeamFormType);
        $builder->add('awayTeam',$gameTeamFormType);
    
        $builder->add('update', 'submit', array(
            'label' => 'Update',
            'attr'  => array('class' => 'submit'),
        ));  
        $builder->add( 'reset','reset', array(
            'label' => 'Reset',
            'attr'  => array('class' => 'submit'),
        ));  
        return $builder->getForm();
    }
}
