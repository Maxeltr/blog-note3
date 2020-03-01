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
    function StateContainer() {
        this.getStopState = function () {
            return new StopState(this);
        };

        this.getMoveState = function () {
            return new MoveState(this);
        };

        this.getShootState = function () {
            return new ShootState(this);
        };

        this.getDestroyState = function () {
            return new DestroyState(this);
        };
    }

    function State(container) {
        this.container = container;
        this.name;
    }

    State.prototype.getName = function () {
        return this.name;
    };

    State.prototype.update = function () {

    };

    State.prototype.stop = function (object, seconds) {
        object.currentSpeed = 0.0;
        object.currentRotationSpeed = 0.0;
        object.getGraphics().setCurrentAnimation('stop');
        object.setState(this.container.getStopState());
    };

    State.prototype.move = function (object, seconds) {
        object.getGraphics().setCurrentAnimation('move');
        object.setState(this.container.getMoveState());
    };

    State.prototype.shoot = function (object, seconds) {
        object.getGraphics().setCurrentAnimation('getWeapons');
        object.setState(this.container.getShootState());
    };

    State.prototype.destroy = function (object, seconds) {
        object.getGraphics().setCurrentAnimation('destroy');
        object.setState(this.container.getDestroyState());
    };

    function StopState(container) {
        State.apply(this, arguments);
        this.name = 'STATE_STOP';
        this.reactionTime;
        this.idleTime;

        this.stop = function (object, seconds) {

        };

        this.update = function (object, seconds) {
            object.getGraphics().update(object, seconds);
        };
    }

    StopState.prototype = Object.create(State.prototype);
    StopState.prototype.constructor = StopState;

    function MoveState(container) {
        State.apply(this, arguments);
        this.name = 'STATE_MOVE';

        this.move = function (object, seconds) {
            object.getPhysics().move(object, seconds);
        };

        this.update = function (object, seconds) {
            object.getGraphics().update(object, seconds);
        };
    }

    MoveState.prototype = Object.create(State.prototype);
    MoveState.prototype.constructor = MoveState;

    function ShootState(container) {
        State.apply(this, arguments);
        this.name = 'STATE_SHOOT';

        this.shoot = function (object, seconds) {
            object.getWeapons().shoot(object, seconds);
        };

        this.move = function (object, seconds) {
            let commands = object.getInputs().handleInput();
            for (let i = 0; i < commands.length; i++) {
                if (commands[i].name === 'Shoot')
                    return;
            }
            this.stop(object);
        };

        this.update = function (object, seconds) {
            let graphics = object.getGraphics();
            if (graphics.getCurrentAnimation().name === 'getWeapons' && graphics.isLastFrame())
                graphics.setCurrentAnimation('shoot');
            graphics.update(object, seconds);

            object.getWeapons().update(object, seconds);
        };
    }

    ShootState.prototype = Object.create(State.prototype);
    ShootState.prototype.constructor = ShootState;

    function DestroyState(container) {
        State.apply(this, arguments);
        this.name = 'STATE_DESTROY';
        this._destroyTime = 0.0;

        this.destroy = function (object, seconds) {

        };

        this.stop = function (object, seconds) {

        };

        this.move = function (object, seconds) {

        };

        this.shoot = function (object, seconds) {

        };

        this.update = function (object, seconds) {
            if (!object.getGraphics().isLastFrame()) {
                object.getGraphics().update(object, seconds);
            } else {
                this._destroyTime += seconds;
                if (this._destroyTime > 1)
                    object.destroy = true;
            }
        };
    }

    DestroyState.prototype = Object.create(State.prototype);
    DestroyState.prototype.constructor = DestroyState;

    return {
        createStateContainer: function () {
            return new StateContainer();
        },
        StateContainer: StateContainer
    };
});
