<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProjectPersonModel
{
    public $project;
    public $person;
    public $plan;    // Project Person Plan
    public $fed;
    
    protected $dispatcher;
    
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    public function process()
    {   
    }
    
    public function create(Request $request)
    { 
        $this->project = $request->attributes->get('project');
        $this->person  = $request->attributes->get('person');
        $this->plan    = $request->attributes->get('plan');
        $this->fed     = $request->attributes->get('fed');
        
        return $this;
    }    
}