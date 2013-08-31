<?php
namespace Cerad\Bundle\TournsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller
{
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }
    public function editAction(Request $request, $project, $id)
    {
        $tourns = $this->getParameter('tourns');
        if (!isset($tourns[$project])) return $this->welcomeAction($request);
        $tourn = $tourns[$project];

        $manager  = $this->get('cerad_tourn.tourn_official.manager');
        $manager->setTournMeta($tourn);

        $official = $manager->loadOfficialForId($id);
        $person   = $official->getPerson();
        $cert     = $person->getCertRefereeUSSF();
        
        $item = array(
            'person' => $person, 
            'cert'   => $cert,
            'plans'  => $official,
        );
        
        // Form stuff
        $formType = $this->get('cerad_tourn.official_edit.formtype');
        $form = $this->createForm($formType,$item);
        
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);

            if ($form->isValid())
            {
                $manager->flush();            
                return 
                    $this->redirect($this->generateUrl('cerad_tourn_admin_edit', 
                    array('project' => $project, 'id' => $id)));
            }
        }
        // Consider stashing official.id in session?
        
        // Render
        $tplData             = array();
        $tplData['form']     = $form->createView();
        $tplData['project']  = $project;
        $tplData['official'] = $official;
        return $this->render('CeradTournBundle:Tourn:edit.html.twig',$tplData);
        
    }
    public function listAction(Request $request, $project, $_format)
    {   
        $tourns = $this->getParameter('tourns');
        
        if (!isset($tourns[$project])) 
        {
            return $this->redirect($this->generateUrl('cerad_tourn_welcome'));
        }
        $tourn = $tourns[$project];
        
        $manager = $this->get('cerad_tourn.tourn_official.manager');
        $manager->setTournMeta($tourn);
        
        $officials = $manager->loadOfficials($tourn);
        
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
        
        $tplData['tourn']     = $tourn;
        $tplData['tourns']    = $tourns;
        
        $tplData['project']   = $project;
        $tplData['officials'] = $officials;
        
        return $this->render('CeradTournBundle:Tourn:list.html.twig',$tplData);        
    }
}
?>
