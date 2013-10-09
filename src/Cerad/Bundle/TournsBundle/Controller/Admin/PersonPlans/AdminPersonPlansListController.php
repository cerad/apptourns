<?php
namespace Cerad\Bundle\TournsBundle\Controller\Admin\PersonPlans;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

class AdminPersonPlansListController extends MyBaseController
{
    public function listAction(Request $request, $_format)
    {
        // Security
        if (!$this->hasRoleAdmin()) { return $this->redirect('cerad_tourn_welcome'); }
        
        $model = $this->createModel($request);
        if (isset($model['repsonse'])) return $model['response'];
                
        if ($_format == 'xls') 
        {
            $export = $this->get('cerad_tourn.officials.export.excel');
            $export->setTournMeta($tourn);

            $export->generate($officials);
            
            $outFileName = ucfirst($project) . date('Ymd-Hi') . '.xls';
        
            $response = new Response();
            $response->setContent($export->getBuffer());
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', "attachment; filename=\"$outFileName\"");
            
            return $response;
           
        }
        $tplData = array();
        $tplData['slug']    = $model['slug'];
        $tplData['project'] = $model['project'];
        $tplData['persons'] = $model['persons'];
        
        $tplName = $request->get('_template');
        return $this->render($tplName,$tplData);   
    }
    public function createModel(Request $request)
    {
        $slug = $request->get('slug');
        $project = $this->getProject($slug);
        
        $personRepo = $this->get('cerad_person.person_repository');
        $persons = $personRepo->query(array($project->getId()));
        
        $model = array();
        $model['slug']    = $slug;
        $model['project'] = $project;
        $model['persons'] = $persons;
        return $model;
    }
}
?>
