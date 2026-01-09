<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'frontend' => [
        'checkfaluploads/cleanup-uploads' => [
            'target' => \JWeiland\Checkfaluploads\Middleware\CheckFileUploadsMiddleware::class,
            'before' => [
                'typo3/cms-frontend/extbase',
            ],
        ],
    ],
];
