<?php
namespace Cerad\Bundle\TournsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BaseController extends Controller
{
    const FED_ROLE_ID = 'USSFC';
    
    const FLASHBAG_TYPE             = 'cerad_tourn';
    const FLASHBAG_ACCOUNT_CREATED  = 'cerad_tourn_account_created';
    
    protected function punt($request,$reason = null)
    {
        $flashBag = $request->getSession()->getFlashBag();
        
        $flashBag->add(self::FLASHBAG_TYPE,$reason);
        
        return $this->redirect($this->generateUrl('cerad_tourn_welcome'));
    }
    public function redirect($path,$params = array())
    {
        return parent::redirect($this->generateUrl($path,$params));
    }
    /* ==================================================
     * Short cuts for determining users
     */
    protected function hasRoleUser($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_USER');
    }
    protected function hasRoleAdmin($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_ADMIN');
    }
    protected function hasRoleAssignor($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_ASSIGNOR');
    }
    /* ===================================================
     * This is similiar to what the authentication listener does on success
     * This should me moved to some sort of user service
     */
    public function loginUser(Request $request, UserInterface $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $securityContext = $this->get('security.context');
        
        $securityContext->setToken($token);
        
        $session = $request->getSession();
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(SecurityContextInterface::LAST_USERNAME);

        /* ============================================================
         * Lots of other good stuff
         * AbstractAuthenticationListener
         */
        return;
        
        if (null !== $this->dispatcher) {
            $loginEvent = new InteractiveLoginEvent($request, $token);
            $this->dispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
        }

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Success Handler did not return a Response.');
        }

        if (null !== $this->rememberMeServices) {
            $this->rememberMeServices->loginSuccess($request, $response, $token);
        }

    }

    /* =================================================
     * Shared between login controllers
     */
    protected function getAuthenticationInfo(Request $request)
    {
        $error = null;
        
        // Check request for error
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) 
        {
            $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        }
        // Then look in session
        $session = $request->getSession();
        if (!$session)
        {
            $info['lastUsername'] = null;
            $info['error'] = $error ? $error->getMessage() : null;
            return $info;
        }
        
        // Pull user name
        $info['lastUsername'] = $session ? $session->get(SecurityContextInterface::LAST_USERNAME) : null;
        
        // Check for error in context
        if (!$error && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) 
        {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove      (SecurityContextInterface::AUTHENTICATION_ERROR);
       }
       $info['error'] = $error ? $error->getMessage() : null;
       return $info; 
    }
    /* ==============================================================
     * Get the currently signed in user's person
     */
    protected function getUserPerson()
    {
        $user = $this->getUser();
        $personId = $user->getPersonId();
        if (!$personId) return null;
        
        $personRepo = $this->get('cerad_person.person_repository');
        $person = $personRepo->find($personId);
        return $person;
        
    }
    /* ==============================================================
     * Project Handling
     */
    protected function getProjects($status = 'Active')
    {
        $projectRepo = $this->get('cerad_project.project_repository');
        return $projectRepo->findAllByStatus($status);   
    }
    protected function getProject($slug)
    {
        $projectRepo = $this->get('cerad_project.project_repository');
        $project = $projectRepo->findBySlug($slug);
        if ($project) return $project;
            
        throw new \Exception('No Project For: ' . $slug);
    }
}
?>
