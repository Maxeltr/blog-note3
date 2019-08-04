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
    function Bitmap(src, width, height, frameWidth, frameHeight, onload) {
        this.width = width;
        this.height = height;
        this.isLoaded;
        this.onload = onload;
        this.src = src;
        this.frameHeight = frameHeight;
        this.frameWidth = frameWidth;
        this.image = new Image();

        this.load();
    }
    ;

    Bitmap.prototype.load = function (onload, src) {
        let self = this;

        let callback = onload || this.onload;
        this.image.onload = function () {
            if (callback)
                callback();
            self.isLoaded = true;
        };
        this.image.src = src || this.src;
    };

    return {
        create: function (src, width, height, frameWidth, frameHeight, onload) {
            return new Bitmap(src, width, height, frameWidth, frameHeight, onload);
        }
    };
});
