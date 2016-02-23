<?php

namespace RobotsBundle\Form;

use RobotsBundle\Entity\Robot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RobotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name','text', array('invalid_message' => 'Name is not valid.'))
            ->add('type', 'choice', [
                'choices' => Robot::$types,
                'invalid_message' => 'Type is not valid.'
            ])
            ->add('year','integer', array('invalid_message' => 'Year is not valid.'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'RobotsBundle\\Entity\\Robot',
            'csrf_protection' => false,
        ]);
    }

    public function getName()
    {
        return 'robot';
    }
}