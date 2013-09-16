<?php
namespace Cerad\Bundle\TournsBundle\Controller\AccountPassword;

use Cerad\Bundle\TournsBundle\Controller\AccountPassword\AccountPasswordResetEmailController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameOrEmailExistsConstraint;

class AccountPasswordResetRequestController extends MyBaseController
{
    public function requestFormAction(Request $request)
    {
        $model = $this->getModel($request);
        
        $form = $this->getModelForm($model);
        
        $tplData = array();
        $tplData['form'] = $form->createView();
        
        return $this->render('@CeradTourns/AccountPassword/ResetRequest/AccountPasswordResetRequestForm.html.twig',$tplData);      
    }
    public function requestAction(Request $request)
    {
        $model = $this->getModel($request);
        
        $form = $this->getModelForm($model);
        
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model1 = $form->getData();
            
            $model2 = $this->processModel($model1);
            
            $model3 = $this->sendEmail($model2);
            
            $user = $model3['user'];
            
            return $this->redirect('cerad_tourn_account_password_reset_requested',array('id' => $user->getId()));
        }
        
        // Render
        $tplData = array();
        $tplData['form'] = $form->createView();
        
        return $this->render('@CeradTourns/AccountPassword/ResetRequest/AccountPasswordResetRequestIndex.html.twig',$tplData);      
    }
    protected function processModel($model)
    {
        $username = $model['username'];
        
        $userProvider = $this->get('cerad_user.user_provider');
        
        $user = $userProvider->loadUserByUsername($username);
        
        // Make a key 
        $token = rand(1000,9999);
        $user->setPasswordResetToken($token);
        
        $userManager = $userProvider->getUserManager();
        $userManager->updateUser($user);
        
        $model['user']  = $user;
        $model['token'] = $token;
        
        return $model;
    }
    protected function getModel(Request $request)
    {
        $authInfo = $this->getAuthenticationInfo($request);
        $model = array('username' => $authInfo['lastUsername']);
        return $model;
    }
    protected function getModelForm($model)
    {
        $builder = $this->createFormBuilder($model);

        $builder->add('username','text', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new UsernameOrEmailExistsConstraint(),
            ),
            'attr' => array('size' => 30),
         ));
        return $builder->getForm();
    }
}
?>
