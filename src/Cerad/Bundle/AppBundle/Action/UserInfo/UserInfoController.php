<?php

namespace Cerad\Bundle\AppBundle\Action\UserInfo;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class UserInfoController
{
    protected $router;
    protected $templating;
    
    public function setRouter    (RouterInterface $router)     { $this->router     = $router;     }
    public function setTemplating(EngineInterface $templating) { $this->templating = $templating; }
    
    /* =========================================================================
     * This is an example of a sub request
     * All we get is _format,_locale,_controller
     * Possible to get the parent request or to add parameters?
     */
    public function action(Request $request, $user)
    {   
        if (!$user)
        {
            return $this->templating->renderResponse($request->attributes->get('_templateGuest'));
        }
        if (!$user) die('No user');
        else die('Got user');
        
        $props = $request->attributes->all();
        foreach($props as $key => $prop)
        {
            if (is_object($prop)) $prop = get_class($prop);
            if (!is_array($prop)) echo sprintf("Prop %s: %s<br>",$key,$prop);
        }
        die('UserInfo.action');
        
        $tplData = array();
        $tplData['projects'] = $model->getProjects();
        
        $tplName = $request->attributes->get('_template');
        return $this->templating->renderResponse($tplName,$tplData);
    }    
}
