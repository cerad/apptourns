<?php
namespace Cerad\Bundle\TournsBundle\Controller\PersonPlan;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Cerad\Bundle\TournBundle\FormType\DynamicFormType;

/* ========================================================
 */
class PersonPlanUpdateController extends MyBaseController
{   
    public function updateAction(Request $request, $personId, $slug)
    {   
        // Security
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');
        
        // The project
        $project = $this->getProject($slug);
        
        // The model
        $model1 = $this->createModel($project,$personId);
                      
        // The form
        $form = $this->createModelForm($model1);
        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
            // Maybe dispatch something to adjust form
            $model2 = $form->getData();
            
            $model3 = $this->processModel($model2);
            
            // Notify email system
            // $person2 = $model2['person'];
            
            return $this->redirect('cerad_tourn_home');
        }

        // Template stuff
        $tplData = array();
        $tplData['msg'    ] = null; // $msg; from flash bag
        $tplData['form'   ] = $form->createView();
        
        $tplData['plan'   ] = $model1['plan'];
        $tplData['person' ] = $model1['person'];
        $tplData['project'] = $model1['project'];

        return $this->render('@CeradTourns\PersonPlan\Update\PersonPlanUpdateIndex.html.twig',$tplData);        
    }
    protected function createModel($project,$personId)
    {   
        // Should always have a valid personId
        $personRepo = $this->get('cerad_person.person_repository');
        $person = $personRepo->find($personId);
       
        if (!$person) throw new \Exception('Person not found in lan update');
        
        $plan = $person->getPlan($project->getId());
        $plan->mergeBasicProps($project->getBasic());
        
        // Pack it up
        $model = array();
        $model['plan'  ]  = $plan;
        $model['basic' ]  = $plan->getBasic();
        $model['person']  = $person;
        $model['project'] = $project;
        
        return $model;
    }
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
        
/* ==============================
 * Does not quit work
        $builder->add('notes','textarea', array(
            'label' => false,
            'required' => false,
            'attr' => array('cols' => 50, 'rows' => 5)
        ));
        */
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
                
        // And save
        $personRepo->save($person);
        $personRepo->commit();
       
        return $model;
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
