<?php

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'ctrl' => [
        'title' => 'Item',
        'label' => 'title',
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'images' => [
            'label' => 'Images',
            'config' => [
                'type' => 'file',
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'title, images',
        ],
    ],
];
