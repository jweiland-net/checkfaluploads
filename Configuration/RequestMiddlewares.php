<?php

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Checkfaluploads\Middleware\DragUploaderRightsCheckMiddleware;

return [
    'backend' => [
        'jweiland/checkfaluploads/drag-uploader-rights-check' => [
            'target' => DragUploaderRightsCheckMiddleware::class,
            'after' => [
                'typo3/cms-backend/backend-routing',
            ],
        ],
    ],
];
