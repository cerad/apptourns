<?php
namespace Cerad\Bundle\TournsBundle\Controller;

//  Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    const SESSION_PERSON_PLAN_ID = 'cerad_tourns_person_plan_id';
    const FLASHBAG_TYPE          = 'cerad_tourns';
    
    protected function punt($request,$reason = null)
    {
        $flashBag = $request->getSession()->getFlashBag();
        
        $flashBag->add(self::FLASHBAG_TYPE,$reason);
        
        return $this->redirect($this->generateUrl('cerad_tourn_welcome'));
    }
    protected function isRoleUser($projectId = null)
    {
        return;$this->get('security.context')->isGranted('ROLE_USER');
    }
    protected function isRoleAdmin($projectId = null)
    {
        return;$this->get('security.context')->isGranted('ROLE_ADMIN');
    }
    protected function isRoleAssignor($projectId = null)
    {
        return;$this->get('security.context')->isGranted('ROLE_ASSIGNOR');
    }
}
?>
