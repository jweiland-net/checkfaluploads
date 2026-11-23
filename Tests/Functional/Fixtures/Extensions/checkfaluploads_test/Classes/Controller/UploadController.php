<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/checkfaluploads.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\CheckfaluploadsTest\Controller;

use JWeiland\CheckfaluploadsTest\Domain\Model\Item;
use JWeiland\CheckfaluploadsTest\Domain\Repository\ItemRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Extbase controller of a frontend form with a file upload. It contains no
 * checkfaluploads code at all, like any foreign extension.
 */
final class UploadController extends ActionController
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {}

    public function newAction(?Item $item = null): ResponseInterface
    {
        $this->view->assign('item', $item ?? new Item());

        return $this->htmlResponse();
    }

    public function createAction(Item $item): ResponseInterface
    {
        $this->itemRepository->add($item);
        $this->persistenceManager->persistAll();
        $this->view->assign('item', $item);

        return $this->htmlResponse();
    }
}
