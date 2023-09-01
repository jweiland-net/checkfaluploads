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
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Event\AfterFileUpdatedInIndexEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Add the uid of the current user to the uploaded file
 */
class AddUserToFalRecordOnUpdateEventListener
{
    use ApplicationContextTrait;

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

    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
