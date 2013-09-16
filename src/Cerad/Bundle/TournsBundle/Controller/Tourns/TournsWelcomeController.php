<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsWelcomeController extends MyBaseController
{
    public function welcomeAction(Request $request)
    {
        if ($this->hasRoleUser()) return $this->redirect('cerad_tourn_home');

        $projects = $this->getProjects();
        
        $tplData = array();
        $tplData['projects'] = $projects;
        
        return $this->render('@CeradTourns/Tourns/Welcome/TournsWelcomeIndex.html.twig',$tplData);        
    }
}
?>
