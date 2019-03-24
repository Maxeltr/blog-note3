'use strict';

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

    function Sprite(x, y, textures, frames) {
        this.x = 0.0 || x;
        this.y = 0.0 || y;
        this.frameIndex = 0;
        this._frameIndex = 0.0;
        this.distanceToPlayer;
        this.sizeRadius = 0.1;		//calculate in depence of map size
        this.precision = 0.001;
        this.textures = textures;
        this.animationSpeed = 5;
        this.frames = frames - 1;
    }

    function MoveableSprite(x, y, textureId, direction) {
        Sprite.apply(this, arguments);
        this.direction = direction || 1.523;    // degrees - rad, 0-0, 90-‪1.570796‬, 180-‪3.141593‬, 270-‪4.712389‬
        this.weapon;
        this.paces = 0;
        this.circle = Math.PI * 2;
        this.fov = Math.PI / 3.0;
        this.ray;
        this.waypoint;
        this.lifetime = 0.0;
    }

    MoveableSprite.prototype = Object.create(Sprite.prototype);
    MoveableSprite.prototype.constructor = MoveableSprite;

    MoveableSprite.prototype.move = function (map, distance) {
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

    MoveableSprite.prototype.update = function (map, seconds) {

        this.ray = map.castRay(this, this.direction, 0.3, true);
        if (this.ray.distance < 0.9) {
            this.direction = this.getRandom(0, 6);
        } else {
            this.move(map, seconds);
        }
        this.lifetime += seconds;

        this._frameIndex += this.animationSpeed * seconds;
        this.frameIndex = Math.trunc(this._frameIndex);
        if (this.frameIndex > this.frames) {
            this.frameIndex = this._frameIndex = 0;
        }
    };

    MoveableSprite.prototype.getRandomInt = function (min, max) {
        return Math.floor(Math.random() * (max - min)) + min;
    };

    MoveableSprite.prototype.getRandom = function (min, max) {
        return Math.random() * (max - min) + min;
    };

    function Npc() {
        MoveableSprite.apply(this, arguments);
    }

    Npc.prototype = Object.create(MoveableSprite.prototype);
    Npc.prototype.constructor = Npc;

    function Monster () {
        Npc.apply(this, arguments);
        this.sizeRadius = 0.3;
    }

    Monster.prototype = Object.create(Npc.prototype);
    Monster.prototype.constructor = Monster;

    function Bitmap(src, width, height, hFrameSize, vFrameSize, onload) {
        this.width = width;
        this.height = height;
        this.isLoaded;
        this.packedImg;
        this.onload = onload;
        this.src = src;
        this.vFrameSize = vFrameSize;
        this.hFrameSize = hFrameSize;
        this.rows = Math.floor(height / vFrameSize);
        this.columns = Math.floor(width / hFrameSize);

        this.load();
    }

    Bitmap.prototype.load = function (onload, src) {
        let self = this;
        let image = new Image();							//move to constructor?
        let callback = onload || this.onload;
        image.onload = function() {
            self.packedImg = self.packImg(image);
            if (callback) callback();
            self.isLoaded = true;
        };
        image.src = src || this.src;
    };

    Bitmap.prototype.packColor = function (r, g, b, a) {
        return (a << 24) | (b << 16) | (g << 8) | r;
    };

    Bitmap.prototype.unpackColor = function (color) {
        return [(color >> 0) & 255, (color >> 8) & 255, (color >> 16) & 255, (color >> 24) & 255];
    };

    Bitmap.prototype.packImg = function (img) {
        let packedTextures = [];
        let r, g, b, a;
        let canvas = document.createElement('canvas');
        canvas.height = img.height;
        canvas.width = img.width;
        canvas.getContext("2d").drawImage(img, 0, 0, img.width, img.height);
        let imgData = canvas.getContext("2d").getImageData(0, 0, img.width, img.height);

        for (let j = 0; j < img.height; j++) {
            for (let i = 0; i < img.width; i++) {
                r = imgData.data[(i + j * img.width) * 4 + 0];
                g = imgData.data[(i + j * img.width) * 4 + 1];
                b = imgData.data[(i + j * img.width) * 4 + 2];
                a = imgData.data[(i + j * img.width) * 4 + 3];
               packedTextures[i + j * img.width] = this.packColor(r, g, b, a);
            }
        }

        return packedTextures;
    };

    function Camera() {
        this.height = 512;
        this.width = 1024;
        this.canvas;
        this.context;
        this.frameBuffer = [];
        this.imgDataFinalScene;
        this.map;
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
        this.showRayOnMap = true;
    }

    Camera.prototype.packColor = function (r, g, b, a) {
        return (a << 24) | (b << 16) | (g << 8) | r;
    };

    Camera.prototype.unpackColor = function (color) {
        return [(color >> 0) & 255, (color >> 8) & 255, (color >> 16) & 255, (color >> 24) & 255];
    };

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

    Camera.prototype.drawBackground = function (bitmap, direction) {
        let left = Math.floor((direction / (Math.PI * 2)) * + bitmap.width);
        let top = (bitmap.height - this.projectionHeight) / 2;

        for (let j = this.projectionTopY, v=0; j < this.projectionBottomY; j++, v++) {
            for (let i = this.projectionLeftX, h=0; i < this.projectionRightX; i++, h++) {
                if ((left+h) < bitmap.width) {
                    this.frameBuffer[i + j  * this.width] = bitmap.packedImg[left + h + (v + top) * bitmap.width];
                } else {
                    this.frameBuffer[i + j  * this.width] = bitmap.packedImg[left + h - bitmap.width + (v + top) * bitmap.width];
                }
            }
        }
    };

    Camera.prototype.setMapPositionOnScreen = function (map, position) {
        this.mapPosition = position;
    };

    Camera.prototype.setMap = function (map, position) {
        this.mapPosition = position;
        this.map = map;
        if (this.mapPosition === 'onTop') {
            this.projectionWidth = this.width;
            this.projectionHeight = this.height / 2;
            this.projectionLeftX = 0;
            this.projectionRightX = this.width;
            this.projectionMiddleY = Math.floor(this.height * 3 / 4);
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
            this.projectionMiddleY = Math.floor(this.height / 4);
            this.projectionTopY = 0;
            this.projectionBottomY = this.height / 2;
            this.hMapScaleRatio = this.width / map.width;
            this.vMapScaleRatio = this.height / (map.height * 2);
            this.mapWinLeftX = 0;
            this.mapWinTopY = this.height / 2;
        } else if (this.mapPosition === 'onRight') {
            this.projectionWidth = Math.floor(this.width / 2);
            this.projectionHeight = this.height;
            this.projectionLeftX = 0;
            this.projectionRightX = Math.floor(this.width / 2);
            this.projectionMiddleY = Math.floor(this.height / 2);
            this.projectionTopY = 0;
            this.projectionBottomY = this.height;
            this.hMapScaleRatio = this.width / (map.width * 2);
            this.vMapScaleRatio = this.height / map.height;
            this.mapWinLeftX = Math.floor(this.width / 2);
            this.mapWinTopY = 0;
        } else if (this.mapPosition === 'onLeft') {
            this.projectionWidth = Math.floor(this.width / 2);
            this.projectionHeight = this.height;
            this.projectionLeftX = Math.floor(this.width / 2);
            this.projectionRightX = this.width;
            this.projectionMiddleY = Math.floor(this.height / 2);
            this.projectionTopY = 0;
            this.projectionBottomY = this.height;
            this.hMapScaleRatio = this.width / (map.width * 2);
            this.vMapScaleRatio = this.height / map.height;
            this.mapWinLeftX = 0;
            this.mapWinTopY = 0;
        } else {
            this.projectionWidth = this.projectionRightX = this.width;
            this.projectionHeight = this.projectionBottomY = this.height;
            this.projectionLeftX = this.mapWinLeftX = this.mapWinTopY = this.projectionTopY = 0;
            this.projectionMiddleY = Math.floor(this.height / 2);
            this.hMapScaleRatio = this.width / map.width;
            this.vMapScaleRatio = this.height / map.height;
        }
    };

    Camera.prototype.getMapPositionOnScreen = function () {
        return this.mapPosition;
    };

    Camera.prototype.getMap = function () {
        this.mapPosition = position;
            return this.map;
    }

    Camera.prototype.drawPlayerOnMap = function (player) {
        this.drawRect(player.x * this.hMapScaleRatio + this.mapWinLeftX, player.y * this.vMapScaleRatio + this.mapWinTopY, 5, 5, this.packColor(0, 0, 0, 255));
    };

    Camera.prototype.drawSpritesOnMap = function (sprites) {
        for (let i = 0; i < sprites.length; i++){
            this.drawRect(sprites[i].x * this.hMapScaleRatio + this.mapWinLeftX, sprites[i].y * this.vMapScaleRatio + this.mapWinTopY, 5, 5, this.packColor(255, 0, 0, 255));
            if (this.showRayOnMap) {
                this.drawRayOnMap(sprites[i].ray);
            }
        }
    };

    Camera.prototype.drawSprites = function (sprites, player, depthBuffer) {
        sprites.sort(function(spriteA, spriteB) {
            return spriteB.distanceToPlayer - spriteA.distanceToPlayer;
        });
        for (let i = 0; i < sprites.length; i++) {
            sprites[i].distanceToPlayer = Math.sqrt(Math.pow(player.x - sprites[i].x, 2) + Math.pow(player.y - sprites[i].y, 2));
            this.drawSprite(sprites[i], player, depthBuffer);
        }
    }

    Camera.prototype.drawSprite = function (sprite, player, depthBuffer) {
        let spriteDirection, spriteProjectionSize, h_offset, v_offset, pixel, hSpriteScaleRatio, vSpriteScaleRatio, spriteOffset;

        spriteDirection = Math.atan2(sprite.y - player.y, sprite.x - player.x);						// absolute direction from the player to the sprite (in radians)
        while (spriteDirection - player.direction >  Math.PI)
            spriteDirection -= 2 * Math.PI; 													// remove unncesessary periods from the relative direction
        while (spriteDirection - player.direction < - Math.PI)
            spriteDirection += 2 * Math.PI;
        spriteProjectionSize = Math.min(500, Math.floor(this.projectionHeight / sprite.distanceToPlayer));
        h_offset = Math.floor((spriteDirection - player.direction) * this.projectionWidth / player.fov + this.projectionWidth / 2 - spriteProjectionSize / 2);
        v_offset = this.projectionMiddleY - Math.floor(spriteProjectionSize / 2);
        hSpriteScaleRatio = sprite.textures.hFrameSize / spriteProjectionSize;
        vSpriteScaleRatio = sprite.textures.vFrameSize / spriteProjectionSize;
        spriteOffset = sprite.frameIndex * sprite.textures.hFrameSize;

        for (let i = 0; i < spriteProjectionSize; i++) {
            if ((h_offset + i) < 0 || depthBuffer[h_offset + i] < sprite.distanceToPlayer) continue;
            if ((h_offset + i) >= this.projectionWidth) break;
            for (let j = 0; j < spriteProjectionSize; j++) {
                if ((v_offset + j) < 0)  continue;
                if ((v_offset + j) >= this.projectionBottomY)  break;
                pixel = sprite.textures.packedImg[Math.floor(i * hSpriteScaleRatio) + spriteOffset + Math.floor(j * vSpriteScaleRatio) * sprite.textures.width];
                if (sprite.textures.unpackColor(pixel)[3] > 128)
                    this.frameBuffer[this.projectionLeftX + h_offset + i + (v_offset + j) * this.width] = pixel;
            }
        }
    };

    Camera.prototype.drawMap = function (walls) {
        let textureId;

        for (let j = 0; j < this.map.height; j++) {
            for (let i = 0; i < this.map.width; i++) {
                if (this.map.walls[i + j * this.map.width] === undefined) {
                    continue;
                }
                textureId = this.map.walls[i + j * this.map.width];
                if (this.mapPosition === 'onLeft') {
                    this.drawRect(i * this.hMapScaleRatio, j * this.vMapScaleRatio, this.vMapScaleRatio, this.hMapScaleRatio, walls.packedImg[textureId * walls.hFrameSize]);
                } else if (this.mapPosition === 'onRight') {
                    this.drawRect(i * this.hMapScaleRatio + this.mapWinLeftX, j * this.vMapScaleRatio, this.vMapScaleRatio, this.hMapScaleRatio, walls.packedImg[textureId * walls.hFrameSize]);
                } else if (this.mapPosition === 'onTop') {
                    this.drawRect(i * this.hMapScaleRatio, j * this.vMapScaleRatio, this.vMapScaleRatio, this.hMapScaleRatio, walls.packedImg[textureId * walls.hFrameSize]);
                } else if (this.mapPosition === 'onBottom') {
                    this.drawRect(i * this.hMapScaleRatio, j * this.vMapScaleRatio + this.mapWinTopY, this.vMapScaleRatio, this.hMapScaleRatio, walls.packedImg[textureId * walls.hFrameSize]);
                } else {
                    this.drawRect(i * this.hMapScaleRatio, j * this.vMapScaleRatio, this.vMapScaleRatio, this.hMapScaleRatio, walls.packedImg[textureId * walls.hFrameSize]);
                }
            }
        }
    };

    Camera.prototype.drawRect = function (rectX, rectY, rectHeight, rectWidth, packedColor) {
        let cx;
        let cy;
        for (let j = 0; j < rectWidth; j++) {
            for (let i = 0; i < rectHeight; i++) {
                cx = Math.floor(rectX + j);
                cy = Math.floor(rectY + i);
                if (cx >= this.width || cy >= this.height)
                    continue;	//why?
                this.frameBuffer[cx + cy * this.width] = packedColor;
            }
        }
    };

    Camera.prototype.drawWeapon = function (bitmap, paces) {
        let pixel, periodY = 4, periodX = 2, amplitudeY = 6,amplitudeX = 6, initialX = 0.66, initialY = 0.6;
        let scale = (this.width + this.height) / 1700;
        let scaledWeaponTextureHeight = Math.floor(bitmap.height/scale);
        let scaledWeaponTextureWidth = Math.floor(bitmap.width/scale);

        let bobX = Math.cos(paces * periodX) * scale * amplitudeX;
        let bobY = Math.sin(paces * periodY) * scale * amplitudeY;
        let left = Math.floor(this.projectionLeftX + this.projectionWidth - (scaledWeaponTextureWidth * initialX + amplitudeX * scale) + bobX);
        let top = Math.floor(this.projectionTopY + this.projectionHeight - (scaledWeaponTextureHeight * initialY + amplitudeY * scale) + bobY);

        for (let j = 0; j < scaledWeaponTextureHeight; j++) {
            if ((j + top) > this.projectionBottomY) break;
            for (let i = 0; i < scaledWeaponTextureWidth; i++) {
                if ((i + left) > this.projectionRightX) break;
                pixel = bitmap.packedImg[Math.floor(i*scale) + Math.floor(j*scale) * bitmap.width];
                if (bitmap.unpackColor(pixel)[3] > 128)
                    this.frameBuffer[i + left  + (j + top) * this.width] = pixel;
            }
        }
    };

    Camera.prototype.drawColumns = function (player, map, bitmap) {
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
            columnHeight = Math.min(1000, Math.floor(this.projectionHeight / ray.distance));
            textureX = this.getTextureX(ray.x, ray.y, bitmap);
            column = this.getColumnTexture(ray.barrier, textureX, columnHeight, bitmap);
            pix_x = this.projectionLeftX + i;
            for (let j = 0; j < columnHeight; j++) {
                pix_y = j + this.projectionMiddleY - Math.floor(columnHeight / 2);
                if (pix_y >= this.projectionTopY && pix_y < this.projectionBottomY)
                    this.frameBuffer[pix_x + pix_y * this.width] = column[j];
            }
            if (this.showRayOnMap) {
                this.drawRayOnMap(ray);
            }
        }

        return depthBuffer;
    };

    Camera.prototype.drawRayOnMap = function (ray) {
        let x, y;

        for (let j = 0; j < ray.trace.length; j++) {
            x = Math.floor(ray.trace[j].x * this.hMapScaleRatio + this.mapWinLeftX);                                     //scale to screen
            y = Math.floor(ray.trace[j].y * this.vMapScaleRatio + this.mapWinTopY);                                       //scale to screen
            this.frameBuffer[x + y * this.width] = -6250336;                                //draw a pixel of the ray with grayish color
        }
    }

    Camera.prototype.getTextureX = function (x, y, bitmap) {
        let hitX, hitY, textureX;

        hitX = x - Math.floor(x + 0.5);                                                   //get fractional part of x
        hitY = y - Math.floor(y + 0.5);                                                   //get fractional part of y
        textureX = hitX * bitmap.hFrameSize;
        if (Math.abs(hitY) > Math.abs(hitX))
            textureX = hitY * bitmap.vFrameSize;
        if (textureX < 0)
            textureX += bitmap.hFrameSize;

        return textureX;
    };

    Camera.prototype.getColumnTexture = function (textureId, textureX, columnHeight, bitmap) {
        let column = [];
        let pixX;
        let pixY;

        pixX = textureId * bitmap.hFrameSize + Math.floor(textureX);
        for (let i = 0; i < columnHeight; i++) {
            pixY = Math.floor((i * bitmap.vFrameSize) / columnHeight);
            column[i] = bitmap.packedImg[pixX + pixY * bitmap.width];
        }

        return column;
    };

    Camera.prototype.fillColor = function () {
        for (let j = 0; j < this.height; j++) {
            for (let i = 0; i < this.width; i++) {
                this.frameBuffer[i + j * this.width] = -1;
            }
        }
    };

    Camera.prototype.render = function (player, monsters, map, weapon, background, walls) {
        this.fillColor();
        this.drawBackground(background, player.direction);
        this.drawMap(walls);
        let depthBuffer = this.drawColumns(player, map, walls);
        this.drawPlayerOnMap(player);
        this.drawSpritesOnMap(monsters);
        this.drawSprites(monsters, player, depthBuffer);
        this.drawWeapon(weapon, player.paces);
        this.show(debug.message);
    }

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
            5, , , , , , , , ,2, , , ,2, ,0,
            0, , , , , ,2,2,2,2, , , ,2, ,3,
            5, , , , , , , , ,2, , , ,2, ,0,
            0, , , , ,2,2,2, ,2,2,2, ,2, ,3,
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
        return this.walls[Math.floor(x) + Math.floor(y) * this.width] === undefined;
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

    Map.prototype.castRay = function (sprite, angle, step, saveTrace) {
        let textureId, cx, cy, pix_x, pix_y, distance, steps;
        let increment = step || 0.01;
        let trace = [];

        for (steps = 0; steps < 20; steps += increment) {                                                        //step of the ray
            cx = sprite.x + steps * Math.cos(angle);                                                    //x coordinate of ray
            cy = sprite.y + steps * Math.sin(angle);                                                    //y coordinate of ray
            if (saveTrace) {
                trace.push({x: cx, y: cy});
            }
            textureId = this.walls[Math.floor(cx) + Math.floor(cy) * this.width];
            if (textureId !== undefined) break;
        }
        distance = steps * Math.cos(angle - sprite.direction);

        return {x: cx, y: cy, distance: distance, barrier: textureId, trace: trace};
    }

    if (document.readyState !== 'loading') {
        startGame();
    } else {
        document.addEventListener('DOMContentLoaded', startGame);
    }

    function startGame() {
        let counter = 0;
        let onload = function() {
            counter++;
            if (counter >= 4) {
                startLoop();
            }
        };

let texture = new Bitmap('/load/textures/3', 576, 384, 72, 96, onload);

        let text = new Bitmap('/load/textures/3', 256, 64, 64, 64, onload);
        let weapon = new Bitmap('/load/textures/4', 319, 320, 319, 320, onload);
        let background = new Bitmap('/load/textures/2', 2048, 1024, 2048, 1024, onload);
        let walls = new Bitmap('/load/textures/1', 384, 64, 64, 64, onload);

        let monsters = [new Monster(6, 6, text, 3)];
        let controls = new Controls();
        let map = new Map();
        let camera = new Camera();
        let player = new Player();
        let loop = new GameLoop();

        let cnv = document.getElementById("canvas");
        camera.setCanvas(cnv);
        camera.setMap(map, 'onRight');

        function startLoop() {
            loop.start(function frame(seconds) {
                player.update(controls.states, map, seconds);
                for (let i = 0; i < monsters.length; i++) {
                    monsters[i].update(map, seconds);
                }

                camera.render(player, monsters, map, weapon, background, walls);
            });
        }
    }

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