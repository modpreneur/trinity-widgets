<?php
/**
 * This file is part of Trinity package.
 */

namespace Trinity\Bundle\WidgetsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trinity\Bundle\WidgetsBundle\Widget\WidgetManager;

/**
 * Class DashboardType
 * @package Trinity\Bundle\WidgetsBundle\Form
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
            ChoiceType::class,
            [
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'choices' => $this->widgetManager->getFlippedDashboardWidgets(),

            ]
        );

        $builder->add(
            'expandedWidgets',
            ChoiceType::class,
            [
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'data' => $this->widgetManager->getBigWidgets(),
                'choices' => $this->widgetManager->getFlippedDashboardWidgets(),
                'choice_label' => function () {
                    return ' ';
                },


            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'attr' => ['class' => 'button button-success'],
            ]
        );
        $builder->setAction($this->widgetManager->getCurrentUri());
    }


    /**
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => null,
            ]
        );
    }


    //@todo @RichardBures getName is remove from symfony 3, this works just in symfony 2
    /**
     * @return string
     */
    public function getName()
    {
        return 'trinity_widgets_bundle_dashboard_type';
    }
}
