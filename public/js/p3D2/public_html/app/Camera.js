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
    function Camera(width, height) {
        this.height = height;
        this.width = width;
        this.canvas;
        this.context;
        this.depthBuffer = [];

        this.canvasOffScreen = document.createElement('canvas');
        this.canvasOffScreen.height = width / 5;
        this.canvasOffScreen.width = width / 5;
        this.ctxOffScr = this.canvasOffScreen.getContext("2d");
        this.prevCanvas;
        this.prevX;
        this.prevY;
        this.prevDirection                
    }

    Camera.prototype.setCanvas = function (canvas) {
        this.canvas = canvas;
        this.canvas.width = this.width;
        this.canvas.height = this.height;
        this.context = this.canvas.getContext("2d");
        this.context.msImageSmoothingEnabled = false;
        this.context.mozImageSmoothingEnabled = false;
        this.context.imageSmoothingEnabled = false;
    };

    Camera.prototype.drawBackground = function (bitmap, direction) {
        var width = bitmap.width * (this.height / bitmap.height) * 2;
        var left = (direction / (Math.PI * 2)) * -width;

        this.context.save();
        this.context.drawImage(bitmap.image, left, 0, width, this.height);
        if (left < width - this.width) {
            this.context.drawImage(bitmap.image, left + width, 0, width, this.height);
        }
        this.context.restore();
    };

    Camera.prototype.drawMap = function (map, color) {
        let hMapScaleRatio = this.width / map.width;
        let vMapScaleRatio = this.height / map.height;

        this.context.save();
        for (let j = 0; j < map.height; j++) {
            for (let i = 0; i < map.width; i++) {
                if (map.isEmptyCell(i, j))
                    continue;
                this.context.fillStyle = color;
                this.context.fillRect(i * hMapScaleRatio, j * vMapScaleRatio, hMapScaleRatio, vMapScaleRatio);
            }
        }
        this.context.restore();
    };

    Camera.prototype.drawAim = function (radius, color) {
        this.context.save();
        //this.context.globalAlpha = 0.5;
        this.context.strokeStyle = color;
        this.context.lineWidth = 1;
        this.context.arc(this.width / 2, this.height / 2, radius, 0, 2 * Math.PI);
        this.context.stroke();
        this.context.beginPath();
        this.context.moveTo(this.width / 2, this.height / 2 - 1.5 * radius);
        this.context.lineTo(this.width / 2, this.height / 2 + 1.5 * radius);
        this.context.stroke();
        this.context.moveTo(this.width / 2 - 1.5 * radius, this.height / 2);
        this.context.lineTo(this.width / 2 + 1.5 * radius, this.height / 2);
        //this.context.globalAlpha = 1;
        this.context.restore();
    };
    
    Camera.prototype.drawHealthBar = function (health, color) {
        let bar, barWidth = 10;
        let x = this.width - this.width / 5 - 10;
        let y = 20;
        let fullBar = this.width / 5;

        this.context.save();
        if (health < 0) {
            bar = 0;
        } else {
            bar = health * this.width / 500;
        }
        if (health <= 30) {
            this.context.fillStyle = 'red';
        } else {
            this.context.fillStyle = color;
        }
        this.context.globalAlpha = 0.5;
        this.context.fillRect(x, y, bar, barWidth);
        this.context.strokeRect(x, y, fullBar, barWidth);
        this.context.globalAlpha = 1;
        this.context.restore();
    };

    Camera.prototype._drawMiniMap = function (x, y, direction, objectRadius, diameter, map, color) {
        let cx, cy;
        let scaleRatio = this.canvasOffScreen.width / Math.max(map.width, map.height);
        let radius = this.canvasOffScreen.width / 2;

        this.ctxOffScr.clearRect(0, 0, this.canvasOffScreen.width, this.canvasOffScreen.height);

        this.ctxOffScr.fillStyle = color;

        this.ctxOffScr.arc(radius, radius, radius, 0, 2 * Math.PI);
        this.ctxOffScr.clip();

        this.ctxOffScr.globalAlpha = 0.5;
        this.ctxOffScr.fill();
        this.ctxOffScr.globalAlpha = 1;

        this.ctxOffScr.beginPath();
        this.ctxOffScr.arc(radius, radius, objectRadius * scaleRatio, 0, 2 * Math.PI);
        this.ctxOffScr.stroke();

        this.ctxOffScr.translate(radius, radius);
        this.ctxOffScr.rotate(-(direction + Math.PI / 2));
        this.ctxOffScr.scale(map.width / 16, map.height / 16);    //if map size is 16, then don't zoom, if map 32 - increase in 2 times

        for (let j = 0; j < this.canvasOffScreen.height; j++) {
            cy = y + j / scaleRatio - map.height / 2;

            if (cy < 0)
                continue;
            if (cy >= map.height)
                break;

            for (let i = 0; i < this.canvasOffScreen.width; i++) {
                cx = x + i / scaleRatio - map.width / 2;

                if (cx < 0)
                    continue;
                if (cx >= map.width)
                    break;

                if (map.isEmptyCell(cx, cy))
                    continue;

                this.ctxOffScr.fillRect(i - radius, j - radius, 1, 1);
            }
        }

        this.ctxOffScr.setTransform(1, 0, 0, 1, 0, 0);

        this.context.drawImage(this.canvasOffScreen, 0, 0);
    };

    Camera.prototype.drawMiniMap = function (x, y, direction, objectRadius, diameter, map, color) {
        let cx, cy;
        let scaleRatio = diameter / Math.max(map.width, map.height);
        
        if (this.prevCanvas) {
            if (Math.abs(this.prevX - x) < 0.5 && Math.abs(this.prevY - y) < 0.5 && Math.abs(this.prevDirection - direction) < 0.5) {
                this.context.drawImage(this.prevCanvas, 0, 0);
                return;
            }
        }
        
        this.prevX = x;
        this.prevY = y;
        this.prevDirection = direction;
        
        let radius = diameter / 2;

        let canvas = document.createElement('canvas');
        canvas.height = diameter;
        canvas.width = diameter;
        let ctx = canvas.getContext("2d");

        ctx.fillStyle = color;

        ctx.arc(radius, radius, radius, 0, 2 * Math.PI);
        ctx.clip();

        ctx.globalAlpha = 0.5;
        ctx.fill();
        ctx.globalAlpha = 1;

        ctx.beginPath();
        ctx.arc(radius, radius, objectRadius * scaleRatio, 0, 2 * Math.PI);
        ctx.stroke();

        ctx.translate(radius, radius);
        ctx.rotate(-(direction + Math.PI / 2));

        ctx.scale(map.width / 16, map.height / 16);    //if map size is 16, then don't zoom, if map 32 - increase in 2 times

        for (let j = 0; j < diameter; j++) {
            cy = Math.floor((y + j / scaleRatio) - map.height / 2);

            if (cy < 0)
                continue;
            if (cy >= map.height)
                break;

            for (let i = 0; i < diameter; i++) {
                cx = Math.floor((x + i / scaleRatio) - map.width / 2);

                if (cx < 0)
                    continue;
                if (cx >= map.width)
                    break;

                if (map.walls[cx + cy * map.width] === undefined)
                    continue;

                ctx.fillRect(i - radius, j - radius, 1, 1);
            }
        }

        this.context.drawImage(canvas, 0, 0);
        this.prevCanvas = canvas;                         
    };

    Camera.prototype.drawRect = function (x, y, width, height, color) {
        this.context.save();
        this.context.fillStyle = color;
        this.context.fillRect(x, y, width, height);
        this.context.restore();
    };

    Camera.prototype.drawObjectsOnMap = function (objects, map, color) {
        for (let j = 0; j < objects.length; j++) {
            this.drawObjectOnMap(objects[j], map, color);
        }
    };

    Camera.prototype.drawObjectOnMap = function (object, map, color) {
        let hMapScaleRatio = this.width / map.width;
        let vMapScaleRatio = this.height / map.height;
        let width = 2 * object.sizeRadius * hMapScaleRatio;
        let height = 2 * object.sizeRadius * vMapScaleRatio;
        let centerX = object.x * hMapScaleRatio - width / 2;
        let centerY = object.y * vMapScaleRatio - height / 2;

        this.drawRect(centerX, centerY, width, height, color);
    };

    Camera.prototype.drawHands = function (bitmap, paces) {

    };

    Camera.prototype.clearScreen = function () {
        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
    };

    Camera.prototype.drawWalls = function (x, y, direction, fov, map, bitmap) {
        let angle = 0.0, ray, columnHeight, columnTop, textureX, leftEdge, angleBetweenRays, distance;

        leftEdge = direction - fov / 2;
        angleBetweenRays = fov / this.width;

        this.context.save();
        for (let i = 0; i < this.width; i++) {                                                      //step define amount of rays
            angle = leftEdge + angleBetweenRays * i;
            ray = map.castRay(x, y, angle, 0.01, true);
            distance = ray.distance * Math.cos(angle - direction);		//to avoid fish eye
            this.depthBuffer[i] = distance;
            columnHeight = Math.min(1000, this.height / distance);
            columnTop = this.height / 2 - columnHeight / 2;
            textureX = this._getTextureX(ray, bitmap);
            this.context.drawImage(bitmap.image, textureX, 0, 1, bitmap.height, i, columnTop, 1, columnHeight);
        }
        this.context.restore();
    };

    Camera.prototype.drawObjects = function (objects, x, y, direction, fov) {
        objects.sort(function (objectA, objectB) {
            return Math.sqrt(Math.pow(x - objectB.x, 2) + Math.pow(y - objectB.y, 2))
                    - Math.sqrt(Math.pow(x - objectA.x, 2) + Math.pow(y - objectA.y, 2));
        });

        for (let i = 0; i < objects.length; i++) {
            this.drawObject(objects[i], x, y, direction, fov);
        }
    };

    Camera.prototype.drawObject = function (object, x, y, direction, fov) {
        let directionToObject, objectProjectionSize, distanceBetweenObjectAndCamera;
        let hOffsetOnProjection, vOffsetOnProjection, hObjectScaleRatio;
        let graphicsComponent = object.getGraphics();

        directionToObject = Math.atan2(object.y - y, object.x - x);						// absolute direction from the player(!) to the sprite(!) (in radians)
        while (directionToObject - direction > Math.PI)
            directionToObject -= 2 * Math.PI; 													// remove unncesessary periods from the relative direction
        while (directionToObject - direction < - Math.PI)
            directionToObject += 2 * Math.PI;

        distanceBetweenObjectAndCamera = Math.sqrt(Math.pow(x - object.x, 2) + Math.pow(y - object.y, 2));

        objectProjectionSize = Math.min(2000, Math.floor(this.height / distanceBetweenObjectAndCamera));
        hOffsetOnProjection = Math.floor((directionToObject - direction) * this.width / fov + this.width / 2 - objectProjectionSize / 2);
        vOffsetOnProjection = this.height / 2 - Math.floor(objectProjectionSize / 2);
        hObjectScaleRatio = graphicsComponent.getFrameWidth() / objectProjectionSize;

        for (let i = 0; i < objectProjectionSize; i++) {
            if ((hOffsetOnProjection + i) < 0 || this.depthBuffer[hOffsetOnProjection + i] < distanceBetweenObjectAndCamera)
                continue;
            if ((hOffsetOnProjection + i) >= this.width)
                break;
            this.context.drawImage(
                    graphicsComponent.getImage(),
                    graphicsComponent.getImageX() + i * hObjectScaleRatio,
                    graphicsComponent.getImageY(object, x, y),
                    1,
                    graphicsComponent.getFrameHeight(),
                    hOffsetOnProjection + i,
                    vOffsetOnProjection,
                    1,
                    objectProjectionSize
                    );
        }
    };

    Camera.prototype._getTextureX = function (ray, bitmap) {
        let hitX, hitY, textureX;

        hitX = ray.x - Math.floor(ray.x + 0.5);                                                   //get fractional part of x
        hitY = ray.y - Math.floor(ray.y + 0.5);                                                   //get fractional part of y
        textureX = hitX * bitmap.frameWidth;
        if (Math.abs(hitY) > Math.abs(hitX))
            textureX = hitY * bitmap.frameHeight;
        if (textureX < 0)
            textureX += bitmap.frameWidth;

        return ray.barrier * bitmap.frameWidth + textureX;
    };

    Camera.prototype.drawFovsOnMap = function (objects, map, color) {
        for (let j = 0; j < objects.length; j++) {
            this.drawFovOnMap(objects[j], map, color);
        }
    };

    Camera.prototype.drawFovOnMap = function (object, map, color) {
        let left, right, rays = [], step = 0.5;

        left = object.direction - object.fov / 2;
        right = object.direction + object.fov / 2;
        rays.push(map.castRay(object.x, object.y, left, step, true));
        rays.push(map.castRay(object.x, object.y, object.direction, step, true));
        rays.push(map.castRay(object.x, object.y, right, step, true));

        this.drawRaysOnMap(rays, map, color);
    };

    Camera.prototype.drawRaysOnMap = function (rays, map, color) {
        for (let j = 0; j < rays.length; j++) {
            this.drawRayOnMap(rays[j], map, color);
        }
    };

    Camera.prototype.drawRayOnMap = function (ray, map, color) {
        let x, y, hMapScaleRatio, vMapScaleRatio;

        hMapScaleRatio = this.width / map.width;
        vMapScaleRatio = this.height / map.height;

        this.context.save();
        this.context.fillStyle = color;

        for (let j = 0; j < ray.trace.length; j++) {
            x = Math.floor(ray.trace[j].x * hMapScaleRatio);                                     //scale to screen
            y = Math.floor(ray.trace[j].y * vMapScaleRatio);                                       //scale to screen
            this.context.fillRect(x, y, 1, 1); //draw a pixel of the ray with color
        }
        this.context.restore();
    };


    return {
        create: function (width, height) {
            return new Camera(width, height);
        }
    };
});
