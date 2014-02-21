<?php

namespace Cerad\Bundle\AppBundle\Action\UserPerson\Create;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Events\PersonEvents;
use Cerad\Bundle\CoreBundle\Event\RegisterProjectPersonEvent;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class UserPersonCreateModel extends ActionModelFactory
{
    public $fedKey;
    public $fedRole;
    public $user;
    public $name;
    public $email;
    public $password;
    
    // Injected
    protected $userManager;
    protected $personRepo;
    
    public function __construct($userManager,$personRepo,$fedRole)
    {
        $this->userManager = $userManager;
        $this->personRepo  = $personRepo;
        $this->fedRole     = $fedRole;
    }
    public function create(Request $request)
    { 
        $this->user = $this->userManager->createUser();
        
        return $this;
    }
    public function process()
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