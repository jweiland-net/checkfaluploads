<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\EventListener;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use JWeiland\Checkfaluploads\Traits\ApplicationContextTrait;
use JWeiland\Checkfaluploads\Traits\BackendUserAuthenticationTrait;
use JWeiland\Checkfaluploads\Traits\ConnectionPoolTrait;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Resource\Event\AfterFileUpdatedInIndexEvent;

/**
 * Add the uid of the current user to the uploaded file
 */
#[AsEventListener(
    identifier: 'checkfaluploads/add-user-to-fal-record-on-update',
)]
final readonly class AddUserToFalRecordOnUpdateEventListener
{
    use ApplicationContextTrait;
    use BackendUserAuthenticationTrait;
    use ConnectionPoolTrait;

    public function __construct(
        private Context $context,
        private ExtConf $extConf,
    ) {}

    public function __invoke(AfterFileUpdatedInIndexEvent $event): void
    {
        $fields = [];
        if ($this->isBackendRequest() && $this->extConf->isStoreBackendUploaderUserIdEnabled()) {
            $fields['cruser_id'] = (int)$this->getBackendUserAuthentication()->user['uid'];
        } elseif ($this->isFrontendRequest() && $this->extConf->isStoreFrontendUploaderUserIdEnabled()) {
            $fields['fe_cruser_id'] = $this->getFrontendUserId();
        } else {
            return;
        }

        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file');
        $connection->update(
            'sys_file',
            $fields,
            [
                'uid' => (int)$event->getRelevantProperties()['uid'],
            ],
        );
    }

    public function getFrontendUserId(): int
    {
        return $this->context->getPropertyFromAspect('frontend.user', 'id', '');
    }
}
