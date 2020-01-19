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
namespace MxmGame\Mapper;

use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\Db\TableGateway\TableGateway;
use Rhumsaa\Uuid\Uuid;
use Laminas\Config\Config;
use Laminas\Http\Response;
use Laminas\Log\Logger;
use Laminas\Stdlib\ErrorHandler;
use MxmGame\Exception\RecordNotFoundException;
use MxmGame\Exception\InvalidArgumentException;
use Laminas\Stdlib\ArrayUtils;
use MxmGame\Model\GameInterface;
use MxmUser\Model\UserInterface;
use Laminas\Db\Sql\Where;
use MxmGame\Exception\DataBaseErrorException;

class ZendTableGatewayMapper implements MapperInterface
{
    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $gameTableGateway;

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $textureTableGateway;

    /**
     * @var Laminas\Config\Config
     */
    protected $config;

    /**
     * @var Laminas\Http\Response
     */
    protected $response;

    /**
     * @var Laminas\Log\Logger
     */
    protected $logger;

    public function __construct(
        TableGateway $gameTableGateway,
        TableGateway $textureTableGateway,
        Config $config,
        Response $response,
        Logger $logger
    ){
        $this->gameTableGateway = $gameTableGateway;
        $this->textureTableGateway = $textureTableGateway;
        $this->config = $config;
        $this->response = $response;
        $this->logger = $logger;
    }

    /*
     * {@inheritDoc}
     */
    public function insertGame($game)
    {
        $this->gameTableGateway->insert($game);
        $resultSet = $this->gameTableGateway->select(['game_id' => $game['game_id']]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * {@inheritDoc}
     */
    public function findAllGames()
    {
        $paginator = new Paginator(new DbTableGateway($this->gameTableGateway, null, ['upload_date' => 'DESC']));

        return $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function findGameById($gameId)
    {
        $resultSet = $this->gameTableGateway->select(['game_id' => $gameId]);
        if (0 === count($resultSet)) {
            throw new RecordNotFoundException('Game ' . $gameId . 'not found.');
        }

        return $resultSet->current();
    }

    /**
     * {@inheritDoc}
     */
    public function findTextureById($textureId)
    {
        $resultSet = $this->textureTableGateway->select(['texture_id' => $textureId]);
        if (0 === count($resultSet)) {
            throw new RecordNotFoundException('Texture ' . $textureId . 'not found.');
        }

        return $resultSet->current();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteGame(GameInterface $game)
    {
        $result = $this->unlinkFile($game->getPath());
        if (! $result) {
            $this->logger->err("Cannot delete file. Id: " . $game->getGameId() . ".");

            return false;
        }

        $result = $this->gameTableGateway->delete(['game_id' => $game->getGameId()]);
        if (! $result) {
            $this->logger->err("Cannot delete game record. Id: " . $game->getGameId() . ".");
        }

        return $result;
    }

    private function unlinkFile($filePath)
    {
        ErrorHandler::start();
        $test = unlink($filePath);
        $error = ErrorHandler::stop();
        if (! $test) {
            $this->logger->err('Cannot remove file ' . $filePath . '. ' . $error . '.');
        }

        return $test;
    }
}