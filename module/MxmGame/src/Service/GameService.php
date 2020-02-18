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

namespace MxmGame\Service;

use Laminas\Log\Logger;
use Laminas\Config\Config;
use Laminas\Paginator\Paginator;
use Laminas\Authentication\AuthenticationService;
use MxmGame\Exception\InvalidArgumentException;
use MxmRbac\Service\AuthorizationService;
use Laminas\Paginator\Adapter\ArrayAdapter;
use MxmGame\Mapper\MapperInterface;
use Laminas\Validator\Db\RecordExists;
use MxmGame\Model\GameInterface;

class GameService implements GameServiceInterface {

    /**
     * @var Laminas\Authentication\AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var MxmRbac\Service\AthorizationService
     */
    protected $authorizationService;

    /**
     * @var Laminas\Config\Config;
     */
    protected $config;

    /**
     * @var Laminas\Log\Logger
     */
    protected $logger;

    /**
     * @var MxmGame\Mapper\MapperInterface
     */
    protected $mapper;

    /**
     * @var Laminas\Validator\Db\RecordExists
     */
    protected $recordExists;

    public function __construct(
            AuthenticationService $authenticationService,
            AuthorizationService $authorizationService,
            Config $config,
            Logger $logger,
            MapperInterface $mapper,
            RecordExists $recordExists
    ) {
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->config = $config;
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->recordExists = $recordExists;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllGames() {
        return $this->mapper->findAllGames();
    }

    /**
     * {@inheritDoc}
     */
    public function findGameById($id) {
        return $this->mapper->findGameById($id);
    }

    /**
     * {@inheritDoc}
     */
    public function updateGame(GameInterface $game) {
        $this->authorizationService->checkPermission('edit.game', $game);

        $game->setUpdateDate($this->datetime->modify('now'));

        if ($game->getIsPublished() === true && $this->recordExists->isValid(true)) {
            $game->setPublished($this->datetime->modify('now'));
        }

        return $this->mapper->updateGame($game);
    }

    public function findTextureById($id) {
        $texture = $this->mapper->findTextureById($id);

        return $texture;
    }

}
