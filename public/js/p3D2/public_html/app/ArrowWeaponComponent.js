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
    function ArrowWeaponComponent() {
        this.name = 'arrow';
        this.shotDistance = 3.0;
        this.isCharge;
        this._wasShot;
    }

    ArrowWeaponComponent.prototype.shoot = function (object, seconds) {
        if (this.isCharge) {
            object.getSubject().notifyObservers(object, {event: 'shot', params: {x: object.x + object.sizeRadius * Math.cos(object.direction), y: object.y + object.sizeRadius * Math.sin(object.direction), direction: object.direction}});
            this._wasShot = true;
        }
    };

    ArrowWeaponComponent.prototype.update = function (object, seconds) {
        if (object.getGraphics().getCurrentAnimation().name === 'shoot') {
            if (object.getGraphics().isLastFrame()) {
                if (!this._wasShot) {
                    this.isCharge = true;
                } else {
                    this.isCharge = false;
                }
            } else {
                if (this._wasShot)
                    this._wasShot = false;
            }
        } else if (this.isCharge || this._wasShot) {
            this.isCharge = false;
            this._wasShot = false;
        }
    };

    return {
        create: function () {
            return new ArrowWeaponComponent();
        },
        ArrowWeaponComponent: ArrowWeaponComponent
    };
});
