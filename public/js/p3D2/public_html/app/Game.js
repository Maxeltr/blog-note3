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
    function Game() {
        this.state;
        this.inputHandlers = [];
        this.codes = {'Escape': 27, 'Enter': 13};

        Game.prototype.getState = function () {
            return this.state;
        };

        Game.prototype.setState = function (state) {
            this.state = state;
        };

        this.addInputHandler = function (handler) {
            let onKey = function (e) {
                if (e.keyCode === this.codes[handler.name]) {
                    handler(this);
                }
                e.preventDefault && e.preventDefault();
                e.stopPropagation && e.stopPropagation();
            }.bind(this);
            document.addEventListener('keyup', onKey, false);
            this.inputHandlers.push(onKey);
        };

        this.removeInputHandlers = function () {
            for (i = 0; i < this.inputHandlers.length; i++) {
                document.removeEventListener('keyup', this.inputHandlers[i]);
            }
            this.inputHandlers = [];
        };

        var mapModule = require('./Map');
        var bitmapModule = require('./Bitmap');
        var cameraModule = require('./Camera');
        var gameObjectManagerModule = require('./GameObjectManager');
        var aiManagerModule = require('./AiManager');
        var collisionDetectorModule = require('./CollisionDetector');

        this.loop = require('./GameLoop').create();

        this.background = bitmapModule.create('http://blog-note3/img/game/sky_daytime_blue.jpg', 2048, 1024, 2048, 1024);
        //this.walls = bitmapModule.create('http://blog-note3/img/game/textures.png', 384, 64, 64, 64);
        this.walls = bitmapModule.create('http://blog-note3/img/game/walls.png', 2560, 640, 640, 640);

        this.playerCamera = cameraModule.create(1024, 512);
        this.playerCamera.setCanvas(document.getElementById("3DView"));

        this.mapScreen = cameraModule.create(256, 256);
        this.mapScreen.setCanvas(document.getElementById("map"));

        this.map = mapModule.create();

        this.gameObjectManager = gameObjectManagerModule.create();

        this.gameObjectManager.create('orc', 4, 4, 0);
        this.gameObjectManager.create('orc', 7, 2, 0.8);
        this.gameObjectManager.create('orc', 4, 2, 0.8);
        this.gameObjectManager.create('orc', 7, 6, 0.8);
        this.gameObjectManager.create('orc', 9, 2, 0.8);
        this.gameObjectManager.create('orc', 7, 8, 0.8);
        this.gameObjectManager.create('orc', 3, 5, 0);

        let player = this.gameObjectManager.create('player', 7, 2.7, 3.14);

        this.aiManager = aiManagerModule.create(player, this.gameObjectManager, this.map);

        this.collisionDetector = collisionDetectorModule.create(this.gameObjectManager, this.map);

        this.startLoop = function (seconds) {
            this.playerCamera.clearScreen();
            this.playerCamera.context.save();
            this.playerCamera.context.fillStyle = "red";
            this.playerCamera.context.font = "24px serif";
            this.playerCamera.context.fillText('Press start', 15, 20);
            this.playerCamera.context.restore();
        }.bind(this);

        this.playLoop = function (seconds) {
            this.aiManager.update(seconds);
            this.gameObjectManager.update(seconds);
            this.collisionDetector.update(seconds);

            let player = this.gameObjectManager.getPlayer();
            if (!player) {
                this.getState().loose(this);
                return;
            }
            
            let npcArr = this.gameObjectManager.getArrayObjects();
            if (npcArr.length === 1 && npcArr[0].name === 'player')
                this.getState().win(this);

            this.playerCamera.clearScreen();
            this.playerCamera.drawBackground(this.background, player.direction);
            this.playerCamera.drawWalls(player.x, player.y, player.direction, player.fov, this.map, this.walls);
            this.playerCamera.drawObjects(npcArr, player.x + 0.1 * Math.cos(player.direction), player.y + 0.1 * Math.sin(player.direction), player.direction, player.fov);
            this.playerCamera.drawMiniMap(player.x, player.y, player.direction, player.sizeRadius, this.playerCamera.width / 5, this.map, 'grey');
            this.playerCamera.drawHealthBar(player.health, 'grey');
            this.playerCamera.drawAim(5, 'red');                                    

            this.mapScreen.clearScreen();
            this.mapScreen.drawMap(this.map, 'grey');
            this.mapScreen.drawObjectsOnMap(npcArr, this.map, 'red');
            this.mapScreen.drawFovsOnMap(npcArr, this.map, 'blue');
            this.mapScreen.drawObjectOnMap(player, this.map, 'yellow');
            this.mapScreen.drawFovOnMap(player, this.map, 'yellow');

        }.bind(this);

        this.pauseLoop = function (seconds) {
            let npcArr = this.gameObjectManager.getArrayObjects();
            let player = this.gameObjectManager.getPlayer();

            this.playerCamera.clearScreen();
            this.playerCamera.drawMap(this.map, 'grey');
            this.playerCamera.drawObjectOnMap(player, this.map, 'grayish');

            this.mapScreen.clearScreen();
            this.mapScreen.drawMap(this.map, 'grey');
            this.mapScreen.drawObjectsOnMap(npcArr, this.map, 'red');
            this.mapScreen.drawFovsOnMap(npcArr, this.map, 'blue');
            this.mapScreen.drawObjectOnMap(player, this.map, 'yellow');
            this.mapScreen.drawFovOnMap(player, this.map, 'yellow');

            this.playerCamera.context.save();
            this.playerCamera.context.fillStyle = "red";
            this.playerCamera.context.font = "24px serif";
            this.playerCamera.context.fillText('PAUSE', 15, 20);
            this.playerCamera.context.restore();
        }.bind(this);

        this.looseLoop = function (seconds) {
            this.playerCamera.clearScreen();
            this.playerCamera.context.save();
            this.playerCamera.context.fillStyle = "red";
            this.playerCamera.context.font = "24px serif";
            this.playerCamera.context.fillText('loose', 15, 20);
            this.playerCamera.context.restore();
        }.bind(this);

        this.winLoop = function (seconds) {
            this.playerCamera.clearScreen();
            this.playerCamera.context.save();
            this.playerCamera.context.fillStyle = "red";
            this.playerCamera.context.font = "24px serif";
            this.playerCamera.context.fillText('win', 15, 20);
            this.playerCamera.context.restore();
        }.bind(this);
    }






    return {
        create: function () {
            return new Game();
        }
    };
});

