<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsWelcomeController extends MyBaseController
{
    public function welcomeAction(Request $request)
    {
        if ($this->hasRoleUser()) return $this->redirect('cerad_tourn_home');
        
        // The model
        $model = $this->createModel($request);
        if ($model['_response']) return $model['_response'];
        
        $tplData = array();
        $tplData['projects'] = $model['projects'];
        
        return $this->render($model['_template'],$tplData);        
    }
    protected function createModel(Request $request)
    {
        $model = parent::createModel($request);
        
        $model['projects'] = $this->getProjects();
        
        return $model;
    }
}
?>
