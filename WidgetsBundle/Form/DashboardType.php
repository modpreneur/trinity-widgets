<?php

namespace Trinity\WidgetsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trinity\WidgetsBundle\Widget\WidgetManager;


/**
 * Class DashboardType
 * @package Trinity\WidgetsBundle\Form
 */
class DashboardType extends AbstractType
{

    /**
     * @var WidgetManager
     */
    private $widgetManager;


    /**
     * DashboardType constructor.
     * @param WidgetManager $widgetManager
     */
    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'widgets',
            'choice',
            [
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'choices' => $this->widgetManager->getDashboardWidgets(),
            ]
        );

        $builder->add(
            'submit',
            'submit',
            [
                'attr' => ['class' => 'button button-success'],
            ]
        );

        $builder->setAction($this->widgetManager->getCurrentUri());
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => null,
            ]
        );
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'trinity_widgets_bundle_dashboard_type';
    }
}
