<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

class TournsUserInfoController extends MyBaseController
{
    public function renderAction(Request $request)
    {        
        $tplData = array();
        
        // Guest
        if (!$this->hasRoleUser())
        {
            return $this->render('@CeradTourns/Tourns/UserInfo/TournsGuestInfo.html.twig',$tplData);
        }
        
        // Pass user and main userPerson to the listing
        $user   = $this->getUser();
        $person = $this->getUserPerson(true);
        
        $personFed = $person->getFed(self::FED_ROLE_ID);
        
        $tplData['user']      = $user;
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
