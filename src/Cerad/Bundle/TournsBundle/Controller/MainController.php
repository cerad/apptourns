<?php
namespace Cerad\Bundle\TournsBundle\Controller;

use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class MainController extends Controller
{
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }
    public function welcomeAction(Request $request)
    {
        // Allow admin signin from this page
        $session = $request->getSession();
        
        // get the login error if there is one
        $error = null;
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) 
        {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } 
        else 
        {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove      (SecurityContext::AUTHENTICATION_ERROR);
        }
        $projectRepo = $this->get('cerad_tourns.project.repository');
        $projects = $projectRepo->findAll();
        
        $tplData = array();
        
        // Get rid of
        $tplData['login_error']         = $error;
        $tplData['login_csrf_token']    = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');
        $tplData['login_last_username'] = $session->get(SecurityContext::LAST_USERNAME);
        
        // The good stuff
      //$tplData['flashes']  = $request->getSession()->getFlashBag()->all();
        $tplData['projects'] = $projects;
        
        return $this->render('@CeradTourns\Welcome\index.html.twig',$tplData);        
    }
    public function registerAction(Request $request, $slug, $op = null)
    {
        // Get the project
        $projectRepo = $this->get('cerad_tourns.project.repository');
        $project = $projectRepo->findBySlug($slug);
        if (!$project) return $this->welcomeAction($request);
               
        /* ========================
         * Initialize Person
         */
        $personRepo = $this->get('cerad_person.repository');
        $person     = $personRepo->newPerson();
        
        $personType    = $this->get('cerad_tourns.person.form_type');
        $ussfIdType    = $this->get('cerad_person.ussf_contractor_id.form_type');
        $orgIdType     = $this->get('cerad_person.ussf_org_state.form_type');
        $badgeType     = $this->get('cerad_person.ussf_referee_badge.form_type');
        $upgradingType = $this->get('cerad_person.ussf_referee_upgrading.form_type');
        
        $dto = array(
            'person'    => $person,
            'badge'     => null,
            'ussfId'    => null,
            'orgId'     => null,
            'upgrading' => 'No',
        );
        
        $form = $this->createFormBuilder($dto)
            ->add('person',   $personType)
            ->add('badge',    $badgeType)
            ->add('ussfId',   $ussfIdType)
            ->add('orgId',    $orgIdType)
            ->add('upgrading',$upgradingType)
          //->add('update', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
            $dto = $form->getData();
            $plan = $this->processDto($dto); // $personRepo
            
          //$personRepo->persist($person);
          //$personRepo->flush();
            
        }
        
        // Template stuff
        $tplData = array();
        $tplData['msg'    ] = null; // $msg; from flash bag
        $tplData['form'   ] = $form->createView();
        $tplData['project'] = $project;

        return $this->render('CeradTournsBundle:Register:index.html.twig',$tplData);
        
    }
    /* ===============================================
     * Lot's of possible processing to do
     * All ends with a plan
     */
    protected function processDto($dto)
    {
        $personRepo = $this->get('cerad_person.repository');
         
        // Unpack dto
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
                
                // Could check certain fields for updates
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
        $cert->setUpgrading($badge);
        
        $org = $personFed->getOrgState();
        $org->setOrgId($orgId);
        
        $person->getPersonPersonPrimary();
        
        $personRepo->persist($person);
        $personRepo->flush();
       
        return null;
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
