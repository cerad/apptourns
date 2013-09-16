<?php
namespace Cerad\Bundle\TournsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class HomeController extends BaseController
{
    public function homeAction(Request $request)
    {
        // Must be signed in
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');
 
        // Is this the first time since the account was created?
        $msgs = $request->getSession()->getFlashBag()->get(self::FLASHBAG_ACCOUNT_CREATED);
        if (count($msgs))
        {
            return $this->redirect('cerad_tourn_person_edit');
        }
        // Grab the projects
        $projectRepo = $this->get('cerad_tourns.project.repository');
        $projects = $projectRepo->findAll();
              
        $tplData = array();
        $tplData['projects'] = $projects;
        
        return $this->render('@CeradTourns/Home/index.html.twig',$tplData);        
    }
}
?>
