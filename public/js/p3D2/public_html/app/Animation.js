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
    function Animation(
            animationName,
            spritesheetRowFrontSide,
            spritesheetRowBackSide,
            spritesheetRowLeftSide,
            spritesheetRowRightSide,
            frameAmount,
            animationSpeed,
            initialFrame
            ) {
        this.name = animationName;
        this.initialFrame = initialFrame;
        this.frameIndex = this.initialFrame;
        this._frameIndex = this.initialFrame;
        this.front = spritesheetRowFrontSide;
        this.back = spritesheetRowBackSide;
        this.left = spritesheetRowLeftSide;
        this.right = spritesheetRowRightSide;
        this.animationSpeed = animationSpeed;
        this.frames = frameAmount - 1;
    }

    Animation.prototype.update = function (object, seconds) {
        this._frameIndex += this.animationSpeed * seconds;
        this.frameIndex = Math.trunc(this._frameIndex);
        if (this.frameIndex > this.frames) {
            this.frameIndex = this._frameIndex = this.initialFrame;
        }
    };

    Animation.prototype.resetFrame = function () {
        this._frameIndex = this.initialFrame;
        this.frameIndex = this.initialFrame;
    };

    return {
        create: function (animationName, spritesheetRowFrontSide, spritesheetRowBackSide, spritesheetRowLeftSide, spritesheetRowRightSide, frameAmount, animationSpeed, initialFrame) {
            return new Animation(animationName, spritesheetRowFrontSide, spritesheetRowBackSide, spritesheetRowLeftSide, spritesheetRowRightSide, frameAmount, animationSpeed, initialFrame);
        },
        Animation: Animation
    };
});

