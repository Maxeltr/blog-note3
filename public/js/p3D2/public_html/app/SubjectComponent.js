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
    function SubjectComponent() {
        this.observers = {};
    }

    SubjectComponent.prototype.registerObserver = function (observer) {
        this.observers[observer.id] = observer;
    };

    SubjectComponent.prototype.removeObserver = function (observer) {
        delete this.observers[observer.id];
    };

    SubjectComponent.prototype.notifyObservers = function (subject, event) {
        for (let observerId in this.observers) {
            if (this.observers.hasOwnProperty(observerId)) {
                this.observers[observerId].onNotify(subject, event);
            }
        }
    };

    return {
        create: function () {
            return new SubjectComponent();
        },
        SubjectComponent: SubjectComponent
    };
});
