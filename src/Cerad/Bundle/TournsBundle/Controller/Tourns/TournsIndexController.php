<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsIndexController extends MyBaseController
{
    public function indexAction(Request $request)
    {
        // This is just a hold over
        $request->getSession()->remove('project_slug');
        
        // No more slug stuff
        return $this->redirect('cerad_tourn_welcome');
        
        $this->setSessionProjectSlug($request);
        
        $slug = $this->getSessionProjectSlug($request);
        
        return $this->redirect('cerad_tourns_welcome', array('slug' => $slug));        
    }
}
?>
