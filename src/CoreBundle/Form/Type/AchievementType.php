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
        $builder->add('orderNumber');
        $builder->add('icon');
        $builder->add('shortdesc');
        $builder->add('longdesc');
        $builder->add('favorite');
        $builder->add('images', CollectionType::class, [
            'entry_type' => \Symfony\Component\Form\Extension\Core\Type\TextType::class,
            'allow_add' => true,
            'error_bubbling' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CoreBundle\Entity\Achievement',
            'csrf_protection' => false
        ]);
    }
}
