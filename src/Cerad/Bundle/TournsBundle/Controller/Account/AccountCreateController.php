<?php
namespace Cerad\Bundle\TournsBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use FOS\UserBundle\FOSUserEvents;
use Cerad\Bundle\UserBundle\Event\UserEvent;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameAndEmailUniqueConstraint;

use Symfony\Component\Validator\Constraints\Email     as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

class AccountCreateController extends MyBaseController
{
    public function createFormAction(Request $request)
    {
        // The model
        $model = $this->createModel();
        
        // The form
        $form = $this->createModelForm($model);
        
        $tplData = array();
        $tplData['form'] = $form->createView();
        
        return $this->render('@CeradTourns/Account/Create/AccountCreateForm.html.twig',$tplData);   
    }

    public function createAction(Request $request)
    {
        // If already signed in then no need to make an account
        if ($this->hasRoleUser()) return $this->redirect('cerad_tourn_home');
            
        // The model
        $model1 = $this->createModel();
        
        // This will let janrain have a shot at it
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, new UserEvent($model1['user'], $request));
         
        // Simple custom form
        $form = $this->createModelForm($model1);
        
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            /* =====================================
             * Just to follow the FOSUser pattern
             * The event is poorly named
             * Should be REGISTRATION_SUBMITTED or something
             */
          //$formEvent = new FormEvent($form, $request);
          //$dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $formEvent);

            $model2 = $form->getData();
            
            $model3 = $this->processModel($model2);
            
            // If all went well then user and person were created and persisted
            $response = null; //$formEvent->getResponse();
            if (!$response) $response = $this->redirect('cerad_tourn_home');
            
            // This will log the user in
            // If I had the service running
          //$dispatcher->dispatch(
          //        FOSUserEvents::REGISTRATION_COMPLETED, 
          //        new FilterUserResponseEvent($model['user'], $request, $response)
          //);
            
            // Flag as just having created an account
            $user = $model3['user'];
            $request->getSession()->getFlashBag()->add(self::FLASHBAG_ACCOUNT_CREATED,$user->getUsername());;

            // Log the user in
            $this->loginUser($request,$user);
            
            // And done
            return $response;
        }        
        
        $tplData = array();
        $tplData['form'] = $form->createView();
        
        return $this->render('@CeradTourns/Account/Create/AccountCreateIndex.html.twig',$tplData);   
    }  
    protected function processModel($model)
    {
        // Unpack
        $user      = $model['user'    ];
        $name      = $model['name'    ];
        $fedId     = $model['fedId'   ];
        $fedRoleId = $model['fedRoleId' ];
        $email     = $model['email'   ];
        $password  = $model['password'];
 
        // If they left it blank
        if (!$fedId)
        {
       
            $fedIdTransformerServiceId = sprintf('cerad_person.%s_id_fake.data_transformer',$fedRoleId);

            $fedIdTransformer = $this->get($fedIdTransformerServiceId);
            
            $fedId = $fedIdTransformer->reverseTransform('99');
            
            $model['fedId'] = $fedId;
        }
        /* =================================================
         * Process the person first
         */
        $personRepo = $this->get('cerad_person.person_repository');
        
        $personFed = $personRepo->findFed($fedId);
        
        if (!$personFed)
        {
            // Build a complete person record
            $person = $personRepo->createPerson();
            $person->getPersonPersonPrimary();
            
            // A value object
            $personNameVO = $person->createName();
            $personNameVO->full = $name;
            $person->setName($personNameVO);
            
            $person->setEmail($email);
           
            $personFed = $person->getFed($fedRoleId);
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
        $userManager = $this->get('cerad_user.user_manager');

        // Fill in the user
        $user->setEmail         ($email);
        $user->setUsername      ($email);
        $user->setAccountName   ($name);
        $user->setAccountEnabled(true);
        $user->setPasswordPlain ($password);
        $user->setPersonGuid    ($person->getGuid());
      //$user->setPersonFedId($personFed->getId());
        
        $model['user'] = $user;
        
        /* ===============================
         * And persist
         */
        $userManager->updateUser($user);
        
        $personRepo->save($person);
        $personRepo->commit();
        
        // Done
        return $model;
    }
    /* ==================================
     * Your basic dto model
     */
    protected function createModel()
    {
        // Do this here so janrain can add stuff
        $userManager = $this->get('cerad_user.user_manager');
        $user = $userManager->createUser();

        $model = array(
            'fedId'     => null,
            'fedRoleId' => self::FED_ROLE_ID,
            'user'      => $user,
            'name'      => null,
            'email'     => null,
            'password'  => null,
        );
        return $model;
    }
    /* ================================================
     * Create the form
     */
    protected function createModelForm($model)
    {
        
        /* ==================================================================
         * Make form type based on AYSOV or USSF
         * Be nice if the constraibnt type could come along with the form
         * Need to see how to inject the constraint options
         */
        $fedRoleId = $model['fedRoleId'];
        
        $fedIdTypeServiceId = sprintf('cerad_person.%s_id_Fake.form_type',$fedRoleId);

        $fedIdTypeService   = $this->get($fedIdTypeServiceId);
        
        /* ======================================================
         * Start building
         */
        $formOptions = array(
          //'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
        $constraintOptions = array(); // array('groups' => 'basic');
        
        $builder = $this->createFormBuilder($model,$formOptions);
        
        $builder->add('fedId',$fedIdTypeService, array(
            'required' => false,
        ));
        $builder->add('email','email', array(
            'required' => true,
            'label'    => 'Arbiter Email',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
                new EmailConstraint   ($constraintOptions),
                new UsernameAndEmailUniqueConstraint($constraintOptions),
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
        return $builder->getForm();
    }
}
