<?php

namespace Cerad\Bundle\TournsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DynamicFormType extends AbstractType
{
    protected $name;
    protected $items;
    
    public function getName() { return $this->name; }
    
    public function __construct($name, $items)
    {
        $this->name  = $name;
        $this->items = $items;
    }
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
    //        'data_class' => 'Cerad\TournBundle\Entity\OfficialPlans'
        ));
    }
    // Two types of arrays
    protected function process($items)
    {
        $itemx = array();
        foreach($items as $name => $value)
        {
            if (is_integer($name)) 
            {
                
            }
        }
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $items = $this->items;
        
        foreach($items as $name => $item)
        {
            $isChoice = false;
           
            switch($item['type'])
            {
                case 'radio':
                    $isChoice = true;
                    $expanded = true;
                    $multiple = false;
                    $attr = array('class' => 'radio-medium');
                    break;
                
                case 'select':
                    $isChoice = true;
                    $expanded = false;
                    $multiple = false;
                    $attr = array();
                    break;
                
                case 'text':
                    
                    $attr = array();
                    
                    if (isset($item['size'])) $attr['size'] = $item['size'];
                    
                    $builder->add($name,'text',array(
                        'label'    => $item['label'],
                        'required' => false,
                        'attr'     => $attr,
                    ));
                    break;
                    
                case 'collection':
                    
                    $dynamicType = new DynamicFormType($name,$item['items']);
                    $builder->add($name,$dynamicType,array(
                        'label'    => false,
                        'required' => false,
                    ));
                    break;
            }
            if ($isChoice) 
            {
                $builder->add($name,'choice',array(
                    'label'       => $item['label'],
                    'required'    => false,
                    'empty_value' => false,
                    'choices'     => $item['choices'],
                    'expanded'    => $expanded,
                    'multiple'    => $multiple,
                    'attr'        => $attr,
                ));
            }
        }
        return;
        
        foreach($items as $name => $item)
        {
            switch($item['type'])
            {
                case 'radio':
                    $expanded = true;
                    $multiple = false;
                    $attr = array('class' => 'radio-medium');
                    break;
                
                case 'select':
                    $expanded = false;
                    $multiple = false;
                    $attr = array();
                    break;
            }
            
            $builder->add($name, 'choice', array(
                'label'       => $item['label'],
                'required'    => false,
                'empty_value' => false,
                'choices'     => $item['choices'],
                'expanded'    => $expanded,
                'multiple'    => $multiple,
                'attr'        => $attr,
            ));
        }
    }
}
?>
