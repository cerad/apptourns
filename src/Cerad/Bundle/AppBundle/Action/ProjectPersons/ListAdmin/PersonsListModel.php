<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPersons\ListAdmin;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Events\PersonEvents;
use Cerad\Bundle\CoreBundle\Event\RegisterProjectPersonEvent;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class PersonsListModel extends ActionModelFactory
{
    // Model Listener
    protected $project;
    
    // Injected
    protected $personRepo;
    
    public function __construct($personRepo)
    {
        $this->personRepo = $personRepo;
    }
    public function getProject() { return $this->project; }
    
    public function create(Request $request)
    { 
        $this->project = $request->attributes->get('project');
        return $this;
    }
    public function getPersons()
    {
        return array();
    }
    public function processSearch()
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