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
use TYPO3\CMS\Core\Resource\Event\AfterFileUpdatedInIndexEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Add the uid of the current user to the uploaded file
 */
class AddUserToFalRecordOnUpdateEventListener
{
    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    public function invoke(AfterFileUpdatedInIndexEvent $event): void
    {
        // Do nothing, if an UpgradeWizard of InstallTool was executed
        if (TYPO3_REQUESTTYPE === TYPO3_REQUESTTYPE_INSTALL) {
            return;
        }

        $fields = [];
        if (TYPO3_MODE === 'BE') {
            $fields['cruser_id'] = (int)$this->getBackendUserAuthentication()->user['uid'];
        } elseif (TYPO3_MODE === 'FE') {
            $fields['fe_cruser_id'] = (int)$this->getTypoScriptFrontendController()->fe_user->user['uid'];
        }

        $connection = $this->connectionPool->getConnectionForTable('sys_file');
        $connection->update(
            'sys_file',
            $fields,
            [
                'uid' => (int)$event->getRelevantProperties()['uid']
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
}
