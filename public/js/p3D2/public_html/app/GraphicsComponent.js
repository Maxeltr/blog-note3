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
    function GraphicsComponent(sprite) {
        this.sprite = sprite;
        var vectorAlgebraModule = require('vectorAlgebra');
        this.vectors = vectorAlgebraModule.VectorAlgebra;
    }

    GraphicsComponent.prototype.setCurrentAnimation = function (animationName) {
        this.sprite.setCurrentAnimation(animationName);
    };

    GraphicsComponent.prototype.getCurrentAnimation = function () {
        return this.sprite.getCurrentAnimation();
    };

    GraphicsComponent.prototype.getFrameWidth = function () {
        return this.sprite.bitmap.frameWidth;
    };

    GraphicsComponent.prototype.getFrameHeight = function () {
        return this.sprite.bitmap.frameHeight;
    };

    GraphicsComponent.prototype.isLastFrame = function () {
        return this.sprite.isLastFrame();
    };

    GraphicsComponent.prototype.getImage = function () {
        return this.sprite.getImage();
    };

    GraphicsComponent.prototype.getImageX = function () {
        return this.sprite.getImageX();
    };

    GraphicsComponent.prototype.getImageY = function (object, cameraX, cameraY) {
        if (object) {
            let referenceNormalizedVector = [Math.cos(object.direction), Math.sin(object.direction)];
            let angle = this.vectors.angleBetween(referenceNormalizedVector, [cameraX - object.x, cameraY - object.y]);

            //onLeft
            if (angle <= -(object.fov / 2) && angle >= -(Math.PI - object.fov / 2))
                return this.sprite.getImageY('left');

            //onRight
            if (angle >= (object.fov / 2) && angle <= (Math.PI - object.fov / 2))
                return this.sprite.getImageY('right');

            //ahead
            if (angle > -(object.fov / 2) && angle < 0 || angle < (object.fov / 2) && angle > 0 || angle === 0)
                return this.sprite.getImageY('front');

            //back
            if (angle < -(Math.PI - object.fov / 2) && angle > -Math.PI || angle > (Math.PI - object.fov / 2) && angle < Math.PI || angle === Math.PI || angle === -Math.PI)
                return this.sprite.getImageY('back');

        }
    };

    GraphicsComponent.prototype.update = function (object, seconds) {
        this.sprite.update(object, seconds);

    };

    return {
        create: function (sprite) {
            return new GraphicsComponent(sprite);
        },
        GraphicsComponent: GraphicsComponent
    };
});

