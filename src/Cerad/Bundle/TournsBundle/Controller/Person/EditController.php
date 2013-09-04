<?php
namespace Cerad\Bundle\TournsBundle\Controller\Person;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournsBundle\Controller\BaseController;

use Cerad\Bundle\PersonBundle\Validator\Constraints\USSF\ContractorId as FedIdConstraint;

/* ========================================================
 * Person Editor
 */
class EditController extends BaseController
{   
    public function editAction(Request $request, $id = null)
    {
        // Document
        $personId = $id;
        
        // Security
        if (!$this->hasRoleUser())
        {
            return $this->redirect('cerad_tourn_welcome');
        }
        // Simple model
        $model = $this->createModel($request,$personId);
        
        // This could also be passed in
        $form = $this->createFormBuilder($model)->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
            // Maybe dispatch something to adjust form
            $model = $form->getData();
            
            
            $model = $this->processModel($model);
            
            // Maybe tell someone?
            // Be kind of nice if changing the person's name also changed the account name.
            //  
            // Back to itself for now
            return $this->redirect('cerad_tourn_person_edit',array('id' => $id));
        }
        
        // Template stuff
        $tplData = array();
        $tplData['form'   ] = $form->createView();
        $tplData['person' ] = $model['person'];

        return $this->render('@CeradTourns\Person\Edit\index.html.twig',$tplData);        
    }
    /* ===============================================
     * Not to bad
     */
    protected function processModel($model)
    {
        $personRepo = $this->get('cerad_person.repository');
         
        // Unpack dto
        $person    = $model['person'];
        $badge     = $model['badge'];
      //$fedId     = $model['fedId']; // Disabled
        $orgId     = $model['orgId'];
        $upgrading = $model['upgrading'];
                
        $personFed = $person->getFedUSSFC();
        
        $cert = $personFed->getCertReferee();
        $cert->setBadgex   ($badge);
        $cert->setUpgrading($upgrading);
        
        $org = $personFed->getOrgState();
        $org->setOrgId($orgId);
               
        // And save
        $personRepo->persist($person);
        $personRepo->flush();
       
        return $model;
    }

    /* ===============================================
     * Model is just the person
     */
    protected function createModel($request,$personId)
    {
        // Always want project
        $model = array();
        
        // Get the person
        $personRepo = $this->get('cerad_person.repository');
        $person = null;
        
        // If passed an id then use it
        if ($personId)
        {
            $personRepo = $this->get('cerad_person.repository');
            $person = $personRepo->find($personId);
        }
        
        // Use the account person
        if (!$person)
        {
            $user = $this->getUser();
            $personId = $user->getPersonId();
            $person = $personRepo->find($personId);
        }
        if (!$person)
        {
            throw new \Exception('No person in cerad_person_edit');
        }
        $personFed = $person->getFedUSSFC();
        $personOrg = $personFed->getOrgState();
        $personCertRef = $personFed->getCertReferee();
        
        // Simple model
        $model['person']    = $person;
        $model['fedId']     = $personFed->getId();
        $model['orgId']     = $personOrg->getOrgId();
        $model['badge']     = $personCertRef->getBadgex();
        $model['upgrading'] = 'No';
        
        return $model;
    }
    /* ==========================================
     * Hand crafted form
     */
    public function createFormBuilder($model = null, array $options = array())
    {
        $personType    = $this->get('cerad_tourns.person.form_type');
        $fedIdType     = $this->get('cerad_person.ussf_contractor_id_fake.form_type');
        $orgIdType     = $this->get('cerad_person.ussf_org_state.form_type');
        $badgeType     = $this->get('cerad_person.ussf_referee_badge.form_type');
        $upgradingType = $this->get('cerad_person.ussf_referee_upgrading.form_type');
        
        $formOptions = array(
            'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
        $constraintOptions = array('groups' => 'basic');
        
        $builder = parent::createFormBuilder($model,$formOptions);
        
        $builder
            ->add('person',$personType, array('label' => false))
                
            ->add('fedId',    $fedIdType, array(
               'disabled'    => true,
               'constraints' => array(
                   new FedIdConstraint($constraintOptions),
                ),
             ))
            ->add('badge',    $badgeType)
            ->add('orgId',    $orgIdType)
            ->add('upgrading',$upgradingType)
        ;
        return $builder;
    }     
}
?>
