<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsIndexController extends MyBaseController
{
    public function indexAction(Request $request)
    {
        $this->setSessionProjectSlug($request);
        
        $slug = $this->getSessionProjectSlug($request);
        
        return $this->redirect('cerad_tourns_welcome', array('slug' => $slug));        
    }
}
?>
