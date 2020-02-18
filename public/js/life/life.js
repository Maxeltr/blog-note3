/*
 * The MIT License
 *
 * Copyright 2020 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

'use strict';
var life = (function(canvas) {
    function Grid(canvas) {
        this.height = 100;
        this.width = 200;
        this.dz = 3;
        this.grid;
        this.generation = 0;
        this.font = "24px serif";
        this.textColor = "red";
        this.speedTimer;
        this.canvas = canvas;
        this.showDead = false;
        this.context;
        this.amountCells = 0;
        this.amountFemaleCells = 0;
        this.amountMaleCells = 0;

        if (typeof this.init != 'function') {
            Grid.prototype.init = function(probability) {
                this.generation = 0;
                this.amountCells = 0;
                this.amountFemaleCells = 0;
                this.amountMaleCells = 0;
                this.grid = new Array(this.height);
                for (let k = 0; k < this.grid.length; k++) {					//coord y
                    this.grid[k] = new Array(this.width);
                    for (let j = 0; j < this.grid[k].length; j++) {				//coord x
                        if (Math.random() < probability) {
                            this.grid[k][j] = this.giveBirthToCell(j, k);
                        }
                    }
                }

                return this.grid;
            }
        }

        if (typeof this.giveBirthToCell != 'function') {
            Grid.prototype.giveBirthToCell = function(x, y) {
                var cell = new Cell();
                cell.sex = Math.random() > 0.5;
                cell.x = x;
                cell.y = y;

                return cell;
            }
        }

        if (typeof this.cloneCell != 'function') {
            Grid.prototype.cloneCell = function(cell) {
                var cloneOfCell = new Cell();
                for (let property in cell) {
                    if (cell.hasOwnProperty(property)) {
                        cloneOfCell[property] = cell[property];
                    }
                }

                return cloneOfCell;
            }
        }

        if (typeof this.update != 'function') {
            Grid.prototype.update = function() {
                let amountAlives;
                let arr = new Array(this.height);
                for (let i = 0; i < arr.length; i++) {													//coord y
                    arr[i] = new Array(this.width);
                    for (let j = 0; j < arr[i].length; j++) {											//coord x
                        amountAlives = this.getAliveNeighbors(j, i).get('amountAlives');
                        if (! (this.grid[i][j] instanceof Cell) || ! this.grid[i][j].isAlive) {			//если ячейка пустая или клетка мертвая
                            if (amountAlives == 3) {													//если кол-во соседей 3, то клетка рождается
                                arr[i][j] = this.giveBirthToCell(j, i);
                            }
                        } else {																		//если ячейка не пустая или клетка не мертвая
                            if ((amountAlives == 2) || (amountAlives == 3)) {							//если если кол-во соседей 2 или 3, то клетка продолжает жить
                                arr[i][j] = this.cloneCell(this.grid[i][j]);
                                ++arr[i][j].age;
                            } else {																	//если если кол-во соседей не равно 2 или 3, то клетка умирает
                                arr[i][j] = this.cloneCell(this.grid[i][j]);
                                arr[i][j].isAlive = false;
                            }
                        }
                    }
                }
                this.generation = ++ this.generation;
                this.grid = arr;

                this.countCells();

                return this.grid;
            }
        }

        if (typeof this.show != 'function') {
            Grid.prototype.show = function() {
                this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
                for (var i = 0; i < this.grid.length; i++) {
                    for (var j = 0; j < this.grid[i].length; j++) {
                        if (this.grid[i][j] instanceof Cell && this.grid[i][j].isAlive) {
                            this.context.fillStyle = "blue";
                            this.context.fillRect(this.dz * j, this.dz * i, this.dz, this.dz);
                        }
                        if (this.grid[i][j] instanceof Cell && ! this.grid[i][j].isAlive && this.showDead) {
                            this.context.fillStyle = "green";
                            this.context.fillRect(this.dz * j, this.dz * i, this.dz, this.dz);
                        }
                    }
                }

                this.context.fillStyle = this.textColor;
                this.context.font = this.font;
                this.context.fillText(this.generation, 15, 20);
                this.context.fillText(this.amountCells, 15, 40);
            }
        }

        if (typeof this.countCells != 'function') {
            Grid.prototype.countCells = function() {
                this.amountCells = 0;
                this.amountFemaleCells = 0;
                this.amountMaleCells = 0;
                for (let i = 0; i < this.grid.length; i++) {
                    for (let j = 0; j < this.grid[i].length; j++) {
                        if (this.grid[i][j] instanceof Cell && this.grid[i][j].isAlive) {
                            ++this.amountCells;
                            if (this.grid[i][j].sex == true) {
                                ++this.amountMaleCells;
                            } else {
                                ++this.amountFemaleCells;
                            }
                        }
                    }
                }
            }
        }

        if (typeof this.getAliveNeighbors != 'function') {
            Grid.prototype.getAliveNeighbors = function(x, y) {
                var above, below, left, right, amountAlives = 0, coords = new Map();

                if (y > 0) {
                    above = y - 1;
                } else {
                    above = this.grid.length - 1;
                }
                if (y < this.grid.length - 1) {
                    below = y + 1;
                } else {
                    below = 0;
                }
                if (x >0 ) {
                    left = x - 1;
                } else {
                    left = this.grid[y].length - 1;
                }
                if (x < this.grid[y].length - 1) {
                    right = x + 1;
                } else {
                    right = 0;
                }
                /*
                amountAlives += this.grid[y][left] instanceof Cell && this.grid[y][left].isAlive ? 1 : 0
                amountAlives += this.grid[y][right] instanceof Cell && this.grid[y][right].isAlive ? 1 : 0
                amountAlives += this.grid[above][x] instanceof Cell && this.grid[above][x].isAlive ? 1 : 0
                amountAlives += this.grid[below][x] instanceof Cell && this.grid[below][x].isAlive ? 1 : 0
                amountAlives += this.grid[above][left] instanceof Cell && this.grid[above][left].isAlive ? 1 : 0
                amountAlives += this.grid[below][right] instanceof Cell && this.grid[below][right].isAlive ? 1 : 0
                amountAlives += this.grid[above][right] instanceof Cell && this.grid[above][right].isAlive ? 1 : 0
                amountAlives += this.grid[below][left] instanceof Cell && this.grid[below][left].isAlive ? 1 : 0
                */

                if (this.grid[y][left] instanceof Cell && this.grid[y][left].isAlive) {
                    ++amountAlives;
                    //coords.push(this.grid[y][left]);
                    coords.set('sixthNeighbor', [left, y]);

                }

                if (this.grid[y][right] instanceof Cell && this.grid[y][right].isAlive) {
                    ++amountAlives;
                    //coords.push(this.grid[y][right]);
                    coords.set('secondNeighbor', [right, y]);
                }

                if (this.grid[above][x] instanceof Cell && this.grid[above][x].isAlive) {
                    ++amountAlives;
                    //coords.push(this.grid[above][x]);
                    coords.set('zerothNeighbor', [x, above]);
                }

                if (this.grid[below][x] instanceof Cell && this.grid[below][x].isAlive) {
                    ++amountAlives;
                    //coords.push(this.grid[below][x]);
                    coords.set('fourthNeighbor', [x, below]);
                }

                if (this.grid[above][left] instanceof Cell && this.grid[above][left].isAlive) {
                    ++amountAlives;
                    //coords.push(this.grid[above][left]);
                    coords.set('seventhNeighbor', [left, above]);
                }

                if (this.grid[below][right] instanceof Cell && this.grid[below][right].isAlive) {
                    ++amountAlives;
                    //coords.push(this.grid[below][right]);
                    coords.set('thirdNeighbor', [right, below]);
                }

                if (this.grid[above][right] instanceof Cell && this.grid[above][right].isAlive) {
                    ++amountAlives;
                    //coords.push(this.grid[above][right]);
                    coords.set('firstNeighbor', [right, above]);
                }

                if (this.grid[below][left] instanceof Cell && this.grid[below][left].isAlive) {
                    ++amountAlives;
                    //coords.push(this.grid[below][left]);
                    coords.set('fifthNeighbor', [left, below]);
                }

                var alives = new Map();
                alives.set('coords', coords);
                alives.set('amountAlives', amountAlives);

                return alives;
            }
        }



        if (typeof this.start != 'function') {
            Grid.prototype.start = function(interval) {
                this.speedTimer = setInterval(function() {
                    this.update()
                    this.show()
                }.bind(this), interval);
            }
        }

        if (typeof this.stop != 'function') {
            Grid.prototype.stop = function() {
                clearInterval(this.speedTimer);
            }
        }

        if (typeof this.setCanvas != 'function') {
            Grid.prototype.setCanvas = function(canvas) {
                this.canvas = canvas;
                this.context = this.canvas.getContext("2d");
                this.canvas.width = this.width * this.dz;
                this.canvas.height = this.height * this.dz;
                this.canvas.addEventListener('mouseup', function(e) {
                    var xClick = Math.floor((e.pageX - e.target.offsetLeft) / this.dz);
                    var yClick = Math.floor((e.pageY - e.target.offsetTop) / this.dz);
                    this.createGlider(xClick, yClick);
                    //this.createCell(xClick, yClick);
                    this.show();
                }.bind(this));
            }
        }

        if (typeof this.createCell != 'function') {
            Grid.prototype.createCell = function(x, y) {
                this.grid[y][x] = this.giveBirthToCell(x, y);
            }
        }

        if (typeof this.createGlider != 'function') {
            Grid.prototype.createGlider = function(x, y) {
                let x1, y1, x2, y2;

                if (x >= this.width) {
                    x = 0;
                }

                x1 = x + 1
                if (x1 == this.width) {
                    x1 = 0;
                } else if (x1 == this.width + 1) {
                    x1 = 1;
                }

                x2 = x + 2
                if (x2 == this.width) {
                    x2 = 0;
                } else if (x2 == this.width + 1) {
                    x2 = 1;
                } else if (x2 == this.width + 2) {
                    x2 = 2;
                }

                if (y >= this.height) {
                    y = 0;
                }

                y1 = y + 1
                if (y1 == this.height) {
                    y1 = 0;
                } else if (y1 == this.height + 1) {
                    y1 = 1;
                }

                y2 = y + 2
                if (y2 == this.height) {
                    y2 = 0;
                } else if (y2 == this.height + 1) {
                    y2 = 1;
                } else if (y2 == this.height + 2) {
                    y2 = 2;
                }

                this.grid[y][x] = this.giveBirthToCell(x, y);
                this.grid[y][x1] = this.giveBirthToCell(x1, y);
                this.grid[y][x2] = this.giveBirthToCell(x2, y);
                this.grid[y1][x] = this.giveBirthToCell(x, y1);
                this.grid[y2][x1] = this.giveBirthToCell(x1, y2);
            }
        }

    }	//Grid

    function Cell() {
        this.isAlive = true;
        this.age = 0;
        this.sex = false;
        this.x = 0;
        this.y = 0;

    }	//end Cell

    return new Grid(canvas);
})()	//end life



var list;
var start, conf;
var nameA = "Begin";
var nameB = "Stop";
var cnv;
var eraseButton;

window.onload = function() {
    cnv = document.getElementById("mycanvas");
    document.getElementById("myform").style.width = cnv.width - 10 + "px";
    conf = document.getElementById("configuration");
    conf.disabled = false;
    start = document.getElementById("start");
    start.value = nameA;
    list = document.getElementById("probability");
    list.disabled = false;
    var lifeSpeed = document.getElementById("life_speed");
    lifeSpeed.disabled = false;
    lifeSpeed.selectedIndex = 3;
    lifeSpeed.onchange = life.stop();
    eraseButton = document.getElementById("erase");
    eraseButton.disabled = false;
    eraseButton.onclick = function() {
        life.init(0);
        life.show()
    }

    var init = function() {
        life.init(list.value);
        life.setCanvas (cnv);
        life.show()

    };
    init();
    conf.onclick = init;
    list.onchange = init;
    start.onclick = function() {
        if (this.value == nameA) {
            list.disabled = true;
            conf.disabled = true;
            lifeSpeed.disabled = true;
            eraseButton.disabled = true;
            this.value = nameB;
            life.start(lifeSpeed.value);
        } else {
            list.disabled = false;
            conf.disabled = false;
            lifeSpeed.disabled = false;
            eraseButton.disabled = false;
            this.value = nameA;
            life.stop();
        }
    }
}