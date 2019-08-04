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
    function CollisionComponent() {
        var vectorAlgebraModule = require('vectorAlgebra');
        this.vectors = vectorAlgebraModule.VectorAlgebra;
    }

    CollisionComponent.prototype.resolveCollision = function (thisObject, hitObject, distance, seconds, map) {

        let reverseDirection = this.vectors.angle([hitObject.x - thisObject.x, hitObject.y - thisObject.y]);
        reverseDirection += Math.PI;

        while (reverseDirection < 0) {
            reverseDirection += (2 * Math.PI);
        }

        while (reverseDirection >= (2 * Math.PI)) {
            reverseDirection -= (2 * Math.PI);
        }

        if (map.isEmptyCell(thisObject.x + thisObject.sizeRadius * Math.cos(reverseDirection), thisObject.y)) {
            thisObject.x += distance * Math.cos(reverseDirection);
        }

        if (map.isEmptyCell(thisObject.x, thisObject.y + thisObject.sizeRadius * Math.sin(reverseDirection))) {
            thisObject.y += distance * Math.sin(reverseDirection);
        }

    };

    CollisionComponent.prototype.resolveCollisionWithWalls = function (object, map, seconds) {

        let motionDirection = object.motionDirection;
        let reverseDirection = motionDirection + Math.PI;

        while (reverseDirection < 0) {
            reverseDirection += (2 * Math.PI);
        }

        while (reverseDirection >= (2 * Math.PI)) {
            reverseDirection -= (2 * Math.PI);
        }

        while (!map.isEmptyCell(object.x + object.sizeRadius * Math.cos(motionDirection), object.y + object.sizeRadius * Math.sin(motionDirection))) {
            if (!map.isEmptyCell(object.x + object.sizeRadius * Math.cos(motionDirection), object.y))
                object.x += object.sizeRadius * Math.cos(reverseDirection) * 0.1;

            if (!map.isEmptyCell(object.x, object.y + object.sizeRadius * Math.sin(motionDirection)))
                object.y += object.sizeRadius * Math.sin(reverseDirection) * 0.1;
        }
        
        while (!map.isEmptyCell(object.x, object.y)) {
            object.x += object.sizeRadius * Math.cos(reverseDirection) * 0.1;
            object.y += object.sizeRadius * Math.sin(reverseDirection) * 0.1;
        }
    };

    CollisionComponent.prototype.update = function (object, seconds) {

    };

    return {
        create: function () {
            return new CollisionComponent();
        },
        CollisionComponent: CollisionComponent
    };
});
