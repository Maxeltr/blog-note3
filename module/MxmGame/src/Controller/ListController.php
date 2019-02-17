<?php

/*
 * The MIT License
 *
 * Copyright 2019 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MxmGame\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Config\Config;
use Zend\Log\Logger;
use MxmGame\Service\GameServiceInterface;
use Zend\i18n\Translator\TranslatorInterface;

class ListController extends AbstractActionController
{
    /**
     * @var Zend\Config\Config
     */
    protected $config;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \MxmGame\Service\GameServiceInterface
     */
    protected $gameService;

    /**
     * @var Zend\i18n\Translator\TranslatorInterface
     */
    protected $translator;

    public function __construct(
        GameServiceInterface $gameService,
        Config $config,
        Logger $logger,
        TranslatorInterface $translator
    ) {
        $this->gameService = $gameService;
        $this->config = $config;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function listGamesAction()
    {
        $paginator = $this->gameService->findAllGames();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'games' => $paginator,
            'route' => 'listGames'
        ]);
    }

    public function detailGameAction()
    {
        $id = 1;

        return new ViewModel([
            'game' => $id
        ]);
    }

    public function loadTexturesAction()
    {
        $pathname = 'c:\js\p3d\textures.png';       //TODO refactor

        $file = file_get_contents($pathname);

        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment;filename="' . $pathname . '"',
        ));
        $response->setContent($file);

        return $response;
    }

    private function configurePaginator(Paginator $paginator)
    {
        $page = (int) $this->params()->fromRoute('page');
        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->config->mxm_game->listController->ItemCountPerPage);

        return $this;
    }
}
