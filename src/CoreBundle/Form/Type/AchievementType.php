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
        $builder->add('orderNumber', ['empty_data' => 0]);
        $builder->add('icon');
        $builder->add('shortdesc', ['empty_data' => "Short description of the achievement"]);
        $builder->add('longdesc', ['empty_data' => "Complete description of the achievement"]);
        $builder->add('favorite', ['empty_data' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CoreBundle\Entity\Achievement',
            'csrf_protection' => false
        ]);
    }
}
