<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

class TournsUserInfoController extends MyBaseController
{
    protected $tplUser  = '@CeradTourns/Tourns/UserInfo/TournsUserInfo.html.twig';
    protected $tplAdmin = '@CeradTourns/Tourns/UserInfo/TournsAdminInfo.html.twig';
    protected $tplGuest = '@CeradTourns/Tourns/UserInfo/TournsGuestInfo.html.twig';
    
    /* ======================================================
     * Tweak this so I can create the model first
     */
    public function renderAction(Request $request)
    {        
        // Guest
        if (!$this->hasRoleUser()) return $this->render($this->tplGuest,array());
        
        // The model
        $model = $this->createModel($request);
        if ($model['_response']) return $model['_response'];
        
        // Templae data
        $tplData = array();
        $tplData['user']      = $model['user'];
        $tplData['person']    = $model['person'];
        $tplData['personFed'] = $model['personFed'];

        // Get the template
        $tpl = $this->hasRoleAdmin() ?  $this->tplAdmin : $this->tplUser;
        
        // Admin
        return $this->render($tpl,$tplData);
    }
    protected function createModel(Request $request)
    {
        $model = parent::createModel($request);
        
        $user      = $this->getUser();
        $person    = $this->getUserPerson(true);
        $personFed = $person->getFed($this->getFedRole());
        
        $model['user']      = $user;
        $model['person']    = $person;
        $model['personFed'] = $personFed;
        
        return $model;
    }
}
