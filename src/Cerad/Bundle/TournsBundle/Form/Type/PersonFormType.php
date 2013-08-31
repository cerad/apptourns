<?php

namespace Cerad\Bundle\TournsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email as IsEmail;

class PersonFormType extends AbstractType
{
    public function getName()   { return 'cerad_tourns_person'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Cerad\Bundle\PersonBundle\Entity\Person',
          //'validation_groups'  => array('create'),
            'cascade_validation' => true,
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $notBlank = new NotBlank();
        
        $builder->add('name',      'text', array('label' => 'Full  Name*', 'constraints' => $notBlank, 'attr' => array('size' => 30)));
        $builder->add('firstName', 'text', array('label' => 'First Name*', 'constraints' => $notBlank));
        $builder->add('lastName',  'text', array('label' => 'Last  Name*', 'constraints' => $notBlank));
        $builder->add('nickName',  'text', array('label' => 'Nick  Name' , 'required' => false,));
        
        $builder->add('phone', 'cerad_person_phone',  array('required' => false,));
        
        $builder->add('email', 'cerad_person_email',  array(
            'required'    => true, 
            'constraints' => array($notBlank, new IsEmail()),
            'label' => 'Arbiter Email*'));
        
        $builder->add('city','text', array('label' => 'Home City',  'required' => false, 'attr' => array('size' => 30)));
        
        $builder->add('state', 'choice', array(
            'label'         => 'Home State',
            'required'      => false,
            'empty_value'   => false,
            'choices'       => $this->stateChoices,
        ));
        $builder->add('gender', 'choice', array(
            'label'         => 'Your Gender',
            'required'      => false,
            'choices'       => $this->genderChoices,
            'empty_value'   => false,
          //'expanded'      => true,
          //'multiple'      => false,
          //'attr' => array('class' => 'radio-medium'),
        ));        
        $builder->add('dob', 'birthday', array(
            'label'         => 'Date of Birth (mm/dd/yyyy)',
            'required'      => false,
            'widget'        => 'single_text',
            'input'         => 'datetime',
            'attr' => array('placeholder'   => 'mm/dd/yyyy'),
        ));        
    }
    protected $genderChoices = array ('M' => 'Male', 'F' => 'Female');
    
    protected $stateChoices  = array
    (
        'AL' => 'Alabama',
        'AR' => 'Arkansas',
        'GA' => 'Gerogia',
        'LA' => 'Louisiana',
        'MS' => 'Mississippi',
        'TN' => 'Tennessee',
        'ZZ' => 'See Notes',
    );
}
?>
