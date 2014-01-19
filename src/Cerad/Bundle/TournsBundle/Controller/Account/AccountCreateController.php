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
    /* ========================================================
     * This gets called by the welcome template
     * No eays way to inject the template name?
     * render(route( instead of render(controller
     * Maybe create a fake route?
     */
    public function createFormAction(Request $request)
    {
        // The model
        $model = $this->createModel($request);
        if ($model['_response']) return $model['_response'];
        
        $model['_route'] = 'cerad_tourn_account_create';
        
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
        $model = $this->createModel($request);
        if ($model['_response']) return $model['_response'];
        
        // This will let janrain have a shot at it
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, new UserEvent($model['user'], $request));
         
        // Simple custom form
        $form = $this->createModelForm($model);
        
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

            $modelPosted = $form->getData();
            
            $modelProcessed = $this->processModel($modelPosted);
            
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
            $user = $modelProcessed['user'];
            $request->getSession()->getFlashBag()->add(self::FLASHBAG_ACCOUNT_CREATED,$user->getUsername());;

            // Log the user in
            $this->loginUser($request,$user);
            
            // And done
            return $response;
        }        
        
        $tplData = array();
        $tplData['form'] = $form->createView();
        
        return $this->render($model['_template'],$tplData);   
    }
    protected function processModel($model)
    {
        // Unpack
        $user      = $model['user'    ];
        $name      = $model['name'    ];
        $fedKey    = $model['fedKey'  ];
        $fedRole   = $model['fedRole' ];
        $email     = $model['email'   ];
        $password  = $model['password'];
 
        // If they left it blank
        if (!$fedKey)
        {
       
            $fedKeyTransformerServiceId = sprintf('cerad_person.%s_id_fake.data_transformer',$fedRole);

            $fedKeyTransformer = $this->get($fedKeyTransformerServiceId);
            
            $fedKey = $fedKeyTransformer->reverseTransform('11');
            
            $model['fedKey'] = $fedKey;
        }
        /* =================================================
         * Process the person first
         */
        $personRepo = $this->get('cerad_person.person_repository');
        
        $personFed = $personRepo->findFedByFedKey($fedKey);
        
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
           
            $personFed = $person->getFed($fedRole);
            $personFed->setFedKey($fedKey);
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
    protected function createModel(Request $request)
    {
        $model = parent::createModel($request);
        
        // Do this here so janrain can add stuff
        $userManager = $this->get('cerad_user.user_manager');
        $user = $userManager->createUser();

        $modelx = array(
            'fedKey'    => null,
            'fedRole'   => $this->getFedRole(),
            'user'      => $user,
            'name'      => null,
            'email'     => null,
            'password'  => null,
        );
        return array_merge($model,$modelx);
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
        $fedRole = $model['fedRole'];
        
        $fedKeyTypeServiceId = sprintf('cerad_person.%s_id_Fake.form_type',$fedRole);

        $fedKeyTypeService   = $this->get($fedKeyTypeServiceId);
        
        /* ======================================================
         * Start building
         */
        $formOptions = array(
          //'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
        $constraintOptions = array(); // array('groups' => 'basic');
        
        $builder = $this->createFormBuilder($model,$formOptions);
        $builder->setMethod('POST');
        $builder->setAction($this->generateUrl($model['_route']));
        
        $builder->add('fedKey',$fedKeyTypeService, array(
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
