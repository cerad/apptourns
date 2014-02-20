<?php

namespace Cerad\Bundle\AppBundle\Action;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProjectsModel
{   
    // Injected
    protected $dispatcher;
    protected $projectRepo;
    
    protected $created = false; // Singleton for sub requests?
    
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
        return $this->projectRepo->findBy(array('status' => 'Active','role' => 'Tournament'));
    }
    public function create(Request $request)
    { 
        if ($this->created) return;
        
        $this->created = true;
        
        return $this;
        if ($request);
    }
}