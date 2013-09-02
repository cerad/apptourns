<?php

namespace Cerad\Bundle\TournsBundle\Tests\Project;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectTest extends WebTestCase
{
    public function testRepo()
    {
        $client = static::createClient();
        
        $projectRepo = $client->getContainer()->get('cerad_tourns.project.repository');
        
        $project = $projectRepo->findByTourn('kicks');
        
        $this->assertEquals('USSF_AL_HFC_Kicks2013',$project->getId());
        
        $projects = $projectRepo->findAll();
        $this->assertTrue(count($projects) > 0);
        
    }
}
