<?php
namespace Cerad\Bundle\TournsBundle\Controller\Register;

//  Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RegisterBaseController extends Controller
{
    const SESSION_PLAN_ID = 'cerad_tourns_register_plan_id';
    const FLASHBAG_TYPE   = 'cerad_tourns_register';
    
    protected function punt($request,$reason = null)
    {
        $flashBag = $request->getSession()->getFlashBag();
        
        $flashBag->add(self::FLASHBAG_TYPE,$reason);
        
        return $this->redirect($this->generateUrl('cerad_tourns_welcome'));
    }
}
?>
