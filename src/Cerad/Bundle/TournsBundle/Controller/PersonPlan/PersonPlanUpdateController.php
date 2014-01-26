<?php
namespace Cerad\Bundle\TournsBundle\Controller\PersonPlan;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Cerad\Bundle\TournsBundle\FormType\DynamicFormType;

/* ========================================================
 */
class PersonPlanUpdateController extends MyBaseController
{   
    public function updateAction(Request $request)
    {   
        // Security
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');
        
        // The model
        $model = $this->createModel($request);
        if ($model['_response']) return  $model['_response'];
        
        // The form
        $form = $this->createModelForm($model);
        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
            // Maybe dispatch something to adjust form
            $modelPosted = $form->getData();
            
            $modelProcessed = $this->processModel($modelPosted);
            
            $this->sendEmail($modelProcessed);
            
            return $this->redirect('cerad_tourn_home');
        }

        // Template stuff
        $tplData = array();
        $tplData['msg'    ] = null; // $msg; from flash bag
        $tplData['form'   ] = $form->createView();
        
        $tplData['plan'   ] = $model['plan'];
        $tplData['person' ] = $model['person'];
        $tplData['project'] = $model['project'];

        return $this->render($model['_template'],$tplData);        
    }
    protected function createModel(Request $request)
    {   
        // Init
        $model = parent::createModel($request);
        
        // The project
        $slug = $request->get('slug');
        $project = $this->getProject($slug);
        if (!$project)
        {
            $model['_response'] = $this->redirect('cerad_tourn_home');
            return $model;
        }

        // Should always have a valid personId
        $personRepo = $this->get('cerad_person.person_repository');
        $personId = $request->get('personId');
        $person = $personRepo->find($personId);
       
        if (!$person) 
        {
            $model['_response'] = $this->redirect('cerad_tourn_home');
            return $model;
        }
        $plan = $person->getPlan($project->getKey());
        $plan->mergeBasicProps($project->getBasic());
        
        // Pack it up
        $model['plan'  ]  = $plan;
        $model['basic' ]  = $plan->getBasic();
        $model['person']  = $person;
        $model['project'] = $project;
        
        return $model;
    }
    /* ========================================================
     * Lots of majic here but hey it works
     */
    protected function createModelForm($model)
    {   
        $project = $model['project'];
        
        $basicType = new DynamicFormType('basic',$project->getBasic());
        
        $formOptions = array(
            'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
                
        $builder = $this->createFormBuilder($model,$formOptions);
        
        $builder->add('basic',$basicType, array('label' => false));
        
        return $builder->getForm();
    }
    /* ===============================================
     * Lot's of possible processing to do
     * All ends with a plan
     */
    protected function processModel($model)
    {
        $personRepo = $this->get('cerad_person.person_repository');
         
        // Unpack dto
        $plan   = $model['plan'];
        $basic  = $model['basic'];
        $person = $model['person'];
        
        $basic['notes'] = strip_tags($basic['notes']);
        
        $plan->setBasic($basic);
        $plan->setUpdatedOn();
        
        // And save
        $personRepo->save($person);
        $personRepo->commit();
       
        return $model;
    }
    protected function sendEmail($model)
    {   
        $project = $model['project'];
        $person  = $model['person'];
        $plan    = $model['plan'];
        
        $personFed = $person->getFed($project->getFedRole());
        
        $prefix = $project->getPrefix(); // OpenCup2013
        
        $assignor = $project->getAssignor();
        
        $assignorName  = $assignor['name'];
        $assignorEmail = $assignor['email'];
        
        $adminName =  'Art Hundiak';
        $adminEmail = 'ahundiak@gmail.com';
        
        $personName = $person->getName();
        
        $refereeName  = $personName->full;
        $refereeEmail = $person->getEmail();
        
        /* =================================================
         * Use templates for email subject and body
         */
        $tplData = array();
        $tplData['plan']        = $plan;
        $tplData['person']      = $person;
        
        $tplData['fed']         = $personFed;
        $tplData['certReferee'] = $personFed->getCertReferee();
        
        $tplData['project']  = $project;
        $tplData['assignor'] = $assignor;
        
        $subject = $this->renderView('@CeradTourns\PersonPlan\Update\PersonPlanUpdateEmailSubject.html.twig',$tplData);       
        $body    = $this->renderView('@CeradTourns\PersonPlan\Update\PersonPlanUpdateEmailBody.html.twig',   $tplData);
       
      //die(nl2br($body));
        
        // This goes to the assignor
        $message1 = \Swift_Message::newInstance();
        $message1->setSubject($subject);
        $message1->setBody($body);
        $message1->setFrom(array('admin@zayso.org' => $prefix));
        $message1->setBcc (array($adminEmail => $adminName));
        
        $message1->setTo     (array($assignorEmail => $assignorName));
        $message1->setReplyTo(array($refereeEmail  => $refereeName));

        $this->get('mailer')->send($message1);
        
        // This goes to the referee
        $message2 = \Swift_Message::newInstance();
        $message2->setSubject($subject);
        $message2->setBody($body);
        $message2->setFrom(array('admin@zayso.org' => $prefix));
      
        $message2->setTo     (array($refereeEmail  => $refereeName));
        $message2->setReplyTo(array($assignorEmail => $assignorName));

        $this->get('mailer')->send($message2);
        
        return $model;
    }
}
?>
