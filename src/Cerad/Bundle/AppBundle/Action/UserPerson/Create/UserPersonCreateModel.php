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
    public $name;
    public $email;
    public $password;
    
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
        return $this;
    }
    public function process()
    {
        $personFed = $this->personRepo->findFedByFedKey($this->fedKey);
        
        if (!$personFed)
        {
            // Build a complete person record
            $person = $this->personRepo->createPerson();
            $person->getPersonPersonPrimary();
            
            // A value object
            $personName = $person->createName();
            $personName->full = $this->name;
            $person->setName($personName);
            
            $person->setEmail($this->email);
           
            $personFed = $person->getFed($this->fedRole);
            $personFed->setFedKey($this->fedKey);
        }
        else
        {
            // TODO: More security, check email etc
            $person = $personFed->getPerson();
            
            // If this person has an account then we need to use it as well
            // Or else two accounts pointing to the same person?
            
        }
        $this->person    = $person;
        $this->personFed = $personFed;
        
        /* ==================================================
         * Now take care of the account
         * Already checked for duplicate emails/user names
         */
        $user = $this->userManager->createUser();
        
        $user->setEmail         ($this->email);
        $user->setUsername      ($this->email);
        $user->setAccountName   ($this->name);
        $user->setAccountEnabled(true);
        $user->setPasswordPlain ($this->password);
        $user->setPersonGuid    ($person->getGuid());
        
        $this->user = $user;
        
        /* ===============================
         * And persist
         */
        $this->userManager->updateUser($user);
        
        $this->personRepo->persist($person);
        $this->personRepo->flush();
    }
}