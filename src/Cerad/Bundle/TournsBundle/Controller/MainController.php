<?php
namespace Cerad\Bundle\TournsBundle\Controller;

use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Cerad\TournBundle\Entity\OfficialPlans;
use Cerad\Bundle\CoreBundle\Entity\Person;
use Cerad\Bundle\CoreBundle\Entity\PersonCert;

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
        $error = null;
       
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) 
        {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } 
        else 
        {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove      (SecurityContext::AUTHENTICATION_ERROR);
        }
           
        $tplData = array();
        $tplData['login_error']         = $error;
        $tplData['login_csrf_token']    = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');
        $tplData['login_last_username'] = $session->get(SecurityContext::LAST_USERNAME);
        
        $tplData['tourns'] = $this->getParameter('cerad_tourns_tournaments');
        
        return $this->render('@CeradTourns\Welcome\index.html.twig',$tplData);        
    }
    public function registerAction(Request $request, $project, $op = null)
    {
        // Extract tourn information for project
        $tourns = $this->getParameter('cerad_tourns_tournaments');
        if (!isset($tourns[$project])) return $this->welcomeAction($request);
        $tourn = $tourns[$project];
        
        /* ========================
         * Initialize Person
         */
        $personRepo = $this->get('cerad_person.repository');

        $person     = $personRepo->newPerson();
        
        $personType    = $this->get('cerad_tourns.person.form_type');
        $ussfidType    = $this->get('cerad_person.ussf_contractor_id.form_type');
        $leagueType    = $this->get('cerad_person.ussf_league.form_type');
        $badgeType     = $this->get('cerad_person.ussf_referee_badge.form_type');
        $upgradingType = $this->get('cerad_person.ussf_referee_upgrading.form_type');
        
        $formData = array(
            'person'    => $person,
            'badge'     => null,
            'ussfid'    => null,
            'league'    => null,
            'upgrading' => 'No',
        );
        
        $form = $this->createFormBuilder($formData)
            ->add('person',   $personType)
            ->add('badge',    $badgeType)
            ->add('ussfid',   $ussfidType)
            ->add('league',   $leagueType)
            ->add('upgrading',$upgradingType)
          //->add('update', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid() && 1) 
        {             
            $formData = $form->getData();
            $plan = $this->processFormData($personRepo,$formData);
            
          //$personRepo->persist($person);
          //$personRepo->flush();
        }
        
        // Template stuff
        $tplData = $tourn;
        $tplData['msg']      = null; // $msg; from flash bag
        $tplData['form']     = $form->createView();
      //$tplData['official'] = $official;
        return $this->render('CeradTournsBundle:Register:index.html.twig',$tplData);
        
        /* ========================
         * Old stuff
         */
        $manager = $this->get('cerad_tourn.tourn_official.manager');
        $manager->setTournMeta($tourn);
        
        $official = null;
        
        $session = $request->getSession();
        $msg = $session->getFlash('tournMessage');
        
        // Start over if a new operation
        if ($op == 'new')
        {
            $session->set('tournOfficialId',null);
            return $this->redirect($this->generateUrl('cerad_tourns_project', array('project' => $project)));
        }
        
        // Load existing from session
        $id = $session->get('tournOfficialId');
        if ($id && ($id > 50)) // From changing formats
        {
            $official = $manager->loadOfficialForId($id);
            if ($official)
            {
                // Make sure not in different project or something
                $ok = true;
                foreach(array('season','sport','group','groupSub') as $name)
                {
                    if ($official[$name] != $tourn[$name]) $ok = false;
                }
                if (!$ok) $official = null;
            }
            if ($official)
            {
                $person = $official->getPerson();
                $cert   = $person->getCertRefereeUSSF();
            }
        }
        // Make a new one
        if (!$official)
        {
            $official = new OfficialPlans($tourn['plan']);
            foreach(array('season','sport','group','groupSub') as $name)
            {
                $official[$name] = $tourn[$name];
            }
            $person = new Person();
            $person->setGender(Person::GenderMale);
            
            $cert = PersonCert::createRefereeUSSF();
            $cert->setBadgex(PersonCert::BadgeGrade8);
            
            $person->addPlan($official);
            $person->addCert($cert);
        }
        $item = array(
            'person' => $person, 
            'cert'   => $cert,
            'plans'  => $official,
        );
        
        $formType = $this->get('cerad_tourn.signup.formtype');
        
        $formFactory = $this->container->get('form.factory');
        
        $form = $formFactory->create($formType,$item);
        
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);

            if ($form->isValid())
            {
                // Make badges match
                $cert->setBadge($cert->getBadgex());
                
                // Save it
                $manager->persist($official);
                $manager->persist($person);
                $manager->persist($cert);
                
                $manager->flush();
                
                // Email it
                $this->sendRefereeEmail($tourn,$official);
                
                // Tuck ID away in session
                $id = $official->getId();
                
                $session->set     ('tournOfficialId',$id);
                $session->setFlash('tournMessage',   'Application Submitted');
                
                return $this->redirect($this->generateUrl('cerad_tourn',array('project' => $project)));
            }
            else $msg = 'Form not valid';
        }
        $tplData = $tourn;
        $tplData['msg']      = $msg;
        $tplData['form']     = $form->createView();
        $tplData['official'] = $official;
        return $this->render('CeradTournBundle:Tourn:signup.html.twig',$tplData);
    }
    /* ===============================================
     * Lot's of possible processing to do
     * All ends with a plan
     */
    protected function processFormData($repo,$formData)
    {
        // Have a person identifier?
        $person = $formData['person'];
        $ussfid = $formData['ussfid'];
        
        if (strlen($ussfid) == 21)
        {
            $personIdentifier = $repo->findIdentifierByValue($ussfid);
            if ($personIdentifier)
            {
                // Have an existing record
                $person = $personIdentifier->getPerson();
                
                // Could check certain fields for updates
            }
            else
            {
                $personIdentifier = $person->newIdentifier();
                $personIdentifier->setSource('USSFC');
                $personIdentifier->setValue($ussfid);
                $person->addIdentifier($personIdentifier);
            }
        }
        $cert = $person->getCertUSSFReferee();
        $cert->setIdentifier($ussfid);
        $cert->setBadgex   ($formData['badge']); //die('Badge ' . $formData['badge']);
        $cert->setUpgrading($formData['upgrading']);
        
        $league = $person->getLeagueUSSFContractor();
        $league->setIdentifier($ussfid);
        $league->setLeague($formData['league']);
        
        $person->getPersonPersonPrimary();
        
        $repo->persist($person);
        $repo->flush();
       
        echo 'USSFID ' . $ussfid;
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
