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
    function GameObject(physicsComponent, graphicsComponent, inputComponent, stateComponent, weaponComponent, subjectComponent, collisionComponent) {
        this.id;
        this.type;
        this.name;
        this.x = 1.4;
        this.y = 1.4;
        this.direction = 0.0;
        this.motionDirection = 0.0;
        this.fov = Math.PI / 3.0;               //field of view
        this.sizeRadius = 0.2;
        this.destroy = false;
        this.movementVelocity = 3;
		this.movementAcceleration = 2.0;
        this.maxMoveVelocity = 5.0;
        this.rotationVelocity = Math.PI / 3;
		this.rotationAcceleration = 2.5;
        this.maxRotationVelocity = 3.0;
        this.physicsComponent = physicsComponent;
        this.graphicsComponent = graphicsComponent;
        this.inputComponent = inputComponent;
        this.stateComponent = stateComponent;
        this.weaponComponent = weaponComponent;
        this.subjectComponent = subjectComponent;
        this.collisionComponent = collisionComponent;
        this.paces = 0;
        this.sightDistance = 4.0;
        this.health = 100.0;
        this.damage = 0.0;
		this.currentMoveSpeed = 0.0;
        this.currentRotationSpeed = 0.0;
        this.waypoints = {
            waypoints: [], nextIndex: 0,
            set: function (waypoints) {
                this.waypoints = waypoints;
            },

            next: function () {
                if (this.nextIndex < this.waypoints.length) {
                    return this.waypoints[this.nextIndex++];
                }
            },

            current: function () {
                return this.waypoints[this.nextIndex];
            },

            rewind: function () {
                this.nextIndex = 0;
            },

            reverse: function () {
                this.waypoints.reverse();
            }
        };
    }

    GameObject.prototype.update = function (seconds) {

        this._handleInput(seconds);

        this.getState().update(this, seconds);

    };

    GameObject.prototype._handleInput = function (seconds) {
        let commands = this.inputComponent.handleInput();
        for (i = 0; i < commands.length; i++) {
            commands[i].execute(this, seconds);
        }

        if (commands.length === 0 && typeof this.getState().stop === 'function')
            this.getState().stop(this);

    };

    GameObject.prototype.getState = function () {
        return this.stateComponent;
    };

    GameObject.prototype.setState = function (state) {
        this.stateComponent = state;
    };

    GameObject.prototype.getPhysics = function () {
        return this.physicsComponent;
    };

    GameObject.prototype.getGraphics = function () {
        return this.graphicsComponent;
    };

    GameObject.prototype.getInputs = function () {
        return this.inputComponent;
    };

    GameObject.prototype.getWeapons = function () {
        return this.weaponComponent;
    };

    GameObject.prototype.getSubject = function () {
        return this.subjectComponent;
    };

    GameObject.prototype.getCollisions = function () {
        return this.collisionComponent;
    };

    return {
        create: function (physicsComponent, graphicsComponent, inputComponent, stateComponent, weaponComponent, subjectComponent, collisionComponent) {
            return new GameObject(physicsComponent, graphicsComponent, inputComponent, stateComponent, weaponComponent, subjectComponent, collisionComponent);
        },
        GameObject: GameObject
    };
});