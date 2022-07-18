<?php

declare(strict_types=1);

return [
    \JWeiland\Clubdirectory\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
        'properties' => [
            'lockToDomain' => [
                'fieldName' => 'lockToDomain'
            ],
        ],
    ],
];
