<?php
namespace Cerad\Bundle\TournsBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Request;
/// Symfony\Component\Security\Core\SecurityContext;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LoginController extends Controller
{
    public function loginAction(Request $request)
    {
        // Majic to get any previous errors
        $authInfo = $this->get('cerad_account.authentication_information');
        $info = $authInfo->get($request);
        
        $model = array(
            'error'       => $info['error'],
            'username'    => $info['lastUsername'],
            'password'    => null,
            'remember_me' => true,
        );
        $form = $this->createForm($this->get('cerad_account.login.form_type'),$model);
        
        // Render
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['error'] = $model['error'];
        
        return $this->render('@CeradTourns/Account/Login/index.html.twig',$tplData);      
    }
    /* ================================================
     * In case the firewall is not configured correctly
     */
    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }
    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

}
?>
