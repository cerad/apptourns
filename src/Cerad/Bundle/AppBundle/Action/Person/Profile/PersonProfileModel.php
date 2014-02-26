<?php

namespace Cerad\Bundle\AppBundle\Action\Person\Profile;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class PersonProfileModel extends ActionModelFactory
{
    public $person;
    
    public $personFed;
    
    protected $fedRole;
    protected $personRepo;
    
    public function __construct($personRepo,$fedRole)
    {
        $this->fedRole    = $fedRole;
        $this->personRepo = $personRepo;
    }
    public function getPerson() { return $this->person; }
    
    public function create(Request $request)
    {
        $this->person = $person = $request->attributes->get('person');
        
        $this->personFed = $personFed = $person->getFed($this->fedRole);
 
        return $this;
    }
    public function process()
    {
        $this->personRepo->flush();
    }
}