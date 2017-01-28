<?php

namespace CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TabType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('color');
        $builder->add('orderNumber', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, array('empty_data' => 0));
        $builder->add('icon');
        $builder->add('achievements', CollectionType::class, [
            'entry_type' => AchievementType::class,
            'allow_add' => true,
            'error_bubbling' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CoreBundle\Entity\Tab',
            'csrf_protection' => false
        ]);
    }
}
