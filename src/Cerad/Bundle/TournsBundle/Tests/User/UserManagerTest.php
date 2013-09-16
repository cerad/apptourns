<?php

namespace Cerad\Bundle\TournsBundle\Tests\Project;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class UserManagerTest extends WebTestCase
{
    public function testManager()
    {
        $client = static::createClient();
        
        $userManager = $client->getContainer()->get('cerad_account.user_manager');
        
        $this->assertTrue($userManager instanceOf UserManagerInterface);
        
        $user = $userManager->createUser();
        $this->assertTrue($user instanceOf UserInterface);
        
    }
}
