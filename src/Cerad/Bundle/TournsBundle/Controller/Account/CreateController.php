<?php
namespace Cerad\Bundle\TournsBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints\Email     as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

use Cerad\Bundle\PersonBundle\Validator\Constraints\USSF\ContractorId as FedIdConstraint;
use Cerad\Bundle\TournsBundle\Controller\BaseController;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class CreateController extends BaseController
{
    /* ================================================
     * For creating a new account the slug is not relevant
     * If someone really cares about it then try stashing in a session
     * 
     * TODO: Might be kind of handy for determining the required fedId if any
     */
    public function createAction(Request $request, $slug = null)
    {
        // If already signed in then no need to make an account
        if ($this->hasRoleUser()) return $this->redirect('cerad_tourn_home');
            
        // The model
        $model = $this->createModel($request);
        
        // This will let janrain have a shot at it
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, new UserEvent($model['user'], $request));
         
        // Simple custom form
        $form = $this->createFormBuilderForModel($request, $model)->getForm();
        
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            /* =====================================
             * Just to follow the FOSUser pattern
             * The event is poorly named
             * Should be REGISTRATION_SUBMITTED or something
             */
            $formEvent = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $formEvent);

            $model = $form->getData();
            
            $model = $this->processModel($request,$model);
            
            // If all went well then user and person were created and persisted
            $response = $formEvent->getResponse();
            if (!$response) $response = $this->redirect('cerad_tourn_home');
            
            // This will log the user in
            $dispatcher->dispatch(
                    FOSUserEvents::REGISTRATION_COMPLETED, 
                    new FilterUserResponseEvent($model['user'], $request, $response)
            );
            
            // Flag as just having created an account
            $user = $model['user'];
            $request->getSession()->getFlashBag()->add(self::FLASHBAG_ACCOUNT_CREATED,$user->getUsername());;

            // And done
            return $response;
        }        
        
        $tplData = array();
        $tplData['form'] = $form->createView();
        
        return $this->render('@CeradTourns/Account/Create/index.html.twig',$tplData);   
    }  
    protected function processModel($request,$model)
    {
        // Unpack
        $user     = $model['user'    ];
        $name     = $model['name'    ];
        $fedId    = $model['fedId'   ];
        $email    = $model['email'   ];
        $password = $model['password'];
      //$social   = $model['social'  ];
 
        /* =================================================
         * Process the person first
         */
        $personRepo = $this->get('cerad_person.repository');
        
        $personFed = $personRepo->findFed($fedId);
        
        if (!$personFed)
        {
            // Build a complete person record
            $person = $personRepo->newPerson();
            $person->setName ($name);
            $person->setEmail($email);
            $person->getPersonPersonPrimary();
           
            $personFed = $person->getFedUSSFC();
            $personFed->setId($fedId);
        }
        else
        {
            // TODO: More security, check email etc
            $person = $personFed->getPerson();
            
            // If this person has an account then we need to use it as well
            // Or else two accounts pointing to the same person?
            
        }
        $model['person']    = $person;
        $model['personFed'] = $personFed;
        
        /* ==================================================
         * Now take care of the account
         * Already checked for duplicate emails/user names
         */
        $userManager = $this->get('cerad_account.user_manager');

      // Already made
      //$user = $userManager->createUser();
        
        $user->setUsername($email);
        $user->setEmail   ($email);
        $user->setName    ($name);
        $user->setPlainPassword($password);
        $user->setEnabled (true);
        $user->setPersonId($person->getId());
        
        $model['user'] = $user;
        
        /* ===============================
         * And persist
         */
        $userManager->updateUser($user);
        
        $personRepo->persist($person);
        $personRepo->flush();
        
        // Done
        return $model;
    }
    /* ==================================
     * Your basic dto model
     */
    protected function createModel($request)
    {
        // Do this here so janrain can add stuff
        $userManager = $this->get('cerad_account.user_manager');
        $user = $userManager->createUser();

        $model = array(
            'fedId'    => null,
            'user'     => $user,
            'name'     => null,
            'email'    => null,
            'password' => null,
        );
        return $model;
    }
    /* ================================================
     * Create the form
     */
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
    protected function sendEmail($request,$model)
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
