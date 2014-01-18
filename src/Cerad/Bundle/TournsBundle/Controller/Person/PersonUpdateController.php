<?php
namespace Cerad\Bundle\TournsBundle\Controller\Person;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\Validator\Constraints\Email     as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

class PersonUpdateController extends MyBaseController
{
    public function updateAction(Request $request)
    {   
        // Security
        if (!$this->hasRoleUser()) { return $this->redirect('cerad_tourn_welcome'); }
        
        // Simple model
        $model = $this->createModel($request);
        if ($model['_response']) return $model['response'];

        $form = $this->createModelForm($model);
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $modelPosted = $form->getData();
            
            $this->processModel($modelPosted);
            
            return $this->redirect('cerad_tourn_home');
            return $this->redirect('cerad_tourn_person_update',array('personId' => $person2->getId()));
        }
        
        $tplData = array();
        $tplData['form']   = $form->createView();
        $tplData['person'] = $model['person'];
        return $this->render($model['_template'], $tplData);
    }
    protected function processModel($model)
    { 
        // Update person
        $person = $model['person'];
        $name   = $person->getName();
        $name->full  = $model['personNameFull'];
        $name->first = $model['personNameFirst'];
        $name->last  = $model['personNameLast'];
        $person->setName($name);
        
        $address = $person->getAddress();
        $address->city  = $model['personAddressCity' ];
        $address->state = $model['personAddressState'];
        $person->setAddress($address);
        
        $person->setEmail($model['personEmail']);
        $person->setPhone($model['personPhone']);
        
        // Certs
        $orgKey    = $model['orgKey'   ];
        $badge     = $model['badge'    ];
        $upgrading = $model['upgrading'];
        
        $personFed        = $person->getFed($this->getFedRole());
        $personFedCertRef = $personFed->getCertReferee();
        
        $personFed->setOrgKey($orgKey);
        $personFedCertRef->setBadgeUser($badge);
        $personFedCertRef->setUpgrading($upgrading);
        
        // And persist
        $personRepo = $this->get('cerad_person.person_repository');
        $personRepo->save($person);
        $personRepo->commit();
        
        // Done
        return $model;
    }
    /* ===============================================
     * Person + referee cert
     */
    protected function createModel(Request $request)
    {
        // Init model
        $model = parent::createModel($request);

        // Get the person
        $personId   = $request->get('personId');
        $personRepo = $this->get('cerad_person.person_repository');
        $person = null;
        
        // If passed an id then use it
        if ($personId)
        {
            $person = $personRepo->find($personId);
        }
        
        // Use the account person
        if (!$person) $person = $this->getUserPerson();
        if (!$person)
        {
            $model['_response'] = $this->redirect('cerad_tourn_home');
            return $model;
            throw new \Exception('No person in cerad_tourn_person_update');
        }
        $personFed = $person->getFed($this->getFedRole());
 
        $personFedCertRef = $personFed->getCertReferee();
        
        // Simple model
        $model['person']    = $person;
        $model['fedKey']    = $personFed->getFedKey();
        $model['orgKey']    = $personFed->getOrgKey();
        $model['badge']     = $personFedCertRef->getBadgeUser();
        $model['upgrading'] = $personFedCertRef->getUpgrading();
        
        // Value object, just flatten for now
        $name = $person->getName();
        $model['personName']      = $name;
        $model['personNameFull']  = $name->full;
        $model['personNameFirst'] = $name->first;
        $model['personNameLast']  = $name->last;
         
        $model['personEmail'] = $person->getEmail();
        $model['personPhone'] = $person->getPhone();
        
       // Value object, just flatten for now
        $address = $person->getAddress();
        $model['personAddressCity']  = $address->city;
        $model['personAddressState'] = $address->state;
        
        return $model;
    }
    /* ==========================================
     * Hand crafted form
     */
    public function createModelForm($model)
    {
        $fedRole = $this->getFedRole();
        
        // Service id's are not case sensitive
        $fedKeyTypeServiceId = sprintf('cerad_person.%s_id_Fake.form_type',      $fedRole);
        $orgKeyTypeServiceId = sprintf('cerad_person.%s_org_id.form_type',       $fedRole);
        $badgeTypeServiceId  = sprintf('cerad_person.%s_referee_badge.form_type',$fedRole);
   
        $fedKeyTypeService  = $this->get($fedKeyTypeServiceId);
        $orgKeyTypeService  = $this->get($orgKeyTypeServiceId);
        $badgeTypeService   = $this->get($badgeTypeServiceId);
        
        $formOptions = array(
          //'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
        $constraintOptions = array();
        
        $builder = $this->createFormBuilder($model,$formOptions);
        
        $builder->setAction($this->generateUrl($model['_route'],array('personId' => $model['person']->getId())));
        $builder->setMethod('POST');
         
        $builder->add('fedKey',$fedKeyTypeService, array(
            'required' => false,
            'disabled' => true,
        ));
        $builder->add('orgKey',$orgKeyTypeService, array(
            'required' => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
        )));
        $builder->add('badge',$badgeTypeService, array(
            'required' => true,
        ));
        $builder->add('upgrading','cerad_person_upgrading', array(
            'required' => false,
        ));
       
        $builder->add('personNameFull','text', array(
            'required' => true,
            'label'    => 'Full Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('personNameFirst','text', array(
            'required' => true,
            'label'    => 'First Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('personNameLast','text', array(
            'required' => true,
            'label'    => 'Last Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('personEmail','email', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
                new EmailConstraint   ($constraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
        $builder->add('personPhone','cerad_person_phone', array(
            'required' => false,
            'label'    => 'Cell Phone',
            'trim'     => true,
            'constraints' => array(
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('personAddressCity','text', array(
            'required' => false,
            'label'    => 'Home City',
            'trim'     => true,
            'constraints' => array(
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('personAddressState','cerad_person_state', array(
            'required' => false,
            'label'    => 'Home State',
        ));

        return $builder->getForm();
    }
}
