<?php

namespace Art\JobtestBundle\Form;

use Art\JobtestBundle\Entity\Job;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('type')
            ->add('type', 'choice', array('choices' => Job::getTypes(), 'expanded' => true))
            ->add('category',null, ['label' => 'Category'])
            ->add('company',null, ['label' => 'Company'])
            // ->add('logo',null, ['label' => 'Company Logo'])
            ->add('file', 'file', array('label' => 'Company logo', 'required' => false))
            ->add('url',null, ['label' => 'Url'])
            ->add('position',null, ['label' => 'Position'])
            ->add('location',null, ['label' => 'Location'])
            ->add('description',null, ['label' => 'Description'])
            ->add('how_to_apply',null, ['label' => 'How to apply?'])
            // ->add('token',null, ['label' => 'Token'])
            ->add('is_public',null, ['label' => 'Is public?'])
            ->add('email',null, ['label' => 'Email'])
            // ->add('is_activated')
            // ->add('expires_at')
            // ->add('created_at')
            // ->add('updated_at')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Art\JobtestBundle\Entity\Job'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'art_jobtestbundle_job';
    }
}
