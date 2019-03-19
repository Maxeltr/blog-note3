'use strict';
var pseudo3D = (function () {
    function Main() {

        if (typeof this.getRandomInt !== 'function') {
            Main.prototype.getRandomInt = function (min, max) {
                return Math.floor(Math.random() * (max - min)) + min;
            };
        }
    }

    return new Main();
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

    function Player(x, y, direction) {
        this.x = x || 3.456;
        this.y = y || 2.345;
        this.direction = direction || 1.523;    //player view direction degrees - rad, 0-0, 90-‪1.570796‬, 180-‪3.141593‬, 270-‪4.712389‬
        this.weapon;
        this.paces = 0;
        this.circle = Math.PI * 2;
        this.fov = Math.PI / 3.0;               //field of view
        this.sizeRadius = 0.1;		//calculate in depence of map size

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
                let moveDirection = this.direction;

                if (distance < 0)
                    moveDirection += Math.PI;

                if (map.checkCollisionsWithWalls(this.x + dx, this.y, moveDirection, this.sizeRadius))
                    this.x += dx;
                if (map.checkCollisionsWithWalls(this.x, this.y + dy, moveDirection, this.sizeRadius))
                    this.y += dy;

                this.paces += distance;
            };
        }
    }

    var player = new Player();

    function Sprite(x, y, textureId) {
        this.x = 0.0 || x;
        this.y = 0.0 || y;
        this.textureId = textureId || 0;
        this.distanceToPlayer;
        this.sizeRadius = 0.1;		//calculate in depence of map size
        this.precision = 0.001;
    }

    function Npc(x, y, textureId, direction) {
        Sprite.apply(this, arguments);
        this.direction = direction || 1.523;
        this.weapon;
        this.paces = 0;
        this.circle = Math.PI * 2;
        this.fov = Math.PI / 3.0;
        this.ray;
        this.waypoint;
    }

    Npc.prototype = Object.create(Sprite.prototype);
    Npc.prototype.constructor = Npc;

    Npc.prototype.move = function (map, distance) {
        let dx = Math.cos(this.direction) * distance;
        let dy = Math.sin(this.direction) * distance;
        let moveDirection = this.direction;
        let prevX = this.x;
        let prevY = this.y;

        if (distance < 0)
            moveDirection += Math.PI;

        if (map.checkCollisionsWithWalls(this.x + dx, this.y, moveDirection, this.sizeRadius))
            this.x += dx;
        if (map.checkCollisionsWithWalls(this.x, this.y + dy, moveDirection, this.sizeRadius))
            this.y += dy;

        if (this.x > prevX || this.y > prevY)
            this.paces += Math.abs(distance);
    };

    Npc.prototype.update = function (map, seconds) {
        this.ray = map.castRay(this, this.direction, 0.3, true);
        if (this.ray.distance < 0.9) {
            this.direction = this.getRandom(0, 6);
        } else {
            this.move(map, 3 * seconds);
        }


    }

    Npc.prototype.getRandomInt = function (min, max) {
        return Math.floor(Math.random() * (max - min)) + min;
    }

    Npc.prototype.getRandom = function (min, max) {
        return Math.random() * (max - min) + min;
    }

    function Monster () {
        Npc.apply(this, arguments);
        this.sizeRadius = 0.3;
    }

    Monster.prototype = Object.create(Npc.prototype);
    Monster.prototype.constructor = Monster;

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
        this.amountSpriteTextures;
        this.spriteTextureSize;
        this.mapPosition;
        this.projectionWidth;
        this.projectionHeight;
        this.projectionLeftX = 0;
        this.projectionRightX;
        this.projectionMiddleY;
        this.projectionTopY = 0;
        this.projectionBottomY;
        this.hMapScaleRatio;
        this.vMapScaleRatio;
        this.mapWinLeftX = 0;
        this.mapWinTopY = 0;
        this.wallTextures = new Bitmap('/load/textures/1', 384, 64);
        this.skyboxTextures = new Bitmap('/load/textures/2', 2048, 1024);
        this.spriteTextures = new Bitmap('/load/textures/3', 256, 64);
        this.knifeTexture = new Bitmap('/load/textures/4', 319, 320);
        this.weaponTextures = [this.knifeTexture];
        this.showRayOnMap = true;

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
                this.context.msImageSmoothingEnabled = false;
                this.context.mozImageSmoothingEnabled = false;
                this.context.imageSmoothingEnabled = false;
                this.canvas.width = this.width;
                this.canvas.height = this.height;
                this.imgDataFinalScene = this.context.getImageData(0, 0, this.width, this.height);
            };
        }

        if (typeof this.setTextures !== 'function') {
            Camera.prototype.setTextures = function (callback) {
                let self = this;

                let loadedCounter = 0;
                this.wallTextures.image.onload = this.skyboxTextures.image.onload =
                        this.spriteTextures.image.onload = this.knifeTexture.image.onload =  function() {
                    loadedCounter++;
                    if (loadedCounter === 4) {
                        self.amountWallTextures = Math.trunc(self.wallTextures.width / self.wallTextures.height);
                        self.wallTextureSize = self.wallTextures.height;
                        self.wallTextures.packedImg = packTextures(self.wallTextures.image);

                        self.skyboxTextures.packedImg = packTextures(self.skyboxTextures.image);
                        self.weaponTextures[0].packedImg = packTextures(self.weaponTextures[0].image);

                        self.amountSpriteTextures = self.spriteTextures.width / self.spriteTextures.height;
			self.spriteTextureSize = self.spriteTextures.height;
                        self.spriteTextures.packedImg = packTextures(self.spriteTextures.image);

                        callback();
                    }
                };

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
                //let width = this.skyboxTextures.width * (this.height / this.skyboxTextures.height) * 2;
                let left = Math.trunc((direction / (Math.PI * 2)) * + this.skyboxTextures.width);
                let top = (this.skyboxTextures.height - this.projectionHeight) / 2;

                for (let j = this.projectionTopY, v=0; j < this.projectionBottomY; j++, v++) {
                    for (let i = this.projectionLeftX, h=0; i < this.projectionRightX; i++, h++) {
                        if ((left+h) < this.skyboxTextures.width) {
                            this.frameBuffer[i + j  * this.width] = this.skyboxTextures.packedImg[left + h + (v + top) * this.skyboxTextures.width];
                        } else {
                            this.frameBuffer[i + j  * this.width] = this.skyboxTextures.packedImg[left + h - this.skyboxTextures.width + (v + top) * this.skyboxTextures.width];
                        }
                    }
                }


            };
        }

        if (typeof this.setPlayer !== 'function') {
            Camera.prototype.setPlayer = function (player) {

            };
        }

        if (typeof this.setMapPositionOnScreen !== 'function') {
            Camera.prototype.setMapPositionOnScreen = function (map, position) {
                this.mapPosition = position;
            };
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
                    this.hMapScaleRatio = this.width / map.width;
                    this.vMapScaleRatio = this.height / (map.height * 2);
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
                    this.hMapScaleRatio = this.width / map.width;
                    this.vMapScaleRatio = this.height / (map.height * 2);
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
                    this.hMapScaleRatio = this.width / (map.width * 2);
                    this.vMapScaleRatio = this.height / map.height;
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
                    this.hMapScaleRatio = this.width / (map.width * 2);
                    this.vMapScaleRatio = this.height / map.height;
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
                    this.hMapScaleRatio = this.width / map.width;
                    this.vMapScaleRatio = this.height / map.height;
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

        if (typeof this.drawSpritesOnMap !== 'function') {
            Camera.prototype.drawSpritesOnMap = function (sprites) {
                for (let i = 0; i < sprites.length; i++){
                    this.drawRect(sprites[i].x * this.projectionWidth / (this.map.width) + this.mapWinLeftX, sprites[i].y * this.projectionHeight / this.map.height + this.mapWinTopY, 6, 6, this.packColor(255, 0, 0, 255));
                }
            };
        }

        if (typeof this.drawSprites !== 'function') {
            Camera.prototype.drawSprites = function (sprites, player, depthBuffer) {
                sprites.sort(function(spriteA, spriteB) {
                    return spriteB.distanceToPlayer - spriteA.distanceToPlayer;
                });
                for (let i = 0; i < sprites.length; i++) {
                    sprites[i].distanceToPlayer = Math.sqrt(Math.pow(player.x - sprites[i].x, 2) + Math.pow(player.y - sprites[i].y, 2));
                    camera.drawSprite(sprites[i], player, depthBuffer);
                    if (this.showRayOnMap) {
                        this.drawRayOnMap(sprites[i].ray);
                    }
                }

            }
        }

        if (typeof this.drawSprite !== 'function') {
            Camera.prototype.drawSprite = function (sprite, player, depthBuffer) {
                let spriteDirection, spriteProjectionSize, h_offset, v_offset, pixel, spriteScaleRatio, spriteOffset;

                spriteDirection = Math.atan2(sprite.y - player.y, sprite.x - player.x);						// absolute direction from the player to the sprite (in radians)
                while (spriteDirection - player.direction >  Math.PI)
                    spriteDirection -= 2 * Math.PI; 													// remove unncesessary periods from the relative direction
                while (spriteDirection - player.direction < - Math.PI)
                    spriteDirection += 2 * Math.PI;
                spriteProjectionSize = Math.min(500, Math.trunc(this.projectionHeight / sprite.distanceToPlayer));
                h_offset = Math.trunc((spriteDirection - player.direction) * this.projectionWidth / player.fov + this.projectionWidth / 2 - spriteProjectionSize / 2);
                v_offset = this.projectionMiddleY - Math.trunc(spriteProjectionSize / 2);
                spriteScaleRatio = this.spriteTextureSize/spriteProjectionSize;
                spriteOffset = sprite.textureId * this.spriteTextureSize;

                for (let i = 0; i < spriteProjectionSize; i++) {
                    if ((h_offset + i) < 0 || depthBuffer[h_offset + i] < sprite.distanceToPlayer) continue;
                    if ((h_offset + i) >= this.projectionWidth) break;
                    for (let j = 0; j < spriteProjectionSize; j++) {
                        if ((v_offset + j) < 0)  continue;
                        if ((v_offset + j) >= this.projectionBottomY)  break;
                        pixel = this.spriteTextures.packedImg[Math.trunc(i * spriteScaleRatio) + spriteOffset + Math.trunc(j * spriteScaleRatio) * this.spriteTextures.width];
                        if (this.unpackColor(pixel)[3] > 128)
                            this.frameBuffer[this.projectionLeftX + h_offset + i + (v_offset + j) * this.width] = pixel;
                    }
                }
            };
        }

        if (typeof this.drawMap !== 'function') {
            Camera.prototype.drawMap = function () {
                let textureId;

                for (let j = 0; j < this.map.height; j++) {
                    for (let i = 0; i < this.map.width; i++) {
                        if (this.map.walls[i + j * this.map.width] === undefined) {
                            continue;
                        }
                        textureId = this.map.walls[i + j * this.map.width];
                        if (this.mapPosition === 'onLeft') {
                            this.drawRect(i * this.hMapScaleRatio, j * this.vMapScaleRatio, this.vMapScaleRatio, this.hMapScaleRatio, this.wallTextures.packedImg[textureId * this.wallTextureSize]);
                        } else if (this.mapPosition === 'onRight') {
                            this.drawRect(i * this.hMapScaleRatio + this.mapWinLeftX, j * this.vMapScaleRatio, this.vMapScaleRatio, this.hMapScaleRatio, this.wallTextures.packedImg[textureId * this.wallTextureSize]);
                        } else if (this.mapPosition === 'onTop') {
                            this.drawRect(i * this.hMapScaleRatio, j * this.vMapScaleRatio, this.vMapScaleRatio, this.hMapScaleRatio, this.wallTextures.packedImg[textureId * this.wallTextureSize]);
                        } else if (this.mapPosition === 'onBottom') {
                            this.drawRect(i * this.hMapScaleRatio, j * this.vMapScaleRatio + this.mapWinTopY, this.vMapScaleRatio, this.hMapScaleRatio, this.wallTextures.packedImg[textureId * this.wallTextureSize]);
                        } else {
                            this.drawRect(i * this.hMapScaleRatio, j * this.vMapScaleRatio, this.vMapScaleRatio, this.hMapScaleRatio, this.wallTextures.packedImg[textureId * this.wallTextureSize]);
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

        if (typeof this.drawWeapon !== 'function') {
            Camera.prototype.drawWeapon = function (weaponId, paces) {
                let pixel, periodY = 4, periodX = 2, amplitudeY = 6,amplitudeX = 6, initialX = 0.66, initialY = 0.6;
                let scale = (this.width + this.height) / 1700;
                let scaledWeaponTextureHeight = Math.trunc(this.weaponTextures[weaponId].height/scale);
                let scaledWeaponTextureWidth = Math.trunc(this.weaponTextures[weaponId].width/scale);

                let bobX = Math.cos(paces * periodX) * scale * amplitudeX;
                let bobY = Math.sin(paces * periodY) * scale * amplitudeY;
                let left = Math.trunc(this.projectionLeftX + this.projectionWidth - (scaledWeaponTextureWidth * initialX + amplitudeX * scale) + bobX);
                let top = Math.trunc(this.projectionTopY + this.projectionHeight - (scaledWeaponTextureHeight * initialY + amplitudeY * scale) + bobY);

                for (let j = 0; j < scaledWeaponTextureHeight; j++) {
                    if ((j + top) > this.projectionBottomY) break;
                    for (let i = 0; i < scaledWeaponTextureWidth; i++) {
                        if ((i + left) > this.projectionRightX) break;
                        pixel = this.weaponTextures[weaponId].packedImg[Math.trunc(i*scale) + Math.trunc(j*scale) * this.weaponTextures[weaponId].width];
                        if (this.unpackColor(pixel)[3] > 128)
                            this.frameBuffer[i + left  + (j + top) * this.width] = pixel;
                    }
                }
            };
        }

        if (typeof this.drawColumns !== 'function') {
            Camera.prototype.drawColumns = function (player, map) {
                let cx = 0.0, cy = 0.0, angle = 0.0;
                let pix_x, pix_y, ray;
                let column, columnHeight;
                let textureId, textureX;
                let hitX, hitY;
                let leftEdge, distance, depthBuffer = [];

                leftEdge = player.direction - player.fov / 2;
                let angleBetweenRays = player.fov / this.projectionWidth;

                for (let i = 0; i < this.projectionWidth; i++) {                                                      //step define amount of rays
                    angle = leftEdge + angleBetweenRays * i;
                    ray = map.castRay(player, angle, 0.01, true);
                    depthBuffer[i] = ray.distance;
                    columnHeight = Math.min(1000, Math.trunc(this.projectionHeight / ray.distance));
                    textureX = this.getTextureX(ray.x, ray.y);
                    column = this.getColumnTexture(ray.barrier, textureX, columnHeight);
                    pix_x = this.projectionLeftX + i;
                    for (let j = 0; j < columnHeight; j++) {
                        pix_y = j + this.projectionMiddleY - Math.trunc(columnHeight / 2);
                        if (pix_y >= this.projectionTopY && pix_y < this.projectionBottomY)
                            this.frameBuffer[pix_x + pix_y * this.width] = column[j];
                    }
                    if (this.showRayOnMap) {
                        this.drawRayOnMap(ray);
                    }
                }

                return depthBuffer;
            };
        }

        if (typeof this.drawRayOnMap !== 'function') {
            Camera.prototype.drawRayOnMap = function (ray) {
                let x, y;

                for (let j = 0; j < ray.trace.length; j++) {
                    x = Math.trunc(ray.trace[j].x * this.hMapScaleRatio + this.mapWinLeftX);                                     //scale to screen
                    y = Math.trunc(ray.trace[j].y * this.vMapScaleRatio + this.mapWinTopY);                                       //scale to screen
                    this.frameBuffer[x + y * this.width] = -6250336;                                //draw a pixel of the ray with grayish color
                }
            }
        }

        if (typeof this.getTextureX !== 'function') {
            Camera.prototype.getTextureX = function (x, y) {
                let hitX, hitY, textureX;

                hitX = x - Math.floor(x + 0.5);                                                   //get fractional part of x
                hitY = y - Math.floor(y + 0.5);                                                   //get fractional part of y
                textureX = hitX * this.wallTextureSize;
                if (Math.abs(hitY) > Math.abs(hitX))
                    textureX = hitY * this.wallTextureSize;
                if (textureX < 0)
                    textureX += this.wallTextureSize;

                return textureX;
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
                    column[i] = this.wallTextures.packedImg[pixX + pixY * this.wallTextureSize * this.amountWallTextures];
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

        if (typeof this.render !== 'function') {
            Camera.prototype.render = function (player, monsters, map) {
                this.fillColor();
                this.drawBackground(player.direction);
                this.drawMap();
                let depthBuffer = this.drawColumns(player, map);
                //camera.drawPlayer(player);
                this.drawSpritesOnMap(monsters);
                this.drawSprites(monsters, player, depthBuffer);
                this.drawWeapon(0, player.paces);
                this.show(debug.message);
            }
        }

	if (typeof this.show !== 'function') {
            Camera.prototype.show = function (message) {
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

                if (message) {
                    this.context.fillStyle = 'red';
                    this.context.font = "14px monospace";
                    this.context.fillText(message, 15, 20);
                }
            };
        }
    }

    return new Camera();
})();

    function DebugMessage(message) {
        this.message = message;

    }
    var debug = new DebugMessage('test');

    function Map() {
        this.width = 16;
        this.height = 16;
        this.walls = [
            2,0,4,0,4,0,4,0,4,0,4,0,4,0,4,0,
            0, , , , , , , , , , , , , , ,3,
            5, , , ,2, , , , ,2, , , , , ,0,
            0, , , ,2,2,2,2, ,2, , , ,2,2,3,
            5, , , ,2, , , , ,2, , , ,2, ,0,
            0, , , ,2, ,2,2,2,2, , , ,2, ,3,
            5, , , ,2, , , , ,2, , , ,2, ,0,
            0, , , ,2,2,2,2, ,2,2,2, ,2, ,3,
            5, , , ,2, , , , ,2, , , , , ,0,
            0, , , ,2, , , , ,2, ,2,2,2,2,3,
            5, , , ,2, , , , ,2, , , , , ,0,
            0, , , ,2, , , , ,2, , , , , ,3,
            5, , , ,2, , , , ,2, , , , , ,0,
            0, , , , , , , , ,2, , , , , ,3,
            5, , , ,2, , , , , , , , , , ,0,
            0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1
        ];
    }

    Map.prototype.isEmptyCell = function (x, y) {
        return this.walls[Math.trunc(x) + Math.trunc(y) * map.width] === undefined;
    };

    Map.prototype.checkCollisionsWithWalls = function (x, y, direction, radius) {	//degrees - rad, 0-0, 90-‪1.570796‬, 180-‪3.141593‬, 270-‪4.712389‬
        if (direction >= 0 && direction <= 1.571) {
            return this.isEmptyCell(x + radius, y + radius);
        } else if (direction > 1.571 && direction <= 3.142) {
            return this.isEmptyCell(x - radius, y + radius);
        } else if (direction > 3.142 && direction <= 4.712) {
            return this.isEmptyCell(x - radius, y - radius);
        } else if (direction > 4.712 && direction <= 6.2832) {
            return this.isEmptyCell(x + radius, y - radius);
        } else {
            return this.checkCollisionsWithWalls(x, y, (direction + (Math.PI * 2)) % (Math.PI * 2), radius);
        }
    }

    Map.prototype.castRay = function (player, angle, step, saveTrace) {
        let textureId, cx, cy, pix_x, pix_y, distance, steps;
        let increment = step || 0.01;
        let trace = [];

        for (steps = 0; steps < 20; steps += increment) {                                                        //step of the ray
            cx = player.x + steps * Math.cos(angle);                                                    //x coordinate of ray
            cy = player.y + steps * Math.sin(angle);                                                    //y coordinate of ray
            if (saveTrace) {
                trace.push({x: cx, y: cy});
            }
            textureId = this.walls[Math.trunc(cx) + Math.trunc(cy) * this.width];
            if (textureId !== undefined) break;
        }
        distance = steps * Math.cos(angle - player.direction);

        return {x: cx, y: cy, distance: distance, barrier: textureId, trace: trace};
    }

var map = new Map();

if (document.readyState !== 'loading') {
    startGame();
} else {
    document.addEventListener('DOMContentLoaded', startGame);
}

function startGame() {
    let depthBuffer;
    let sprites = [new Sprite(4.1, 5.5, 2), new Sprite(2.5, 5.5, 2), new Sprite(1.5, 6.5, 1), new Sprite(1.4, 9.9, 0)];
//let monster = new Monster(6, 6, 0, 3.5);
let monsters = [new Monster(6, 6, 0, 3.5), new Monster(6, 6, 0, 3.5), new Monster(6, 6, 0, 3.5), new Monster(6, 6, 0, 3.5), new Monster(6, 6, 0, 3.5), new Monster(6, 6, 0, 3.5)];

    var cnv = document.getElementById("canvas");
    camera.setCanvas(cnv);
    camera.setTextures(gameLoop);
    camera.setMap(map, 'onRight');

    function gameLoop() {

        loop.start(function frame(seconds) {
            player.update(controls.states, map, seconds);
            for (let i = 0; i < monsters.length; i++) {
                monsters[i].update(map, seconds);
            }
            //monster.update(map, seconds);

            camera.render(player, monsters, map);
            /*camera.fillColor();
            camera.drawBackground(player.direction);
            camera.drawMap();
            depthBuffer = camera.drawColumns(player);
            //camera.drawPlayer(player);
            camera.drawSpritesOnMap(sprites);
            camera.drawSprites(sprites, player, depthBuffer);

            camera.show();*/
        });
    }

}

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