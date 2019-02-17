'use strict';
var pseudo3D = (function () {
    function Main() {
        this.height = 512;
        this.width = 1024;
        this.font = "24px serif";
        this.textColor = "red";
        this.canvas;
        this.context;
        this.frameBuffer = [];
        this.mapHeight = 16;
        this.mapWidth = 16;
        this.map = [
            0,0,0,0,2,2,2,2,2,2,2,2,0,0,0,0,
            1, , , , , , , , , , , , , , ,0,
            1, , , , , , ,1,1,1,1,1, , , ,0,
            1, , , , , ,0, , , , , , , , ,0,
            0, , , , , ,0, , ,1,1,1,0,0,0,0,
            0, , , , , ,3, , , , , , , , ,0,
            0, , , ,1,0,0,0,0, , , , , , ,0,
            0, , , ,3, , , ,1,1,1,0,0, , ,0,
            5, , , ,4, , , ,0, , , , , , ,0,
            5, , , ,4, , , ,1, , ,0,0,0,0,0,
            0, , , , , , , ,1, , , , , , ,0,
            2, , , , , , , ,1, , , , , , ,0,
            0, , , , , , , ,0, , , , , , ,0,
            0, ,0,0,0,0,0,0,0, , , , , , ,0,
            0, , , , , , , , , , , , , , ,0,
            0,0,0,2,2,2,2,2,2,2,2,0,0,0,0,0
        ];
        this.playerX = 3.456;
        this.playerY = 2.345;
        this.playerA = 1.523;	//player view direction
        this.fov = Math.PI / 3.0;	//field of view
        this.horizontalScaleRatio = 0.0;
        this.verticalScaleRatio = 0.0;
        this.imgDataFinalScene;
        this.wallTextures = [];
        this.amountWallTextures;
        this.wallTextureSize;

        if (typeof this.setCanvas !== 'function') {
            Main.prototype.setCanvas = function (canvas) {
                this.canvas = canvas;
                this.context = this.canvas.getContext("2d");
                this.canvas.width = this.width;
                this.canvas.height = this.height;
                this.horizontalScaleRatio = this.width / (this.mapWidth * 2);
                this.verticalScaleRatio = this.height / this.mapHeight;
                this.imgDataFinalScene = this.context.getImageData(0, 0, this.width, this.height);
            };
        }

        if (typeof this.setTextures !== 'function') {
            Main.prototype.setTextures = function (textures) {
                let canvas = document.createElement('canvas');
                canvas.height = textures.height;
                canvas.width = textures.width;
                canvas.getContext("2d").drawImage(textures, 0, 0, textures.width, textures.height);
                let imgData = canvas.getContext("2d").getImageData(0, 0, textures.width, textures.height);

                for (let j = 0; j < textures.height; j++) {
                    for (let i = 0; i < textures.width; i++) {
                        let r = imgData.data[(i + j * textures.width) * 4 + 0];
                        let g = imgData.data[(i + j * textures.width) * 4 + 1];
                        let b = imgData.data[(i + j * textures.width) * 4 + 2];
                        let a = imgData.data[(i + j * textures.width) * 4 + 3];
                        this.wallTextures[i + j * textures.width] = this.packColor(r, g, b, a);
                    }
                }
                this.amountWallTextures = Math.trunc(textures.width / textures.height);
                this.wallTextureSize = Math.trunc(textures.width / this.amountWallTextures);
            };
        }

        if (typeof this.getRandomInt !== 'function') {
            Main.prototype.getRandomInt = function (min, max) {
                return Math.floor(Math.random() * (max - min)) + min;
            };
        }

        if (typeof this.packColor !== 'function') {
            Main.prototype.packColor = function (r, g, b, a) {
                return (a << 24) | (b << 16) | (g << 8) | r;
            };
        }

        if (typeof this.unpackColor !== 'function') {
            Main.prototype.unpackColor = function (color) {
                return [(color >> 0) & 255, (color >> 8) & 255, (color >> 16) & 255, (color >> 24) & 255];
            };
        }

        if (typeof this.drawGradient !== 'function') {
            Main.prototype.drawGradient = function () {
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

        if (typeof this.fillColor !== 'function') {
            Main.prototype.fillColor = function () {
                for (let j = 0; j < this.height; j++) {
                    for (let i = 0; i < this.width; i++) {
                        this.frameBuffer[i + j * this.width] = this.packColor(255, 255, 255, 255);
                    }
                }
            };
        }

        if (typeof this.drawMap !== 'function') {
            Main.prototype.drawMap = function () {
                let rectWidth = this.horizontalScaleRatio;
                let rectHeight = this.verticalScaleRatio;
                let textureId;

                for (let j = 0; j < this.mapHeight; j++) {
                    for (let i = 0; i < this.mapWidth; i++) {
                        if (this.map[i + j * this.mapWidth] === undefined) {
                            continue;
                        }
                        textureId = this.map[i + j * this.mapWidth];
                        this.drawRect(i * this.horizontalScaleRatio, j * this.verticalScaleRatio, rectHeight, rectWidth, this.wallTextures[textureId * this.wallTextureSize]);

                    }
                }
            };
        }

        if (typeof this.drawRect !== 'function') {
            Main.prototype.drawRect = function (rectX, rectY, rectHeight, rectWidth, packedColor) {
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

        if (typeof this.show !== 'function') {
            Main.prototype.show = function () {
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


        if (typeof this.drawPlayer !== 'function') {
            Main.prototype.drawPlayer = function () {
                this.drawRect(this.playerX * this.horizontalScaleRatio, this.playerY * this.verticalScaleRatio, 5, 5, this.packColor(255, 255, 255, 255));
            };
        }

        if (typeof this.playerViewTrace !== 'function') {
            Main.prototype.playerViewTrace = function () {
                let cx = 0.0;
                let cy = 0.0;
                let pix_x;
                let pix_y;
                let angle = 0.0;
                let columnHeight;
                let textureId;
                let textureX;

                for (let i = 0; i < this.width / 2; i++) {
                    angle = this.playerA - this.fov / 2 + this.fov * i / (this.width / 2);
                    for (let t = 0; t < 20; t += 0.01) {
                        cx = this.playerX + t * Math.cos(angle);
                        cy = this.playerY + t * Math.sin(angle);
                        pix_x = Math.trunc(cx * this.horizontalScaleRatio);
                        pix_y = Math.trunc(cy * this.verticalScaleRatio);
                        this.frameBuffer[pix_x + pix_y * this.width] = this.packColor(160, 160, 160, 255);

                        if (this.map[Math.trunc(cx) + Math.trunc(cy) * this.mapWidth] !== undefined) {
                            textureId = this.map[Math.trunc(cx) + Math.trunc(cy) * this.mapWidth];
                            columnHeight = Math.trunc(this.height / (t * Math.cos(angle - this.playerA)));

                            //this.drawRect(this.width / 2 + i, this.height / 2 - columnHeight / 2, columnHeight, 1, this.wallTextures[textureId * this.wallTextureSize]);
                            let hitX = cx - Math.floor(cx + 0.5);
                            let hitY = cy - Math.floor(cy + 0.5);
                            textureX = hitX * this.wallTextureSize;
                            if (Math.abs(hitY) > Math.abs(hitX)) {
                                textureX = hitY * this.wallTextureSize;
                            }
                            if (textureX < 0)
                                textureX += this.wallTextureSize;
                            let column = this.getColumnTexture(textureId, textureX, columnHeight);
                            pix_x = Math.trunc(this.width / 2) + i;
                            for (let j = 0; j < columnHeight; j++) {
                                pix_y = j + Math.trunc(this.height / 2) - Math.trunc(columnHeight / 2);
                                if (pix_y < 0 || pix_y > this.width)
                                    continue;
                                this.frameBuffer[pix_x + pix_y * this.width] = column[j];
                            }
                            break;
                        }
                    }
                }
            };
        }

        if (typeof this.getColumnTexture !== 'function') {
            Main.prototype.getColumnTexture = function (textureId, textureX, columnHeight) {
                let column = [];
                let pixX;
                let pixY;
                for (let i = 0; i < columnHeight; i++) {
                    pixX = textureId * this.wallTextureSize + Math.trunc(textureX);
                    pixY = Math.trunc((i * this.wallTextureSize) / columnHeight);
                    column[i] = this.wallTextures[pixX + pixY * this.wallTextureSize * this.amountWallTextures];
                }

                return column;
            };
        }

        if (typeof this.animate !== 'function') {
            Main.prototype.animate = function () {
                if (! this.speedTimer) {
                    this.speedTimer = setInterval(function () {
                        this.playerA += 2 * Math.PI / 360;

                        this.fillColor();
                        this.drawMap();
                        this.drawPlayer();
                        this.playerViewTrace();
                        this.show();
                        if (this.amountFrames >= 360) {
                            clearInterval(this.speedTimer);
                        }
                        this.amountFrames++;

                    }.bind(this), 0.015);
                }
            };
        }
        this.amountFrames = 0;
        this.speedTimer;

    }

    return new Main();
})();

if (document.readyState !== 'loading') {
    onload();
} else {
    document.addEventListener('DOMContentLoaded', onload);
}

function onload() {
    var cnv = document.getElementById("canvas");
    pseudo3D.setCanvas(cnv);
    var textures = document.getElementById("textures");
    var img = new Image();
    img.src = textures.src;
    img.onload = function() { 
        pseudo3D.setTextures(img); 
        pseudo3D.drawGradient();
        pseudo3D.fillColor();
        pseudo3D.drawMap();
        pseudo3D.drawPlayer();
        pseudo3D.playerViewTrace();
        pseudo3D.show();
        pseudo3D.animate();
    };
    
    
    

}

