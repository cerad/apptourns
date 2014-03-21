<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPersons\ListAdmin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class PersonsListController extends ActionController
{   
    // Needs refinement
    public function __construct($export)
    {
        $this->export = $export;
    }
    public function action(Request $request, PersonsListModel $model, $_format) //, FormInterface $form)
    {   
        //die('persons.list.controller');
        /*
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model->processRegistration();
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
            
            return $this->redirect('cerad_app__home');
        }
        */
        $project = $model->getProject();
        $persons = $model->getPersons();
        if ($_format == 'xlsx')
        {
          //$export = $this->get('cerad_person_admin__project_persons__export_xls');

            $this->export->generate($project,$persons);
            
            $outFileName = ucfirst($project->getSlug()) . date('Ymd-Hi') . '.xlsx';
        
            $response = new Response();
            $response->setContent($this->export->getBuffer());
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', "attachment; filename=\"$outFileName\"");
            
            return $response;
        }
        $tplData = array();
      //$tplData['form']    = $form->createView();
        $tplData['persons'] = $persons;
        $tplData['project'] = $project;
        
        $tplName = $request->attributes->get('_template');
        return $this->regularResponse($tplName, $tplData);

    }    
}
