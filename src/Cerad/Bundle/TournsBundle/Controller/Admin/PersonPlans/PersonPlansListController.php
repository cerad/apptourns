<?php
namespace Cerad\Bundle\TournsBundle\Controller\Admin\PersonPlans;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;

class PersonPlansListController extends MyBaseController
{
    public function listAction(Request $request, $_format)
    {
        // Security
        if (!$this->hasRoleAdmin()) { return $this->redirect('cerad_tourn_welcome'); }
        
        $model = $this->createModel($request);
        if ($model['_response']) return $model['_response'];
                
        if ($_format == 'xls') 
        {
            $export = $this->get('cerad_tourns.officials.export_xls');

            $export->generate($model['project'],$model['persons']);
            
            $outFileName = ucfirst($model['slug']) . date('Ymd-Hi') . '.xls';
        
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
        
        return $this->render($model['_template'],$tplData);   
    }
    public function createModel(Request $request)
    {
        $model = parent::createModel($request);
        
        $slug = $request->get('slug');
        $project = $this->getProject($slug);
        
        $personRepo = $this->get('cerad_person.person_repository');
        $persons = $personRepo->query(array($project->getId()));
        
        $model['slug']    = $slug;
        $model['project'] = $project;
        $model['persons'] = $persons;
        return $model;
    }
}
?>
