<?php
namespace Cerad\Bundle\TournsBundle\Controller\Tourns;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

class TournsHomeController extends MyBaseController
{
    public function homeAction(Request $request)
    {   
        $projects = $this->getProjects();
        
        $person = $this->getUserPerson();
        
        $tplData = array();
        $tplData['person']   = $person;
        $tplData['projects'] = $projects;
        
        return $this->render('@CeradTourns/Tourns/Home/TournsHomeIndex.html.twig',$tplData);        
    }
}
?>
