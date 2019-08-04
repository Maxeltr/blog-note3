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

define(function () {
    function CollisionDetector(gameObjectManager, map) {
        this.gameObjectManager = gameObjectManager;
        this.map = map;
    }

    CollisionDetector.prototype.update = function (seconds) {
        this.checkCollision(this.gameObjectManager.getArrayObjects(), seconds);

    };

    CollisionDetector.prototype.checkCollision = function (gameObjects, seconds) {
        for (let i = 0; i < gameObjects.length; i++) {
            for (let j = i + 1; j < gameObjects.length; j++) {
                let distance = Math.sqrt(Math.pow(gameObjects[i].x - gameObjects[j].x, 2) + Math.pow(gameObjects[i].y - gameObjects[j].y, 2));
                let radiuses = (gameObjects[i].sizeRadius + gameObjects[j].sizeRadius) * 0.9;
                if (distance < radiuses) {
                    let resolveDistance = (gameObjects[i].sizeRadius + gameObjects[j].sizeRadius - distance) / radiuses;
                    if (gameObjects[i].sizeRadius > gameObjects[j].sizeRadius) {
                        gameObjects[i].getCollisions().resolveCollision(gameObjects[i], gameObjects[j], resolveDistance * gameObjects[j].sizeRadius, seconds, this.map);
                        gameObjects[j].getCollisions().resolveCollision(gameObjects[j], gameObjects[i], resolveDistance * gameObjects[i].sizeRadius, seconds, this.map);
                    } else {
                        gameObjects[i].getCollisions().resolveCollision(gameObjects[i], gameObjects[j], resolveDistance * gameObjects[i].sizeRadius, seconds, this.map);
                        gameObjects[j].getCollisions().resolveCollision(gameObjects[j], gameObjects[i], resolveDistance * gameObjects[j].sizeRadius, seconds, this.map);
                    }
                }
            }

            if (this.checkCollisionsWithWalls(gameObjects[i]))
                gameObjects[i].getCollisions().resolveCollisionWithWalls(gameObjects[i], this.map, seconds);
        }
    };

    CollisionDetector.prototype.checkCollisionsWithWalls = function (object) {
        return !this.map.isEmptyCell(
                object.x + object.sizeRadius * Math.cos(object.motionDirection),
                object.y + object.sizeRadius * Math.sin(object.motionDirection)
                ) 
                || 
                !this.map.isEmptyCell(
                object.x,
                object.y  
                );
    };

    return {
        create: function (gameObjectManager, map) {
            return new CollisionDetector(gameObjectManager, map);
        },
        CollisionDetector: CollisionDetector
    };
});
