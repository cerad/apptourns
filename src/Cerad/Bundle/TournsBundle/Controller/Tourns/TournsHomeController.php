<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsHomeController extends MyBaseController
{
    public function homeAction(Request $request)
    {   
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');
        
        $projects = $this->getProjects();
        
        $person = $this->getUserPerson();
        
        $tplData = array();
        $tplData['person']   = $person;
        $tplData['projects'] = $projects;
        
        return $this->render('@CeradTourns/Tourns/Home/TournsHomeIndex.html.twig',$tplData);        
    }
}
?>
