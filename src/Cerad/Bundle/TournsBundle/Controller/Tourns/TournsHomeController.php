<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsHomeController extends MyBaseController
{
    public function homeAction(Request $request)
    {   
        // Must be signed in
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');
        
        // Need to make sure the account has a person
        $person = $this->getUserPerson(false);
        
        // Is this the first time since the account was created?
        $msgs = $request->getSession()->getFlashBag()->get(self::FLASHBAG_ACCOUNT_CREATED);
        if (count($msgs))
        {
            $person = $this->getUserPerson();
            return $this->redirect('cerad_tourn_person_update',array('personId' => $person->getId()));
        }
        
        $projects = $this->getProjects();
        
        $person = $this->getUserPerson();
        
        $tplData = array();
        $tplData['person']   = $person;
        $tplData['projects'] = $projects;
        
        return $this->render('@CeradTourns/Tourns/Home/TournsHomeIndex.html.twig',$tplData);        
    }
}
?>
