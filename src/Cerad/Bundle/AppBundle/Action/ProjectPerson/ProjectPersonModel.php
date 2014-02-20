<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPerson;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Cerad\Bundle\CoreBundle\Events\PersonEvents;
use Cerad\Bundle\CoreBundle\Event\RegisterProjectPersonEvent;

class ProjectPersonModel
{
    // Model Listener
    protected $project;
    protected $person;
    
    // Retrieved
    protected $personFed;
    protected $personPlan;    // Project Person Plan
    
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
    public function getPersonFed()
    {
        // Already got
        if ($this->personFed) return $this->personFed;
        
        $person  = $this->person;
        $project = $this->project;
        
        // Already registered
        $this->personFed = $personFed = $this->personRepo->findFedByProjectAndPerson($project,$person);
        if ($personFed) return $personFed;
        
        // New one
        $this->personFed = $personFed = $this->person->createFed();
        
        $personFed->setPerson ($person);
        $personFed->setFed    ($project->getFed());
        $personFed->setFedRole($project->getFedRole());

        return $personFed;
    }
    public function create(Request $request)
    { 
        $this->person  = $request->attributes->get('person');
        $this->project = $request->attributes->get('project');
        return $this;
    }
    public function processRegistration()
    {
        $personFed  = $this->getPersonFed();
        $personPlan = $this->getPersonPlan();
        
        $registerEvent = new RegisterProjectPersonEvent($this->project,$this->person,$personPlan,$personFed);
        $this->dispatcher->dispatch(PersonEvents::RegisterProjectPerson,$registerEvent);
        
        $this->personRepo->persist($this->personFed);
        $this->personRepo->persist($this->personPlan);
        
        $this->personRepo->flush();
    }
}