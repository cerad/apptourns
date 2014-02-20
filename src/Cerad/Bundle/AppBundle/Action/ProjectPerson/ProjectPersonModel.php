<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProjectPersonModel
{
    // Model Listener
    protected $project;
    protected $person;
    
    // Retrieved
    protected $personPlan;    // Project Person Plan
    protected $personFed;
    
    // Injected
    protected $dispatcher;
    protected $personRepo;
    
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    public function __construct($personRepo)
    {
        $this->personRepo = $personRepo;
    }
    public function getPerson () { return $this->person;  }
    public function getProject() { return $this->project; }
    public function getPersonPlan()
    {
        // Already got
        if ($this->personPlan) return $this->personPlan;
        
        $person  = $this->person;
        $project = $this->project;
        
        // Already registered
        $this->personPlan = $personPlan = $this->personRepo->findPlanByProjectAndPerson($project,$person);
        if ($personPlan) 
        {
            // Merge in any property changes
            $personPlan->mergeBasicProps($project->getPlan());
            return $personPlan;
        }
        // New one
        $this->personPlan = $personPlan = $this->person->createPlan();
        
        $personPlan->setPerson($person);
        $personPlan->setProjectId($project->getKey());
        $personPlan->mergeBasicProps($project->getPlan());
        
        return $personPlan;
    }
    public function create(Request $request)
    { 
        $this->person  = $request->attributes->get('person');
        $this->project = $request->attributes->get('project');
        return $this;
    }
    public function process()
    {
        if ($this->personPlan) $this->personRepo->persist($this->personPlan);
        
        $this->personRepo->flush();
    }
}