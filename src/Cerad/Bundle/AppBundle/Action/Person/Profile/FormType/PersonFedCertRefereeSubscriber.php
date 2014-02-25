<?php

namespace Cerad\Bundle\AppBundle\Action\Person\Profile\FormType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

class PersonFedCertRefereeSubscriber implements EventSubscriberInterface
{
    private $factory;
    
    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory  = $factory;
    }
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $cert = $event->getData();

        if (!$cert) return;
        
        $fedRole  = $cert->getPersonFed()->getFedRole();
        $certRole = $cert->getRole();
        
        // TODO: Figure out correct mappings based on fed role and cert type
        $userBadgeName = 'cerad_person_ussfc_referee_badge';
        /*
        $fedRole = $personFed->getFedRole();
        switch($fedRole)
        {
            case 'USSFC':
                $fedKeyName = 'cerad_person_ussfc_id';
                $orgKeyName = 'cerad_person_ussf_state_id';
                break;
        }*/
        $form->add($this->factory->createNamed('badgeUser',$userBadgeName,null,array(
            'required'        => false,
            'auto_initialize' => false,
        )));
       
        return;
        
        $states = $this->workflow->getStateOptions($gameOfficial->getAssignState());
        
        $form->add($this->factory->createNamed('assignState','choice', null, array(
            'required'        => true,
            'auto_initialize' => false,
            'choices'         => $states,
        )));
        
        // Fill in user name if empty
        if (!$gameOfficial->getPersonNameFull())
        {
            $gameOfficial->setPersonNameFull($this->projectOfficial->getPersonName());
        }
        $form->add($this->factory->createNamed('personNameFull', 'text', null, array(
            'attr'      => array('size' => 30),
            'required'  => false,
            'read_only' => true,
            'auto_initialize' => false,
        )));
        return;
        
    }
}