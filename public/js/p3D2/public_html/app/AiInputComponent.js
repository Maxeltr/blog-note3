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
    function AiInputComponent(up, down, left, right, space) {
        this.states = {'left': false, 'right': false, 'forward': false, 'backward': false, 'space': false};
        this.buttonUp = up;
        this.buttonDown = down;
        this.buttonLeft = left;
        this.buttonRight = right;
        this.buttonSpace = space;
    }

    AiInputComponent.prototype.handleInput = function () {
        let buttons = [];

        if (this.states.left)
            buttons.push(this.buttonLeft);
        if (this.states.right)
            buttons.push(this.buttonRight);
        if (this.states.forward)
            buttons.push(this.buttonUp);
        if (this.states.backward)
            buttons.push(this.buttonDown);
        if (this.states.space)
            buttons.push(this.buttonSpace);

        return buttons;
    };

    AiInputComponent.prototype.setStates = function (states) {
        for (let state in this.states) {
            if (this.states.hasOwnProperty(state))
                this.states[state] = false;
        }

        for (let state in states) {
            if (states.hasOwnProperty(state))
                this.states[state] = states[state];
        }
    };

    return {
        create: function (up, down, left, right, space) {
            return new AiInputComponent(up, down, left, right, space);
        },
        AiInputComponent: AiInputComponent
    };
});
