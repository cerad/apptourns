<?php
namespace Cerad\Bundle\TournsBundle\Model;

class ProjectRepository
{
    protected $configs;
    
    public function __construct($configs)
    {
        $this->configs = $configs;
    }
    public function findBySlug($id)
    {
        if (!isset($this->configs[$id])) return null;
        
        $config = $this->configs[$id];
        
        $project = new Project($config);
        
        return $project;
    }
    public function findAll()
    {
        $projects = array();
        foreach($this->configs as $config)
        {
            $projects[] = new Project($config);
        }
        return $projects;
    }
}

?>
