<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProjectPersonModel
{
    // Model Listener
    public $project;
    public $person;
    
    // Retrieved
    public $plan;    // Project Person Plan
    public $fed;
    
    // Injected
    protected $dispatcher;
    protected $personRepo;
    
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    public function __construct($personRepo)
    {
        $this->personRepo = $personRepo;
    }
    public function process()
    {   
    }
    
    public function create(Request $request)
    { 
        $this->project = $project = $request->attributes->get('project');
        $this->person  = $person  = $request->attributes->get('person');
        
        $this->plan    = $person->getPlan($this->project);
        
      //$this->fed     = $request->attributes->get('fed');
        
        return $this;
    }
}