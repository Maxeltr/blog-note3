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
    function AiManager(player, gameObjectManager, map) {
        this.player = player;
        this.gameObjectManager = gameObjectManager;
        this.map = map;
        var vectorAlgebraModule = require('vectorAlgebra');
        this.vectors = vectorAlgebraModule.VectorAlgebra;
    }

    AiManager.prototype.update = function (seconds) {
        let target = this.player;

        for (let gameObject of this.gameObjectManager.getArrayObjects()) {
            if (gameObject.type !== 'npc')
                continue;
			let states = {};
            if (this.checkSight(target, gameObject, this.map)) {
                let distanceToTarget = Math.sqrt(Math.pow(target.x - gameObject.x, 2) + Math.pow(target.y - gameObject.y, 2));
                if (distanceToTarget > gameObject.getWeapons().shotDistance) {
                    states.forward = true;
                } else {
                    if (this.isAhead(gameObject, target))
                        states.space = true;
                }

                if (this.isOnLeft(gameObject, target)) {
                    states.left = true;
                } else if (this.isOnRight(gameObject, target)) {
                    states.right = true;
                }

            } else {
                let waypoint = gameObject.waypoints.current();
                if (waypoint) {
                    let distanceWaypoint = Math.sqrt(Math.pow(waypoint[0] - gameObject.x, 2) + Math.pow(waypoint[1] - gameObject.y, 2));
                    if (distanceWaypoint > gameObject.sizeRadius) {
                        if (this.isOnLeft(gameObject, {x: waypoint[0], y: waypoint[1]})) {
                            states.left = true;
                        } else if (this.isOnRight(gameObject, {x: waypoint[0], y: waypoint[1]})) {
                            states.right = true;
                        } else {
                            states.forward = true;
                        }
                    } else {
                        let next = gameObject.waypoints.next();
                        if (!next) {
                            gameObject.waypoints.reverse();
                        }
                    }
                }

            }

            gameObject.getInputs().setStates(states);
        }
    };

    AiManager.prototype.isOnLeft = function (referenceGameObject, target) {
        let referenceNormalizedVector = [Math.cos(referenceGameObject.direction), Math.sin(referenceGameObject.direction)];
        let angle = this.vectors.angleBetween(referenceNormalizedVector, [target.x - referenceGameObject.x, target.y - referenceGameObject.y]);

        return angle < -0.1;
    };

    AiManager.prototype.isOnRight = function (referenceGameObject, target) {
        let referenceNormalizedVector = [Math.cos(referenceGameObject.direction), Math.sin(referenceGameObject.direction)];
        let angle = this.vectors.angleBetween(referenceNormalizedVector, [target.x - referenceGameObject.x, target.y - referenceGameObject.y]);

        return angle > 0.1;
    };

    AiManager.prototype.isAhead = function (referenceGameObject, target) {
        let referenceNormalizedVector = [Math.cos(referenceGameObject.direction), Math.sin(referenceGameObject.direction)];
        let angle = this.vectors.angleBetween(referenceNormalizedVector, [target.x - referenceGameObject.x, target.y - referenceGameObject.y]);

        return angle > -0.1 && angle < 0.1;
    };

    AiManager.prototype.checkSight = function (target, npc, map) {
        let distanceBetweenNpcAndPlayer, toPlayerDirection, angleBetweenVectors;
        let nNpcX, nNpcY, nTargetX, nTargetY, ray;

        nNpcX = Math.cos(npc.direction);		//normalized vector sight
        nNpcY = Math.sin(npc.direction);		//normalized vector sight

        distanceBetweenNpcAndPlayer = Math.sqrt(Math.pow(target.x - npc.x, 2) + Math.pow(target.y - npc.y, 2));	//distance from npc to player

        nTargetX = (target.x - npc.x) / distanceBetweenNpcAndPlayer;						//normalize vector direction to player
        nTargetY = (target.y - npc.y) / distanceBetweenNpcAndPlayer;						//normalize vector direction to player

        angleBetweenVectors = Math.acos(Math.floor((nNpcX * nTargetX + nNpcY * nTargetY) * 1000) / 1000);		//to avoid rounding error, sometimes we get Math.acos(1.0000000002)

        if (angleBetweenVectors < (npc.fov / 2) && distanceBetweenNpcAndPlayer < npc.sightDistance) { 		//player in fov of npc and at sight distance

            toPlayerDirection = Math.atan2(target.y - npc.y, target.x - npc.x);		//direction from npc to player
            ray = map.castRay(npc.x, npc.y, toPlayerDirection, 0.3, false);	//ray from npc in player direction

            if (Math.abs(ray.distance) > distanceBetweenNpcAndPlayer) 		//player is behind wall if distance to wall is less than to player
                return true;
        }

        return false;
    };

    return {
        create: function (player, gameObjectManager, map) {
            return new AiManager(player, gameObjectManager, map);
        },
        GameObjectManager: AiManager
    };
});