<?php
namespace Cerad\Bundle\TournsBundle\Controller;

//  Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    const SESSION_PERSON_PLAN_ID    = 'cerad_tourns_person_plan_id';
    const FLASHBAG_TYPE             = 'cerad_tourns';
    const FLASHBAG_ACCOUNT_CREATED  = 'cerad_tourn_account_created';
    
    protected function punt($request,$reason = null)
    {
        $flashBag = $request->getSession()->getFlashBag();
        
        $flashBag->add(self::FLASHBAG_TYPE,$reason);
        
        return $this->redirect($this->generateUrl('cerad_tourn_welcome'));
    }
    public function redirect($path,$params = array())
    {
        return parent::redirect($this->generateUrl($path,$params));
    }
    /* ==================================================
     * Short cuts for determining users
     */
    protected function hasRoleUser($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_USER');
    }
    protected function hasRoleAdmin($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_ADMIN');
    }
    protected function hasRoleAssignor($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_ASSIGNOR');
    }
}
?>
