<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Hooks\Form;

/*
 * Replace placeholder for customer in checkbox labels
 */

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

class ReplacePlaceholderHook
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    public function __construct()
    {
        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);
    }

    /**
     * @param RenderableInterface $formElement
     */
    public function afterBuildingFinished(RenderableInterface $formElement): void
    {
        if (!$formElement instanceof FormElementInterface) {
            return;
        }

        if ($formElement->getProperties()['checkboxType'] === 'uploadRights') {
            $formElement->setLabel(LocalizationUtility::translate(
                'frontend.imageUserRights',
                'checkfaluploads',
                [
                    0 => $this->extConf->getOwner()
                ]
            ));
        }
    }
}
