<?php
namespace Cerad\Bundle\TournsBundle\TwigExtension;

class TournsExtension extends \Twig_Extension
{
    protected $env;
    protected $showConfig;
    
    public function getName()
    {
        return 'cerad_tourns_extension';
    }
    public function __construct($showConfigs)
    {   
        $configName = defined('CERAD_TOURN_SHOW_CONFIG') ? CERAD_TOURN_SHOW_CONFIG : 'default';

        if (!isset($showConfigs[$configName]))
        {
            throw new \Exception('Undefined show config : ' . $configName);
        }
        $this->showConfig = $showConfigs[$configName];
    }
    public function initRuntime(\Twig_Environment $env)
    {
        parent::initRuntime($env);
        $this->env = $env;
    }
    protected function escape($string)
    {
        return twig_escape_filter($this->env,$string);
    }
    public function getFunctions()
    {
        return array(            
            'cerad_tourn_show' => new \Twig_Function_Method($this, 'show'),
            
            'cerad_tourn_get_referer' => new \Twig_Function_Method($this, 'getReferer'),      
        );
    }
    public function getReferer()
    {
        // Should be a better way than to access $_SERVER directly.
        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if (!$url) return null;
        
        $parts = parse_url($url);
        
        $referer = sprintf('%s://%s/',$parts['scheme'],$parts['host']);
      //die($referer);
        return $referer;
        
    }
    public function show($param)
    {
        if (!isset($this->showConfig[$param]))
        {
            throw new \Exception('Undefined show config param : ' . $param);
        }
        return $this->showConfig[$param];
    }
}
?>
