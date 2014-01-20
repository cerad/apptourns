<?php
namespace Cerad\Bundle\TournsBundle\Controller\AccountPassword;

use Cerad\Bundle\TournsBundle\Controller\BaseController as MyBaseController;


/* ==============================================================
 * Base class for the request/requested controllers
 * Handles the email stuff
 * TODO: Move to a service or listener?
 */
class AccountPasswordResetEmailController extends MyBaseController
{
    protected $tplEmailBody    = '@CeradTourns/AccountPassword/ResetEmail/AccountPasswordResetEmailBody.html.twig';
    protected $tplEmailSubject = '@CeradTourns/AccountPassword/ResetEmail/AccountPasswordResetEmailSubject.html.twig';
    
    protected function sendEmail($model)
    {
        $user = $model['user'];
        
        $emailModel   = $this->getEmailModel($user->getId());
        if ($emailModel['_response']) return $emailModel['_response'];
        
        $emailBody    = $emailModel['emailBody'];
        $emailSubject = $emailModel['emailSubject'];
        
        $fromName =  'Zayso Password Reset';
        $fromEmail = 'noreply@zayso.org';
        
        $adminName =  'Art Hundiak';
        $adminEmail = 'ahundiak@gmail.com';
        
        $userName  = $user->getAccountName();
        $userEmail = $user->getEmail();
       
        $message = \Swift_Message::newInstance();
        $message->setSubject($emailSubject);
        $message->setBody($emailBody);
        $message->setFrom(array($fromEmail  => $fromName ));
        $message->setBcc (array($adminEmail => $adminName));
        $message->setTo  (array($userEmail  => $userName ));

        $this->get('mailer')->send($message);
        
        return $model;
    }
    /* ==========================================================
     * Returns email subject and body based on templates
     */
    public function getEmailModel($userId)
    {
        if (!$userId) return array('_response' => $this->redirect('cerad_tourn_welcome'));
 
        $userManager = $this->get('cerad_user.user_manager');
        $user = $userManager->findUser($userId);
        
        if (!$user) return array('_response' => $this->redirect('cerad_tourn_welcome'));

        $userToken = $user->getPasswordResetToken();
        if (!$userToken) return array('_response' => $this->redirect('cerad_tourn_welcome'));
        
        $tplData = array();
        $tplData['user']      = $user;
        $tplData['userToken'] = $userToken;
        $tplData['prefix']    = 'ZaysoAdmin';
        
        $emailBody    = $this->renderView($this->tplEmailBody,   $tplData);
        $emailSubject = $this->renderView($this->tplEmailSubject,$tplData);
        
        $model = array();
        $model['emailBody']    = $emailBody;
        $model['emailSubject'] = $emailSubject; 
        
        return $model;
    }
}
?>
