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
    function ArrowGameObjectFactory() {

        let forcedMoveInputComponentModule = require('./ForcedMoveInputComponent');
        let graphicsComponentModule = require('./GraphicsComponent');
        let physicsComponentModule = require('./PhysicsComponent');
        let stateModule = require('./BulletState');
        let arrowSpriteFactoryModule = require('./ArrowSpriteFactory');
        let gameObjectModule = require('./GameObject');
        var commandModule = require('./Command');
        let nullWeaponModule = require('./NullWeaponComponent');
        let nullSubjectModule = require('./NullSubjectComponent');
        let collisionModule = require('./DestructionCollisionComponent');

        let arrowSpriteFactory = arrowSpriteFactoryModule.create();

        return function () {
            let gameObject = gameObjectModule.create(
                    physicsComponentModule.create(),
                    graphicsComponentModule.create(arrowSpriteFactory()),
                    forcedMoveInputComponentModule.create(new commandModule.MoveForwardCommand()),
                    stateModule.createStateContainer().getMoveState(),
                    nullWeaponModule.create(),
                    nullSubjectModule.create(),
                    collisionModule.create()
                    );

            gameObject.name = 'arrow';
            gameObject.sizeRadius = 0.015;
            gameObject.damage = 10.0;

            return gameObject;
        };
    }

    return {
        create: function () {
            return new ArrowGameObjectFactory();
        },
        ArrowGameObjectFactory: ArrowGameObjectFactory
    };
});
