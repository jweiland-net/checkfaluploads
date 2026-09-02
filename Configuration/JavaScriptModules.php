<?php

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        '@jweiland/checkfaluploads/' => [
            'path' => 'EXT:checkfaluploads/Resources/Public/JavaScript/',
        ],
    ],
];
