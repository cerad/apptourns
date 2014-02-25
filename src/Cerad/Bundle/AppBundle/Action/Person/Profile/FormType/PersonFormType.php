<?php

namespace Cerad\Bundle\AppBundle\Action\Person\Profile\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\Email     as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

class PersonFormType extends AbstractType
{   
    public function getName()   { return 'cerad_person__person__person_profile'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\Person',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email','email', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint(),
                new EmailConstraint   (),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('phone','cerad_person_phone', array(
            'required' => false,
            'label'    => 'Cell Phone',
            'trim'     => true,
            'constraints' => array(
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('name',   new PersonNameFormType());
        $builder->add('address',new PersonAddressFormType());
        return; if ($options);
    }
}
