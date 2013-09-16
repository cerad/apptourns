<?php
namespace Cerad\Bundle\TournsBundle\Controller\AccountPassword;

use Cerad\Bundle\TournsBundle\Controller\AccountPassword\AccountPasswordResetEmailController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints\EqualTo  as EqualToConstraint;
use Symfony\Component\Validator\Constraints\NotBlank as NotBlankConstraint;

class AccountPasswordResetRequestedController extends MyBaseController
{
    public function requestedAction(Request $request, $id = null, $token = null)
    {
        $userId = $id;
        
        $model = $this->getModel($userId,$token);
        if ($model instanceOf Response) return $model;
        
        $form  = $this->getModelForm($model);
                
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model1 = $form->getData();
            
            $model2 = $this->processModel($model1);
            
            // Log the user in
            $this->loginUser($request,$model2['user']);
             
            return $this->redirect('cerad_tourn_home');
        }
        
        // Pass on email information for testing
        $emailModel = $this->getEmailModel($userId);
        
        // Render
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['user'] = $model['user'];
        $tplData['userToken'] = $model['userToken'];
        
        $tplData['emailBody']    = $emailModel['emailBody'];
        $tplData['emailSubject'] = $emailModel['emailSubject'];
        
        return $this->render('@CeradTourns/AccountPassword/ResetRequested/AccountPasswordResetRequestedIndex.html.twig',$tplData);      
    }
    protected function fakeEmail($model)
    {
        
    }
    protected function processModel($model)
    {
        $user = $model['user'];
        
        $user->setPasswordResetToken(null);
        
        $user->setPasswordPlain($model['password']);
        
        $userManager = $this->get('cerad_user.user_manager');
        
        $userManager->updateUser($user);
                
        return $model;
    }
    protected function getModel($userId,$token)
    {
        if (!$userId) return $this->redirect('cerad_tourn_welcome');
 
        $userManager = $this->get('cerad_user.user_manager');
        $user = $userManager->findUser($userId);
        
        if (!$user) return $this->redirect('cerad_tourn_welcome');
 
        $userToken = $user->getPasswordResetToken();
        if (!$userToken) return $this->redirect('cerad_tourn_welcome');
        
        $model = array();
        $model['user']      = $user;
        $model['userToken'] = $userToken;
        $model['token']     = $token;
        $model['password']  = null;
        
        return $model;
    }
    protected function getModelForm($model)
    {
        $equalToConstraintOptions = array(
            'value'   => $model['userToken'],
            'message' => 'Invalid token value',
        );
        
        $builder = $this->createFormBuilder($model);

        $builder->add('token','text', array(
            'required' => true,
            'label'    => 'Password Reset Token (4 digits)',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint(),
                new EqualToConstraint($equalToConstraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
        $builder->add('password', 'repeated', array(
            'type'     => 'password',
            'label'    => 'Zayso Password',
            'required' => true,
            'attr'     => array('size' => 20),
            
            'invalid_message' => 'The password fields must match.',
            'constraints'     => new NotBlankConstraint(),
            'first_options'   => array('label' => 'New Password'),
            'second_options'  => array('label' => 'New Password(confirm)'),
            
            'first_name'  => 'pass1',
            'second_name' => 'pass2',
        ));
        return $builder->getForm();
    }
}
?>
