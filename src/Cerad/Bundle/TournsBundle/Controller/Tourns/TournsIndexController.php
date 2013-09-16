<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsIndexController extends MyBaseController
{
    public function indexAction(Request $request)
    {
        $request->getSession()->remove('project_slug');
        
        return $this->redirect('cerad_tourn_welcome');        
    }
}
?>
