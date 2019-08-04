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

define(function (require) {
    function GameObjectManager() {
        this.id = 'GameObjectManager';	//todo guid
        this.gameObjects = new Map();
        this.factories = new Map();
        this.playerId;

        let orcGameObjectFactoryModule = require('./OrcGameObjectFactory');
        this.addFactory('orc', orcGameObjectFactoryModule.create());

        let playerGameObjectFactoryModule = require('./PlayerGameObjectFactory');
        this.addFactory('player', playerGameObjectFactoryModule.create());

        let arrowGameObjectFactoryModule = require('./ArrowGameObjectFactory');
        this.addFactory('arrow', arrowGameObjectFactoryModule.create());
    }

    GameObjectManager.prototype.addFactory = function (factoryName, factory) {
        this.factories.set(factoryName, factory);
    };

    GameObjectManager.prototype.create = function (factoryName, x, y, direction) {
        let gameObject, factory;

        factory = this.factories.get(factoryName);
        if (!factory)
            return;

        gameObject = factory(factoryName);
        gameObject.id = uuidv4();
        gameObject.x = x;
        gameObject.y = y;
        gameObject.direction = direction;
        gameObject.getSubject().registerObserver(this);

        this.gameObjects.set(gameObject.id, gameObject);

        if (factoryName === 'player')
            this.playerId = gameObject.id;

        return gameObject;

        function uuidv4() {
            return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
            );
        }
    };

    GameObjectManager.prototype.delete = function (key) {
        return this.gameObjects.delete(key);
    };

    GameObjectManager.prototype.getArrayObjects = function () {
        return [...this.gameObjects.values()];
    };

    GameObjectManager.prototype.sort = function () {

    };

    GameObjectManager.prototype.get = function (key) {
        return this.gameObjects.get(key);
    };

    GameObjectManager.prototype.getPlayer = function () {
        return this.gameObjects.get(this.playerId);
    };

    GameObjectManager.prototype.onNotify = function (object, event) {
        this.createBullets(object, event);
    };

    GameObjectManager.prototype.createBullets = function (object, event) {
        let weapons = object.getWeapons();
        this.create(weapons.name, event.params.x, event.params.y, event.params.direction);
    };

    GameObjectManager.prototype.update = function (seconds) {
        for (let gameObject of this.gameObjects.values()) {
            gameObject.update(seconds);
            if (gameObject.health <= 0) {
                gameObject.getState().destroy(gameObject, seconds);
            }

            if (gameObject.destroy === true) {
                this.gameObjects.delete(gameObject.id);
            }
        }
    };

    return {
        create: function () {
            return new GameObjectManager();
        },
        GameObjectManager: GameObjectManager
    };
});