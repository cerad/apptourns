<?php

namespace Cerad\Bundle\AppBundle\Action\Person\Profile\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

class PersonNameFormType extends AbstractType
{   
    public function getName()   { return 'cerad_person__person__person_name_profile'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonName',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('full','text', array(
            'required' => true,
            'label'    => 'Full Name',
            'trim'     => true,
            'constraints' => array(new NotBlankConstraint()),
            'attr' => array('size' => 30),
        ));
        $builder->add('first','text', array(
            'required' => false,
            'label'    => 'First Name',
            'trim'     => true,
            'attr'     => array('size' => 20),
        ));
        $builder->add('last','text', array(
            'required' => false,
            'label'    => 'Last Name',
            'trim'     => true,
            'attr'     => array('size' => 20),
        ));
        return; if ($options);
    }
}
