<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\EventListener;

use JWeiland\Checkfaluploads\Traits\ApplicationContextTrait;
use JWeiland\Checkfaluploads\Traits\BackendUserAuthenticationTrait;
use JWeiland\Checkfaluploads\Traits\ConnectionPoolTrait;
use JWeiland\Checkfaluploads\Traits\TypoScriptFrontendControllerTrait;
use TYPO3\CMS\Core\Resource\Event\AfterFileUpdatedInIndexEvent;

/**
 * Add the uid of the current user to the uploaded file
 */
class AddUserToFalRecordOnUpdateEventListener
{
    use ApplicationContextTrait;
    use BackendUserAuthenticationTrait;
    use ConnectionPoolTrait;
    use TypoScriptFrontendControllerTrait;

    public function __invoke(AfterFileUpdatedInIndexEvent $event): void
    {
        $fields = [];

        if ($this->isBackendRequest()) {
            $fields['cruser_id'] = (int)$this->getBackendUserAuthentication()->user['uid'];
        } elseif ($this->isFrontendRequest()) {
            $fields['fe_cruser_id'] = (int)$this->getTypoScriptFrontendController()->fe_user->user['uid'];
        } else {
            return;
        }

        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file');
        $connection->update(
            'sys_file',
            $fields,
            [
                'uid' => (int)$event->getRelevantProperties()['uid'],
            ]
        );
    }
}
