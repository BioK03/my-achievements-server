<?php

namespace CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchievementType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('orderNumber', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, ['empty_data' => 0]);
        $builder->add('icon');
        $builder->add('shortdesc', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['empty_data' => "Short description of the achievement"]);
        $builder->add('longdesc', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['empty_data' => "Complete description of the achievement"]);
        $builder->add('favorite', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['empty_data' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CoreBundle\Entity\Achievement',
            'csrf_protection' => false
        ]);
    }
}
