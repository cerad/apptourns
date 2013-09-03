<?php
namespace Cerad\Bundle\TournsBundle\Controller;

//  Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class RegisterController extends Controller
{
    const SESSION_PLAN_ID = 'register_plan_id';
    
    protected function createDto($request,$project)
    {
        // Have a previous plan in session?
        if ($request->getSession()->has(self::SESSION_PLAN_ID))
        {
            $planId = $request->getSession()->get(self::SESSION_PLAN_ID);
            //die('Session plan ' . $planId);
        }
        // New person
        $personRepo = $this->get('cerad_person.repository');
        $person     = $personRepo->newPerson();
        
        // The plan
        $plan = $person->getPlan($project->getId());
        $plan->setPlanProperties($project->getPlanProperties());
        
        // Pack it up
        $dto = array(
            'person'    => $person,
            'plan'      => $plan,
            'badge'     => null,
            'ussfId'    => null,
            'orgId'     => null,
            'upgrading' => 'No',
        );
        return $dto;
    }
    protected function createFormBuilderDto($dto)
    {
      //$planType = 
        $personType    = $this->get('cerad_tourns.person.form_type');
        $ussfIdType    = $this->get('cerad_person.ussf_contractor_id.form_type');
        $orgIdType     = $this->get('cerad_person.ussf_org_state.form_type');
        $badgeType     = $this->get('cerad_person.ussf_referee_badge.form_type');
        $upgradingType = $this->get('cerad_person.ussf_referee_upgrading.form_type');
                
        $builder = $this->createFormBuilder($dto)
          //->add('plan',     $planType)
            ->add('person',   $personType)
            ->add('badge',    $badgeType)
            ->add('ussfId',   $ussfIdType)
            ->add('orgId',    $orgIdType)
            ->add('upgrading',$upgradingType)
          //->add('update', 'submit')
        ;
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
        echo sprintf("Person Plan %s %s\n",$person->getId(),$plan->getPerson()->getId());
        
        // And save
        $personRepo->persist($person);
        $personRepo->flush();
       
        return null;
    }

    public function registerAction(Request $request, $slug, $op = null)
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
            $plan = $dto['plan'];die('Plan ' . self::SESSION_PLAN_ID . ' ' . $plan->getId());
            $request->getSession()->set(self::SESSION_PLAN_ID, $plan->getId());
            
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
