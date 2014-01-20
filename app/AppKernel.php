<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
          //new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            
            new Cerad\Bundle\UserBundle   \CeradUserBundle(),
            new Cerad\Bundle\PersonBundle \CeradPersonBundle(),
            new Cerad\Bundle\ProjectBundle\CeradProjectBundle(),
         
            // This should eventually get moved to cerad2
            // It's basically the app bundle
            new Cerad\Bundle\TournsBundle\CeradTournsBundle(),
            
            // This holds arbiter schedule processing stuff
            // Should be moved to cerad2 as well
            new Cerad\Bundle\ArbiterBundle\CeradArbiterBundle(),
            
            // Currently empty
            // Used when the other bundles are moved to cerad2
            new Cerad\Bundle\AppBundle\CeradAppBundle(),
            
            // Need this to update the feds to a new format
            // That stuff should have been in the persons bundle
            // new Cerad\Bundle\AppCeradBundle\CeradAppCeradBundle(),
            
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
