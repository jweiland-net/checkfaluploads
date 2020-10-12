<?php
namespace JWeiland\Checkfaluploads\Slots;

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Add the uid of the current user to the uploaded file
 */
class FileIndexRepository
{
    /**
     * Add the uid of the user to the added file
     *
     * @param array $data
     * @return void
     */
    public function addUserToRecord(array $data)
    {
        $fields = [];
        // add field for BE or FE User
        if (TYPO3_MODE === 'BE') {
            $fields['cruser_id'] = $this->getBackendUserAuthentication()->user['uid'];
        } elseif (TYPO3_MODE === 'FE') {
            $fields['fe_cruser_id'] = $this->getTypoScriptFrontendController()->fe_user->user['uid'];
        }
        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');
        $connection->update('sys_file', $fields, ['uid' => $data['uid']]);
    }

    /**
     * Get BackendUserAuthentication
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Get TypoScriptFrontendController
     *
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
