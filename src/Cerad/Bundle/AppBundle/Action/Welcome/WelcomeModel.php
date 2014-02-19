<?php

namespace Cerad\Bundle\AppBundle\Action\Welcome;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WelcomeModel
{   
    // Injected
    protected $dispatcher;
    protected $projectRepo;
    protected $created = false;
    
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    public function __construct($projectRepo)
    {
        $this->projectRepo = $projectRepo;
    }
    public function process()
    {   
    }
    public function getProjects()
    {
        return $this->projectRepo->findAll();
    }
    public function create(Request $request)
    { 
        return $this;
        
        $this->project = $project = $request->attributes->get('project');
        $this->person  = $person  = $request->attributes->get('person');
        
        $this->plan    = $person->getPlan($this->project);
        
      //$this->fed     = $request->attributes->get('fed');
        
        return $this;
    }
}