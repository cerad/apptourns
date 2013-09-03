<?php
namespace Cerad\Bundle\TournsBundle\Controller\Register;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints\Email     as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

use Cerad\Bundle\PersonBundle\Validator\Constraints\USSF\ContractorId as FedIdConstraint;

class RegisterStep1Controller extends RegisterBaseController
{
    public function registerAction(Request $request, $slug = null, $op = null, $project = null)
    {
        /* ===============================================
         * In principle we should always have input values
         */
        if (!$slug || !$op || !$project)
        {
            return $this->punt($request,'RegisterStep1: Missing slug or op or project.');
        }
        // Simple model
        $model = $this->createModel($request,$project);
        
        // Simple custom form
        $form = $this->createFormBuilderForModel($request,$model)->getForm();
        
        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
        }
        
        $tplData = array();
        $tplData['form']    = $form->createView();
        $tplData['project'] = $project;
        
        return $this->render('@CeradTourns/Register/Step1/index.html.twig',$tplData);   
    }    
    protected function createModel($request,$project)
    {
        $model = array(
            'fedId'    => null,
            'name'     => null,
            'email'    => null,
            'password' => null,
            'social'   => null, // Future
        );
        return $model;
    }
    protected function createFormBuilderForModel($request,$model)
    {
        $fedIdType = $this->get('cerad_person.ussf_contractor_id_fake.form_type');
         
        $formOptions = array(
            'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
        $constraintOptions = array('groups' => 'basic');
                
        $builder = $this->createFormBuilder($model,$formOptions);
        
        $builder->add('fedId',$fedIdType, array(
            'constraints' => array(
                new FedIdConstraint($constraintOptions),
            ),
        ));
        $builder->add('email','email', array(
            'required' => true,
            'label'    => 'Arbiter Email',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
                new EmailConstraint   ($constraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
         $builder->add('name','text', array(
            'required' => true,
            'label'    => 'Your Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('password', 'repeated', array(
            'type'     => 'password',
            'label'    => 'Zayso Password',
            'required' => true,
            'attr'     => array('size' => 20),
            
            'invalid_message' => 'The password fields must match.',
            'constraints'     => new NotBlankConstraint($constraintOptions),
            'first_options'   => array('label' => 'Zayso Password'),
            'second_options'  => array('label' => 'Zayso Password(confirm)'),
            
            'first_name'  => 'pass1',
            'second_name' => 'pass2',
        ));
        return $builder;
    }
    /* ===============================================
     * Lot's of possible processing to do
     * All ends with a plan
     */
    protected function processDto($dto)
    {
        $personRepo = $this->get('cerad_person.repository');
         
        // Unpack dto
        $plan      = $dto['plan'];
        $person    = $dto['person'];
        $badge     = $dto['badge'];
        $ussfId    = $dto['ussfId'];
        $orgId     = $dto['orgId'];
        $upgrading = $dto['upgrading'];
                
        if (strlen($ussfId) == 21)
        {
            $personFed = $personRepo->findFed($ussfId);
            if ($personFed)
            {
                // Have an existing record
                $person = $personFed->getPerson();
                
                // TODO: Add plan to it
                
            }
            else
            {
                // Okay because person is new
                $personFed = $person->getFedUSSFC();
                $personFed->setId($ussfId);
            }
        }
        $cert = $personFed->getCertReferee();
        $cert->setBadgex   ($badge);
        $cert->setUpgrading($upgrading);
        
        $org = $personFed->getOrgState();
        $org->setOrgId($orgId);
        
        $person->getPersonPersonPrimary();
        
        // Plan should take care of itself?
        // echo sprintf("Person Plan %s %s\n",$person->getId(),$plan->getPerson()->getId());
        
        // And save
        $personRepo->persist($person);
        $personRepo->flush();
       
        return null;
    }

    public function registerActionx(Request $request, $slug, $op = null)
    {
        // Get the project
        $projectRepo = $this->get('cerad_tourns.project.repository');
        $project = $projectRepo->findBySlug($slug);
        if (!$project) return $this->redirect($this->generateUrl('cerad_tourns_welcome'));
               
        // This could be passed in or pull from a dispatch?
        $dto = $this->createDto($request,$project);
                        
        // This could also be passed in
        $form = $this->createFormBuilderDto($dto)->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
            // Maybe dispatch something to adjust form
            $dto = $form->getData();
            
            // Handled with a dispatch
            $this->processDto($dto);
            
            // Send processedDto message to kick off email?
            
            // Store plan id in session
            //$plan = $dto['plan'];die('Plan ' . self::SESSION_PLAN_ID . ' ' . $plan->getId());
            //$request->getSession()->set(self::SESSION_PLAN_ID, $plan->getId());
            
            //return $this->redirect($this->generateUrl('cerad_tourns_project',array('slug' => $slug)));
        }
        
        // Template stuff
        $tplData = array();
        $tplData['msg'    ] = null; // $msg; from flash bag
        $tplData['form'   ] = $form->createView();
        $tplData['project'] = $project;

        return $this->render('CeradTournsBundle:Register:index.html.twig',$tplData);        
    }
    protected function sendRefereeEmail($tourn,$plans)
    {   
        $prefix = $tourn['prefix']; // OpenCup2013
        
        $assignorName  = $tourn['assignor']['name'];
        $assignorEmail = $tourn['assignor']['email'];
        
      //$assignorEmail = 'ahundiak@nasoa.org';
        
        $adminName =  'Art Hundiak';
        $adminEmail = 'ahundiak@gmail.com';
        
        $refereeName  = $plans->getPerson()->getFirstName() . ' ' . $plans->getPerson()->getLastName();
        $refereeEmail = $plans->getPerson()->getEmail();
        
        $tplData = $tourn;
        $tplData['plans'] = $plans; 
        $body = $this->renderView('CeradTournBundle:Tourn:email.txt.twig',$tplData);
    
        $subject = sprintf("[%s] Ref App %s",$prefix,$refereeName);
       
        // This goes to the assignor
        $message = \Swift_Message::newInstance();
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setFrom(array('admin@zayso.org' => $prefix));
        $message->setBcc (array($adminEmail => $adminName));
        
        $message->setTo     (array($assignorEmail  => $assignorName));
        $message->setReplyTo(array($refereeEmail   => $refereeName));

        $this->get('mailer')->send($message);
      //return;
        
        // This goes to the referee
        $message = \Swift_Message::newInstance();
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setFrom(array('admin@zayso.org' => $prefix));
      //$message->setBcc (array($adminEmail => $adminName));
        
        $message->setTo     (array($refereeEmail  => $refereeName));
        $message->setReplyTo(array($assignorEmail => $assignorName));

        $this->get('mailer')->send($message);
    }
}
?>
