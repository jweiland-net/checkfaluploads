<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Checkfaluploads\Hooks\Form;

use JWeiland\Checkfaluploads\Configuration\ExtConf;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

/**
 * Replace placeholder for customer in checkbox labels
 */
class ReplacePlaceholderHook
{
    protected ExtConf $extConf;

    public function __construct(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    public function afterBuildingFinished(RenderableInterface $formElement): void
    {
        if (!$formElement instanceof FormElementInterface) {
            return;
        }
        var_dump($formElement->getProperties());
        die;
        if (($formElement->getProperties()['checkboxType'] ?? '') === 'uploadRights') {
            $formElement->setLabel($this->extConf->getLabelForUserRights());
        }
    }
}
