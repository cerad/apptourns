<?php

namespace Cerad\Bundle\AppBundle\Action\Person\Profile\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonAddressFormType extends AbstractType
{   
    public function getName() { return 'cerad_person__person__person_address_profile'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonAddress',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('city','text', array(
            'required' => false,
            'label'    => 'Home City',
            'trim'     => true,
            'attr'     => array('size' => 20),
        ));
        $builder->add('state','cerad_person_state', array(
            'required' => false,
            'label'    => 'Home State',
        ));
        return; if ($options);
    }
}
