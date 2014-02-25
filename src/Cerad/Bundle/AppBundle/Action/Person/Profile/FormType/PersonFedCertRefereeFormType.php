<?php

namespace Cerad\Bundle\AppBundle\Action\Person\Profile\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonFedCertRefereeFormType extends AbstractType
{   
    public function getName()   { return 'cerad_person__person__person_fed_cert_referee_profile'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonFedCert',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('upgrading','cerad_person_upgrading');
        
        $subscriber = new PersonFedCertRefereeSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);

        return; if ($options);
        
    }
}
