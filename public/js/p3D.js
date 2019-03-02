'use strict';
var pseudo3D = (function () {
    function Main() {
        //this.height = 512;
        //this.width = 1024;
        //this.font = "24px serif";
        //this.textColor = "red";
        //this.canvas;
        //this.context;
        //this.frameBuffer = [];
        //this.mapHeight = 16;
        //this.mapWidth = 16;
//        this.map = [
//            0,0,0,0,2,2,2,2,2,2,2,2,0,0,0,0,
//            1, , , , , , , , , , , , , , ,0,
//            1, , , , , , ,1,1,1,1,1, , , ,0,
//            1, , , , , ,0, , , , , , , , ,0,
//            0, , , , , ,0, , ,1,1,1,0,0,0,0,
//            0, , , , , ,3, , , , , , , , ,0,
//            0, , , ,1,0,0,0,0, , , , , , ,0,
//            0, , , ,3, , , ,1,1,1,0,0, , ,0,
//            5, , , ,4, , , ,0, , , , , , ,0,
//            5, , , ,4, , , ,1, , ,0,0,0,0,0,
//            0, , , , , , , ,1, , , , , , ,0,
//            2, , , , , , , ,1, , , , , , ,0,
//            0, , , , , , , ,0, , , , , , ,0,
//            0, ,0,0,0,0,0,0,0, , , , , , ,0,
//            0, , , , , , , , , , , , , , ,0,
//            0,0,0,2,2,2,2,2,2,2,2,0,0,0,0,0
//        ];
//        this.playerX = 3.456;
//        this.playerY = 2.345;
//        this.playerA = 1.523;	//player view direction
//        this.fov = Math.PI / 3.0;	//field of view
        //this.horizontalScaleRatio = 0.0;
        //this.verticalScaleRatio = 0.0;
        //this.imgDataFinalScene;
        //this.wallTextures = [];
        //this.amountWallTextures;
        //this.wallTextureSize;

//        if (typeof this.setCanvas !== 'function') {
//            Main.prototype.setCanvas = function (canvas) {
//                this.canvas = canvas;
//                this.context = this.canvas.getContext("2d");
//                this.canvas.width = this.width;
//                this.canvas.height = this.height;
//                this.horizontalScaleRatio = this.width / (this.mapWidth * 2);
//                this.verticalScaleRatio = this.height / this.mapHeight;
//                this.imgDataFinalScene = this.context.getImageData(0, 0, this.width, this.height);
//            };
//        }


        if (typeof this.getRandomInt !== 'function') {
            Main.prototype.getRandomInt = function (min, max) {
                return Math.floor(Math.random() * (max - min)) + min;
            };
        }




//        if (typeof this.playerViewTrace !== 'function') {
//            Main.prototype.playerViewTrace = function (player) {
//                let cx = 0.0;
//                let cy = 0.0;
//                let pix_x;
//                let pix_y;
//                let angle = 0.0;
//                let columnHeight;
//                let textureId;
//                let textureX;
//                let column;
//                let hitX;
//                let hitY;
//
//                for (let i = 0; i < this.width / 2; i++) {                                                      //step define amount of rays
//                    angle = player.direction - player.fov / 2 + player.fov * i / (this.width / 2);
//                    for (let t = 0; t < 20; t += 0.01) {                                                        //step of the ray
//                        cx = player.x + t * Math.cos(angle);                                                    //x coordinate of ray
//                        cy = player.y + t * Math.sin(angle);                                                    //y coordinate of ray
//                        pix_x = Math.trunc(cx * this.horizontalScaleRatio);                                     //scale to screen
//                        pix_y = Math.trunc(cy * this.verticalScaleRatio);                                       //scale to screen
//                        this.frameBuffer[pix_x + pix_y * this.width] = -6250336;                                //draw a pixel of the ray with grayish color
//                        textureId = this.map[Math.trunc(cx) + Math.trunc(cy) * this.mapWidth];
//                        if (textureId !== undefined) {                                                          //check intersection the ray with a wall (there is no wall if undefined)
//                            columnHeight = Math.trunc(this.height / (t * Math.cos(angle - player.direction)));
//                            hitX = cx - Math.floor(cx + 0.5);                                                   //get fractional part of x
//                            hitY = cy - Math.floor(cy + 0.5);                                                   //get fractional part of y
//                            textureX = hitX * this.wallTextureSize;
//                            if (Math.abs(hitY) > Math.abs(hitX)) {
//                                textureX = hitY * this.wallTextureSize;
//                            }
//                            if (textureX < 0)
//                                textureX += this.wallTextureSize;
//                            column = this.getColumnTexture(textureId, textureX, columnHeight);
//                            pix_x = Math.trunc(this.width / 2) + i;
//                            for (let j = 0; j < columnHeight; j++) {
//                                pix_y = j + Math.trunc(this.height / 2) - Math.trunc(columnHeight / 2);
//                                if (pix_y < 0 || pix_y > this.width)
//                                    continue;
//                                this.frameBuffer[pix_x + pix_y * this.width] = column[j];
//                            }
//                            break;
//                        }
//                    }
//                }
//            };
//        }

//        if (typeof this.getColumnTexture !== 'function') {
//            Main.prototype.getColumnTexture = function (textureId, textureX, columnHeight) {
//                let column = [];
//                let pixX;
//                let pixY;
//                for (let i = 0; i < columnHeight; i++) {
//                    pixX = textureId * this.wallTextureSize + Math.trunc(textureX);
//                    pixY = Math.trunc((i * this.wallTextureSize) / columnHeight);
//                    column[i] = this.wallTextures[pixX + pixY * this.wallTextureSize * this.amountWallTextures];
//                }
//
//                return column;
//            };
//        }


    }

    return new Main();
})();

var loop = (function () {
    function GameLoop() {
        this.lastTime = 0;
        this.callback = function() {};

        if (typeof this.start !== 'function') {
            GameLoop.prototype.start = function (callback) {
                this.callback = callback;
                requestAnimationFrame(this.frame);
            };
        }

        if (typeof this.frame !== 'function') {
            GameLoop.prototype.frame = function (time) {
                let seconds = (time - this.lastTime) / 1000;
                this.lastTime = time;
                if (seconds < 0.2)
                    this.callback(seconds);
                requestAnimationFrame(this.frame);
            }.bind(this);
        }
    }

    return new GameLoop();
})();

var controls = (function () {
    function Controls() {
        this.codes = { 37: 'left', 39: 'right', 38: 'forward', 40: 'backward' };
        this.states = { 'left': false, 'right': false, 'forward': false, 'backward': false };

        document.addEventListener('keydown', function(e) { this.onKey(true, e); }.bind(this), false);
        document.addEventListener('keyup', function(e) { this.onKey(false, e); }.bind(this), false);

        if (typeof this.onKey !== 'function') {
            Controls.prototype.onKey = function (val,e) {
                let state = this.codes[e.keyCode];
                if (typeof state === 'undefined')
                    return;
                this.states[state] = val;
                e.preventDefault && e.preventDefault();
                e.stopPropagation && e.stopPropagation();
            };
        }
    }

    return new Controls();
})();

var player = (function () {
    function Player(x, y, direction) {
        this.x = x || 3.456;
        this.y = y || 2.345;
        this.direction = direction || 1.523;    //player view direction
        this.weapon;
        this.paces = 0;
        this.circle = Math.PI * 2;
        this.fov = Math.PI / 3.0;               //field of view

        if (typeof this.rotate !== 'function') {
            Player.prototype.rotate = function (angle) {
                this.direction = (this.direction + angle + this.circle) % this.circle;
            };
        }

        if (typeof this.update !== 'function') {
            Player.prototype.update = function (controls, map, seconds) {
                if (controls.left)
                    this.rotate(-Math.PI * seconds);
                if (controls.right)
                    this.rotate(Math.PI * seconds);
                if (controls.forward)
                    this.move(map, 3 * seconds);
                if (controls.backward)
                    this.move(map, - 3 * seconds);
            };
        }

        if (typeof this.move !== 'function') {
            Player.prototype.move = function (map, distance) {
                let dx = Math.cos(this.direction) * distance;
                let dy = Math.sin(this.direction) * distance;
                if (map.walls[Math.trunc(this.x + dx) + Math.trunc(this.y) * map.width] === undefined)
                    this.x += dx;
                if (map.walls[Math.trunc(this.x) + Math.trunc(this.y + dy) * map.width] === undefined)
                    this.y += dy;
            };
        }
    }

    return new Player();
})();

function Bitmap(src, width, height) {
    this.image = new Image();
    this.image.src = src;
    this.width = width;
    this.height = height;
    this.packedImg;
}

var camera = (function () {
    function Camera() {
        this.height = 512;
        this.width = 1024;
        this.canvas;
        this.context;
        this.frameBuffer = [];
        this.imgDataFinalScene;
        this.map;
	this.amountWallTextures;
        this.wallTextureSize;
        this.wallTextures = [];
        this.mapPosition;
        this.projectionWidth;
        this.projectionHeight;
        this.projectionLeftX = 0;
        this.projectionRightX;
        this.projectionMiddleY;
        this.projectionTopY = 0;
        this.projectionBottomY;
        this.hScale;
        this.vScale;
        this.mapWinLeftX = 0;
        this.mapWinTopY = 0;
        this.walls = new Bitmap('/load/textures/1', 384, 64);
        this.skybox = new Bitmap('/load/textures/2', 2048, 1024);
        this.backgroundTexture = [];
        this.coneBuffer = [];

        if (typeof this.drawGradient !== 'function') {
            Camera.prototype.drawGradient = function () {
                for (let j = 0; j < this.height; j++) {
                    for (let i = 0; i < this.width; i++) {
                        this.frameBuffer[i + j * this.width] = this.packColor(
                            Math.trunc(255 * j / this.height), //red
                            Math.trunc(255 * i / this.width), //green
                            0, //blue
                            255															//alfa
                        );
                    }
                }
            };
        }

        if (typeof this.packColor !== 'function') {
            Camera.prototype.packColor = function (r, g, b, a) {
                return (a << 24) | (b << 16) | (g << 8) | r;
            };
        }

        if (typeof this.unpackColor !== 'function') {
            Camera.prototype.unpackColor = function (color) {
                return [(color >> 0) & 255, (color >> 8) & 255, (color >> 16) & 255, (color >> 24) & 255];
            };
        }

        if (typeof this.setCanvas !== 'function') {
            Camera.prototype.setCanvas = function (canvas) {
                this.canvas = canvas;
                this.context = this.canvas.getContext("2d");
                this.canvas.width = this.width;
                this.canvas.height = this.height;
                this.imgDataFinalScene = this.context.getImageData(0, 0, this.width, this.height);
            };
        }

        if (typeof this.setTextures !== 'function') {
            Camera.prototype.setTextures = function () {
                let self = this;

                this.amountWallTextures = Math.trunc(this.walls.width / this.walls.height);
                this.wallTextureSize = Math.trunc(this.walls.width / this.amountWallTextures);
                this.wallTextures = packTextures(this.walls.image);

                this.skybox.packedImg = packTextures(this.skybox.image);

                return;

                function packTextures(textures) {
                    let packedTextures = [];
                    let r, g, b, a;
                    let canvas = document.createElement('canvas');
                    canvas.height = textures.height;
                    canvas.width = textures.width;
                    canvas.getContext("2d").drawImage(textures, 0, 0, textures.width, textures.height);
                    let imgData = canvas.getContext("2d").getImageData(0, 0, textures.width, textures.height);

                    for (let j = 0; j < textures.height; j++) {
                        for (let i = 0; i < textures.width; i++) {
                            r = imgData.data[(i + j * textures.width) * 4 + 0];
                            g = imgData.data[(i + j * textures.width) * 4 + 1];
                            b = imgData.data[(i + j * textures.width) * 4 + 2];
                            a = imgData.data[(i + j * textures.width) * 4 + 3];
                           packedTextures[i + j * textures.width] = self.packColor(r, g, b, a);
                        }
                    }

                    return packedTextures;
                };
            };
        }

        if (typeof this.drawBackground !== 'function') {
            Camera.prototype.drawBackground = function (direction) {
                let width = this.skybox.width * (this.height / this.skybox.height) * 2;
                let left = Math.trunc((direction / (Math.PI * 2)) * + this.skybox.width);
                let pixX, pixY, hScale, vScale;

                hScale = this.skybox.width / this.projectionWidth;
                vScale = this.skybox.height / this.projectionHeight;

                for (let j = this.projectionTopY, v=0; j < this.projectionBottomY; j++, v++) {
                    for (let i = this.projectionLeftX, h=0; i < this.projectionRightX; i++, h++) {
                        if (left+i < this.skybox.width) {
                            //this.frameBuffer[i + j  * this.width] = this.skybox.packedImg[left + Math.trunc(h * hScale) + Math.trunc(v * vScale) * this.skybox.width];
                        this.frameBuffer[i + j  * this.width] = this.skybox.packedImg[left + i + j * this.skybox.width];
                        } else {
                            //this.frameBuffer[i + j  * this.width] = this.skybox.packedImg[left + Math.trunc(h * hScale) - this.skybox.width + Math.trunc(v * vScale) * this.skybox.width];
                        this.frameBuffer[i + j  * this.width] = this.skybox.packedImg[left + i - this.skybox.width + j * this.skybox.width];
                        }
                    }
                }


            };
        }

        if (typeof this.setMapPositionOnScreen !== 'function') {
            Camera.prototype.setMapPositionOnScreen = function (map, position) {
                this.mapPosition = position;
            }
        }

        if (typeof this.setMap !== 'function') {
            Camera.prototype.setMap = function (map, position) {
                this.mapPosition = position;
                this.map = map;
                if (this.mapPosition === 'onTop') {
                    this.projectionWidth = this.width;
                    this.projectionHeight = this.height / 2;
                    this.projectionLeftX = 0;
                    this.projectionRightX = this.width;
                    this.projectionMiddleY = Math.trunc(this.height * 3 / 4);
                    this.projectionTopY = this.height / 2;
                    this.projectionBottomY = this.height;
                    this.hScale = this.width / map.width;
                    this.vScale = this.height / (map.height * 2);
                    this.mapWinLeftX = 0;
                    this.mapWinTopY = 0;
                } else if (this.mapPosition === 'onBottom') {
                    this.projectionWidth = this.width;
                    this.projectionHeight = this.height / 2;
                    this.projectionLeftX = 0;
                    this.projectionRightX = this.width;
                    this.projectionMiddleY = Math.trunc(this.height / 4);
                    this.projectionTopY = 0;
                    this.projectionBottomY = this.height / 2;
                    this.hScale = this.width / map.width;
                    this.vScale = this.height / (map.height * 2);
                    this.mapWinLeftX = 0;
                    this.mapWinTopY = this.height / 2;
                } else if (this.mapPosition === 'onRight') {
                    this.projectionWidth = Math.trunc(this.width / 2);
                    this.projectionHeight = this.height;
                    this.projectionLeftX = 0;
                    this.projectionRightX = Math.trunc(this.width / 2);
                    this.projectionMiddleY = Math.trunc(this.height / 2);
                    this.projectionTopY = 0;
                    this.projectionBottomY = this.height;
                    this.hScale = this.width / (map.width * 2);
                    this.vScale = this.height / map.height;
                    this.mapWinLeftX = Math.trunc(this.width / 2);
                    this.mapWinTopY = 0;
                } else if (this.mapPosition === 'onLeft') {
                    this.projectionWidth = Math.trunc(this.width / 2);
                    this.projectionHeight = this.height;
                    this.projectionLeftX = Math.trunc(this.width / 2);
                    this.projectionRightX = this.width;
                    this.projectionMiddleY = Math.trunc(this.height / 2);
                    this.projectionTopY = 0;
                    this.projectionBottomY = this.height;
                    this.hScale = this.width / (map.width * 2);
                    this.vScale = this.height / map.height;
                    this.mapWinLeftX = 0;
                    this.mapWinTopY = 0;
                } else {
                    this.projectionWidth = this.width;
                    this.projectionHeight = this.height;
                    this.projectionLeftX = 0;
                    this.projectionRightX = this.width;
                    this.projectionMiddleY = Math.trunc(this.height / 2);
                    this.projectionTopY = 0;
                    this.projectionBottomY = this.height;
                    this.hScale = this.width / map.width;
                    this.vScale = this.height / map.height;
                    this.mapWinLeftX = 0;
                    this.mapWinTopY = 0;
                }
            };
        }

        if (typeof this.getMapPositionOnScreen !== 'function') {
            Camera.prototype.getMapPositionOnScreen = function () {
                return this.mapPosition;
            };
        }

        if (typeof this.getMap !== 'function') {
            Camera.prototype.getMap = function () {
                this.mapPosition = position;
                    return this.map;
            }
        }

        if (typeof this.drawPlayer !== 'function') {
            Camera.prototype.drawPlayer = function (player, map) {
                this.drawRect(player.x * this.width / (map.width), player.y * this.height / map.height, 5, 5, this.packColor(0, 0, 0, 255));
            };
        }

        if (typeof this.drawMap !== 'function') {
            Camera.prototype.drawMap = function () {
                let textureId, hScale, vScale;

                hScale = this.width / this.map.width;
                vScale = this.height / this.map.height;

                for (let j = 0; j < this.map.height; j++) {
                    for (let i = 0; i < this.map.width; i++) {
                        if (this.map.walls[i + j * this.map.width] === undefined) {
                            continue;
                        }
                        textureId = this.map.walls[i + j * this.map.width];
                        if (this.mapPosition === 'onLeft') {
                            this.drawRect(i * hScale / 2, j * vScale, vScale, hScale / 2, this.wallTextures[textureId * this.wallTextureSize]);
                        } else if (this.mapPosition === 'onRight') {
                            this.drawRect(i * hScale / 2 + this.width / 2, j * vScale, vScale, hScale / 2, this.wallTextures[textureId * this.wallTextureSize]);
                        } else if (this.mapPosition === 'onTop') {
                            this.drawRect(i * hScale, j * vScale / 2, vScale / 2, hScale, this.wallTextures[textureId * this.wallTextureSize]);
                        } else if (this.mapPosition === 'onBottom') {
                            this.drawRect(i * hScale, j * vScale / 2 + this.height / 2, vScale / 2, hScale, this.wallTextures[textureId * this.wallTextureSize]);
                        } else {
                            this.drawRect(i * hScale, j * vScale, vScale, hScale, this.wallTextures[textureId * this.wallTextureSize]);
                        }
                    }
                }
            };
        }

        if (typeof this.drawRect !== 'function') {
            Camera.prototype.drawRect = function (rectX, rectY, rectHeight, rectWidth, packedColor) {
                let cx;
                let cy;
                for (let j = 0; j < rectWidth; j++) {
                    for (let i = 0; i < rectHeight; i++) {
                        cx = Math.trunc(rectX + j);
                        cy = Math.trunc(rectY + i);
                        if (cx >= this.width || cy >= this.height)
                            continue;	//why?
                        this.frameBuffer[cx + cy * this.width] = packedColor;
                    }
                }
            };
        }

        if (typeof this.drawColumns !== 'function') {
            Camera.prototype.drawColumns = function (player) {
                let cx = 0.0, cy = 0.0, angle = 0.0;
                let pix_x, pix_y;
                let column, columnHeight;
                let textureId, textureX;
                let hitX, hitY;

                let leftEdge = player.direction - player.fov / 2;
                let angleBetweenRays = player.fov / this.projectionWidth;

                for (let i = 0; i < this.projectionWidth; i++) {                                                      //step define amount of rays
                    angle = leftEdge + angleBetweenRays * i;
                    for (let step = 0; step < 20; step += 0.01) {                                                        //step of the ray
                        cx = player.x + step * Math.cos(angle);                                                    //x coordinate of ray
                        cy = player.y + step * Math.sin(angle);                                                    //y coordinate of ray
                        if (this.mapPosition) {
                            pix_x = Math.trunc(cx * this.hScale + this.mapWinLeftX);                                     //scale to screen
                            pix_y = Math.trunc(cy * this.vScale + this.mapWinTopY);                                       //scale to screen
                            this.frameBuffer[pix_x + pix_y * this.width] = -6250336;                                //draw a pixel of the ray with grayish color
                        }
                        textureId = this.map.walls[Math.trunc(cx) + Math.trunc(cy) * this.map.width];
                        if (textureId !== undefined) {                                                          //check intersection the ray with a wall (there is no wall if undefined)
                            columnHeight = Math.trunc(this.projectionHeight / (step * Math.cos(angle - player.direction)));
                            hitX = cx - Math.floor(cx + 0.5);                                                   //get fractional part of x
                            hitY = cy - Math.floor(cy + 0.5);                                                   //get fractional part of y
                            textureX = hitX * this.wallTextureSize;
                            if (Math.abs(hitY) > Math.abs(hitX))
                                textureX = hitY * this.wallTextureSize;
                            if (textureX < 0)
                                textureX += this.wallTextureSize;
                            column = this.getColumnTexture(textureId, textureX, columnHeight);
                            pix_x = this.projectionLeftX + i;
                            for (let j = 0; j < columnHeight; j++) {
                                pix_y = j + this.projectionMiddleY - Math.trunc(columnHeight / 2);
                                if (pix_y >= this.projectionTopY && pix_y < this.projectionBottomY)
                                    this.frameBuffer[pix_x + pix_y * this.width] = column[j];
                            }
                            break;
                        }
                    }
                }
            };
        }

        if (typeof this.getColumnTexture !== 'function') {
            Camera.prototype.getColumnTexture = function (textureId, textureX, columnHeight) {
                let column = [];
                let pixX;
                let pixY;

                pixX = textureId * this.wallTextureSize + Math.trunc(textureX);
                for (let i = 0; i < columnHeight; i++) {
                    pixY = Math.trunc((i * this.wallTextureSize) / columnHeight);
                    column[i] = this.wallTextures[pixX + pixY * this.wallTextureSize * this.amountWallTextures];
                }

                return column;
            };
        }

        if (typeof this.fillColor !== 'function') {
            Camera.prototype.fillColor = function () {
                for (let j = 0; j < this.height; j++) {
                    for (let i = 0; i < this.width; i++) {
                        this.frameBuffer[i + j * this.width] = -1;
                    }
                }
            };
        }

	if (typeof this.show !== 'function') {
            Camera.prototype.show = function () {
                let rgba;
                let pointer;
                let imgData = this.imgDataFinalScene.data;

                for (let j = 0; j < this.height; j++) {
                    for (let i = 0; i < this.width; i++) {
                        pointer = i + j * this.width;
                        rgba = this.unpackColor(this.frameBuffer[pointer]);
                        pointer = 4 * pointer;
                        imgData[pointer] = rgba[0];
                        imgData[pointer + 1] = rgba[1];
                        imgData[pointer + 2] = rgba[2];
                        imgData[pointer + 3] = rgba[3];
                    }
                }
                this.context.putImageData(this.imgDataFinalScene, 0, 0);

            };
        }
    }

    return new Camera();
})();

var map = (function () {
    function Map() {
        this.width = 16;
        this.height = 16;
        this.walls = [
            2,0,4,0,4,0,4,0,4,0,4,0,4,0,4,0,
            0, , , , , , , , , , , , , , ,3,
            5, , , , , , , , , , , , , , ,0,
            0, , , , , , , , , , , , , , ,3,
            5, , , , , , , , , , , , , , ,0,
            0, , , , , , , , , , , , , , ,3,
            5, , , , , , , , , , , , , , ,0,
            0, , , , , , , , , , , , , , ,3,
            5, , , , , , , , , , , , , , ,0,
            0, , , , , , , , , , , , , , ,3,
            5, , , , , , , , , , , , , , ,0,
            0, , , , , , , , , , , , , , ,3,
            5, , , , , , , , , , , , , , ,0,
            0, , , , , , , , , , , , , , ,3,
            5, , , , , , , , , , , , , , ,0,
            0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1
        ];
        //this.position = 'onLeft';
        //this.y;


    }

    return new Map();
})();

if (document.readyState !== 'loading') {
    onload();
} else {
    document.addEventListener('DOMContentLoaded', onload);
}

function onload() {
    var cnv = document.getElementById("canvas");
    camera.setCanvas(cnv);
    var walls = document.getElementById("sky_daytime_blue");
    var img = new Image();
    img.src = walls.src;
    img.onload = function() {
        camera.setTextures();
        camera.setMap(map, 'onRight');
        loop.start(function frame(seconds) {
            player.update(controls.states, map, seconds);
            camera.fillColor();
            camera.drawBackground(player.direction);
            camera.drawMap();
            camera.drawColumns(player);
            //camera.drawPlayer(player);

            camera.show();
        });
    }


}

