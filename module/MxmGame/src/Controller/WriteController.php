<?php

/*
 * The MIT License
 *
 * Copyright 2020 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

use MxmGame\Service\GameServiceInterface;
use Laminas\Form\FormInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Log\Logger;
use MxmGame\Model\GameInterface;
use MxmGame\Exception\DataBaseErrorException;
use MxmGame\Exception\NotAuthorizedException;
use MxmGame\Exception\NotAuthenticatedException;
use Laminas\Http\Request;
use Laminas\Router\RouteInterface;

class WriteController extends AbstractActionController {

    /**
     *
     * @var \Service\GameServiceInterface
     */
    protected $gameService;

    /**
     *
     * @var Laminas\Form\FormInterface
     */
    protected $gameForm;

    /**
     *
     * @var Laminas\Log\Logger
     */
    protected $logger;

    public function __construct(
            GameServiceInterface $gameService,
            FormInterface $gameForm,
            Logger $logger
    ) {
        $this->gameService = $gameService;
        $this->gameForm = $gameForm;
        $this->logger = $logger;
    }

    public function addGameAction() {
        $error = false;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->gameForm->setData($request->getPost());
            if ($this->gameForm->isValid()) {
                $savedGame = $this->gameService->insertGame($this->gameForm->getData());

                return $this->redirect()->toRoute('detailGame',
                                ['id' => $savedGame->getId()]);
            } else {
                $error = true;
            }
        }

        return new ViewModel([
            'form' => $this->gameForm,
            'error' => $error
        ]);
    }

    public function editGameAction() {
        $request = $this->getRequest();
        $game = $this->gameService->findGameById($this->params('id'));

        $this->gameForm->bind($game);
        if ($request->isPost()) {
            $this->gameForm->setData($request->getPost());
            if ($this->gameForm->isValid()) {
                $this->gameService->updateGame($game);

                return $this->redirect()->toRoute('detailGame',
                                ['id' => $game->getId()]
                );
            }
        }

        return new ViewModel([
            'form' => $this->gameForm
        ]);
    }

}
