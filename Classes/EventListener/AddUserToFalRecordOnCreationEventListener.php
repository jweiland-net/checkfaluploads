<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\EventListener;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedToIndexEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Add the uid of the current user to the uploaded file
 */
class AddUserToFalRecordOnCreationEventListener
{
    public function __invoke(AfterFileAddedToIndexEvent $event): void
    {
        $fields = [];
        $typo3Request = $GLOBALS['TYPO3_REQUEST'];

        if (ApplicationType::fromRequest($typo3Request)->isBackend()) {
            $fields['cruser_id'] = (int)($this->getBackendUserAuthentication()->user['uid'] ?? 0);
        } elseif (ApplicationType::fromRequest($typo3Request)->isFrontend()) {
            $fields['fe_cruser_id'] = (int)($this->getTypoScriptFrontendController()->fe_user->user['uid'] ?? 0);
        } else {
            return;
        }

        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file');
        $connection->update(
            'sys_file',
            $fields,
            [
                'uid' => $event->getFileUid(),
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
