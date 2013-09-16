<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsWelcomeController extends MyBaseController
{
    public function welcomeAction(Request $request)
    {
        $this->setSessionProjectSlug($request);
        
        $projects = $this->getProjects();
        
        $tplData = array();
        $tplData['projects'] = $projects;
        
        return $this->render('@CeradTourns/Tourns/Welcome/TournsWelcomeIndex.html.twig',$tplData);        
    }
}
?>
