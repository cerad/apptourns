<?php

namespace Cerad\Bundle\AppBundle\Action\UserInfo;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\Controller;

class UserInfoController extends Controller
{    
    /* =========================================================================
     * This is an example of a sub request
     * The request is completely independent of the parent request
     */
    public function action(Request $request)
    {   
        // Different templates for different roles
        $tplRole = 'Guest';
        if ($this->isGranted('ROLE_USER'))  $tplRole = 'User';
        if ($this->isGranted('ROLE_ADMIN')) $tplRole = 'Admin';
        
        $tplName = str_replace('ROLE',$tplRole,$request->attributes->get('_template'));
   
        return $this->renderResponse($tplName);
    }    
}
