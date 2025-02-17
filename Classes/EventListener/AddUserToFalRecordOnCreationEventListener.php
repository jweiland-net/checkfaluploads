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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedToIndexEvent;

/**
 * Add the uid of the current user to the uploaded file
 */
class AddUserToFalRecordOnCreationEventListener
{
    use ApplicationContextTrait;
    use BackendUserAuthenticationTrait;
    use ConnectionPoolTrait;
    use TypoScriptFrontendControllerTrait;

    public function __construct(private readonly Context $context) {}

    public function __invoke(AfterFileAddedToIndexEvent $event): void
    {
        $fields = [];

        if ($this->isBackendRequest()) {
            $fields['cruser_id'] = (int)($this->getBackendUserAuthentication()->user['uid'] ?? 0);
        } elseif ($this->isFrontendRequest()) {
            $fields['fe_cruser_id'] = $this->getFrontendUserId();
            debug($fields);
        } else {
            return;
        }

        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file');
        $connection->update(
            'sys_file',
            $fields,
            [
                'uid' => $event->getFileUid(),
            ],
        );
    }

    public function getFrontendUserId(): int
    {
        return $this->context->getPropertyFromAspect('frontend.user', 'uid', '');
    }
}
