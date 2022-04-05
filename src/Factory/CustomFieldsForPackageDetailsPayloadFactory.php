<?php

declare(strict_types=1);

namespace BitBag\ShopwareInPostPlugin\Factory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\CustomField\CustomFieldTypes;

final class CustomFieldsForPackageDetailsPayloadFactory implements CustomFieldsForPackageDetailsPayloadFactoryInterface
{
    private EntityRepositoryInterface $customFieldSetRepository;

    public function __construct(EntityRepositoryInterface $customFieldSetRepository)
    {
        $this->customFieldSetRepository = $customFieldSetRepository;
    }

    public function create(): array
    {
        $customFieldPrefix = CustomFieldsForPackageDetailsPayloadFactoryInterface::PACKAGE_DETAILS_KEY;

        return [
            'name' => 'Package details',
            'config' => [
                'label' => [
                    'en-GB' => 'Package details',
                ],
            ],
            'customFields' => [
                [
                    'name' => $customFieldPrefix . '_insurance',
                    'label' => 'Insurance value (you can leave empty)',
                    'type' => CustomFieldTypes::INT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Insurance value (you can leave empty)',
                        ],
                    ],
                ],
                [
                    'name' => $customFieldPrefix . '_height',
                    'label' => 'Height',
                    'type' => CustomFieldTypes::INT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Height',
                        ],
                    ],
                ],
                [
                    'name' => $customFieldPrefix . '_width',
                    'label' => 'Width',
                    'type' => CustomFieldTypes::INT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Width',
                        ],
                    ],
                ],
                [
                    'name' => $customFieldPrefix . '_depth',
                    'label' => 'Depth',
                    'type' => CustomFieldTypes::INT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Depth',
                        ],
                    ],
                ],
            ],
            'relations' => [[
                'entityName' => 'order',
            ]],
        ];
    }
}
