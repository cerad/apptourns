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
        
        // The model
        $model = $this->createModel($request);
        if ($model['_response']) return $model['_response'];
        
        // Is this the first time since the account was created?
        $msgs = $request->getSession()->getFlashBag()->get(self::FLASHBAG_ACCOUNT_CREATED);
        if (count($msgs))
        {
            $person = $this->getUserPerson();
            return $this->redirect('cerad_tourn_person_update',array('personId' => $person->getId()));
        }
        
        $tplData = array();
        $tplData['person']   = $model['person'];
        $tplData['projects'] = $model['projects'];
        
        return $this->render($model['_template'],$tplData);        
    }
    protected function createModel(Request $request)
    {
        $model = parent::createModel($request);
        
        $model['person']   = $this->getUserPerson();
        $model['projects'] = $this->getProjects();
        
        return $model;
    }
}
?>
