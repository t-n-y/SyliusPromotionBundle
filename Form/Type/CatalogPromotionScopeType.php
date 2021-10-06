<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\PromotionBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Promotion\Model\CatalogPromotionScopeInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class CatalogPromotionScopeType extends AbstractResourceType
{
    private array $scopeTypes = [];

    private array $scopeConfigurationTypes;

    public function __construct(
        string $dataClass,
        array $validationGroups,
        iterable $scopeConfigurationTypes
    ) {
        parent::__construct($dataClass, $validationGroups ?? []);

        foreach ($scopeConfigurationTypes as $type => $formType) {
            $this->scopeConfigurationTypes[$type] = get_class($formType);
            $this->scopeTypes['sylius.form.catalog_promotion.scope.'.$type] = $type;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultScopeType = current($this->scopeTypes);
        $defaultScopeConfigurationType = $this->scopeConfigurationTypes[$defaultScopeType];

        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'sylius.ui.type',
                'choices' => $this->scopeTypes,
            ])
            ->add('configuration', $defaultScopeConfigurationType)
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event): void {
                /** @var CatalogPromotionScopeInterface $data */
                $data = $event->getData();
                $form = $event->getForm();

                if ($data === null) {
                    return;
                }

                $scopeConfigurationType = $this->scopeConfigurationTypes[$data->getType()];
                $form->add('configuration', $scopeConfigurationType);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event): void {
                /** @var CatalogPromotionScopeInterface $data */
                $data = $event->getData();
                $form = $event->getForm();

                if ($data === null) {
                    return;
                }

                $scopeConfigurationType = $this->scopeConfigurationTypes[$data['type']];
                $form->add('configuration', $scopeConfigurationType);
            })
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_catalog_promotion_scope';
    }
}
