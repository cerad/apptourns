<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

class TournsUserInfoController extends MyBaseController
{
    public function renderAction(Request $request)
    {        
        $tplData = array();
        
        $project = $this->getProject($request);
        
        $tplData['project'] = $project;
        
        // Guest
        if (!$this->hasRoleUser())
        {
            return $this->render('@CeradTourns/Tourns/UserInfo/TournsGuestInfo.html.twig',$tplData);
        }
        
        // Pass user and main userPerson to the listing
        $user = $this->getUser();
        $personId = $user->getPersonId();
        $personRepo = $this->get('cerad_person.person_repository');
        $person = $personRepo->find($personId);
        
        if (!$person) 
        {
            $person = $personRepo->createPerson();
            $person->getPersonPersonPrimary();
        }
        $personFed = $person->getFed($project->getFedRoleId());
        
        $tplData['user']      = $this->getUser();
        $tplData['person']    = $person;
        $tplData['personFed'] = $personFed;

        // Regular user
        if (!$this->hasRoleAdmin())
        {
            return $this->render('@CeradTourns/Tourns/UserInfo/TournsUserInfo.html.twig',$tplData);
        }
        
        // Admin
        return $this->render('@CeradTourns/Tourns/UserInfo/TournsAdminInfo.html.twig',$tplData);
     }
}
