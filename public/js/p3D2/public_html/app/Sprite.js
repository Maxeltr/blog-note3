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
    function Sprite(name, bitmap) {
        this.name = name;
        this.bitmap = bitmap;
        this.animations = new Map();
        this.currentAnimation;
    }

    Sprite.prototype.update = function (object, seconds) {
        this.currentAnimation.update(object, seconds);
    };

    Sprite.prototype.getCurrentFrameNumber = function () {
        return this.currentAnimation.frameIndex;
    };

    Sprite.prototype.getCurrentAmountFrames = function () {
        return this.currentAnimation.frames + 1;
    };

    Sprite.prototype.isLastFrame = function () {
        return (this.currentAnimation.frameIndex === this.currentAnimation.frames);
    };

    Sprite.prototype.getCurrentAnimation = function () {
        return this.currentAnimation;
    };

    Sprite.prototype.setCurrentAnimation = function (animationName) {
        let currentAnimationName, animation;

        animation = this.animations.get(animationName);

        if (this.currentAnimation)
            currentAnimationName = this.currentAnimation.name;

        if (animation !== undefined && animationName !== currentAnimationName) {
            this.currentAnimation = animation;
            this.currentAnimation.resetFrame();
        }
    };

    Sprite.prototype.addAnimation = function (animation) {
        this.animations.set(animation.name, animation);
    };

    Sprite.prototype.getImage = function () {
        return this.bitmap.image;
    };

    Sprite.prototype.getImageX = function () {
        return this.currentAnimation.frameIndex * this.bitmap.frameWidth;
    };

    Sprite.prototype.getImageY = function (side) {
        return this.currentAnimation[side] * this.bitmap.frameHeight;
    };

    return {
        create: function (name, bitmap) {
            return new Sprite(name, bitmap);
        },
        Sprite: Sprite
    };
});

