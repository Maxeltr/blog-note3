/**
 * Vector object
 * Providing basic operations related to vector algebra and transformations
 *
 * @author Lukasz Krawczyk <lukasz@abeja.asia>
 * @license MIT
 */
define(function (require) {
VectorAlgebra = {

    /**
     * Operation on vectors and scalars
     *
     * @param {Array|number} a
     * @param {Array|number} b
     * @param {callable} callback
     * @param {boolean} summation
     * @return {Array|number}
     * @access private
     */
    __operation: function(a, b, callback, summation) {
        var n = (summation) ? 0 : []
            , aVector = (a instanceof Array)
            , bVector = (b instanceof Array);

        if (aVector && bVector) { // two vectors
            for (var i = 0; i < Math.min(a.length, b.length); i++) {
                if (summation) n += callback(a[i], b[i]);
                else n[i] = callback(a[i], b[i]);
            }
        } else if (aVector) { // vector and scalar
            for (var i = 0; i < a.length; i++) {
                if (summation) n += callback(a[i], b);
                else n[i] = callback(a[i], b);
            }
        } else if (bVector) { // scalar and vector
            for (var i = 0; i < b.length; i++) {
                if (summation) n += callback(a, b[i]);
                else n[i] = callback(a, b[i]);
            }
        } else { // two scalars
            return callback(a, b);
        }

        return n;
    },
    
    // BASIC OPERATIONS

    /**
     * Add two vectors / scalars
     * 
     * @param {Array|number} a
     * @param {Array|number} b
     * @returns {Array|number}
     * @access public
     */
    add: function(a, b) {
        return this.__operation(a, b, function(an, bn) { return an + bn; });
    },

    /**
     * Subtract two vectors / scalars
     *
     * @param {Array|number} a
     * @param {Array|number} b
     * @returns {Array|number}
     * @access public
     */
    subtract: function(a, b) {
        return this.__operation(a, b, function(an, bn) { return an - bn; });
    },
    
    /**
     * Multiply two vectors / scalars
     *
     * @param {Array|number} a
     * @param {Array|number} b
     * @returns {Array|number}
     * @access public
     */
    multiply: function(a, b) {
        return this.__operation(a, b, function(an, bn) { return an * bn; });
    },

    /**
     * Divide two vectors / scalars
     *
     * @param {Array|number} a
     * @param {Array|number} b
     * @returns {Array|number}
     * @access public
     */
    divide: function(a, b) {
        return this.__operation(a, b, function(an, bn) { return an / bn; });
    },

    /**
     * Sum of products
     *
     * @param {Array|number} a
     * @param {Array|number} b
     * @returns {Array|number}
     * @access public
     */
    sumOfProducts: function(a, b) {
        return this.__operation(a, b, function(an, bn) { return an * bn; }, true);
    },

    /**
     * Calculating norm - length of a given vector
     *
     * @param {Array} a
     * @return {int|float}
     * @access public
     */
    norm: function(a) {
        return Math.sqrt(this.dot(a, a));
    },

    /**
     * Alias for norm
     *
     * @param {Array}
     * @return {integer}
     * @access public
     */
    length: function(a) {
        return this.norm(a);
    },

    /**
     * Alias for euclidean distance
     *
     * @param {Array} a
     * @param {Array} b
     * @return {number}
     * @access public
     */
    distance: function(a, b) {
        return this.euclidean(a, b);
    },

    /**
     * Euclidean distance between two vectors
     * Euclidean distance is a special case of Minkowski distance where p == 2
     *
     * @param {Array} a
     * @param {Array} b
     * @return {number}
     * @access public
     */
    euclidean: function(a, b) {
        var sum = this.__operation(a, b, function(an, bn) { return Math.pow(an - bn, 2); }, true);
        return Math.sqrt(sum);
    },
    
    /**
     * Manhattan distance between two vectors
     * Manhattan distance is a special case of Minkowski distance where p == 1
     * 
     * @param {Array} a
     * @param {Array} b
     * @return {number}
     * @access public
     */
    manhattan: function(a, b) {
        return this.__operation(a, b, function(an, bn) { return Math.abs(an - bn); }, true);
    },
    
    /**
     * Chebyshew distance between two vectors
     * Chebyshew distance is a special case of Minkowski distance where p is reaching infinity
     * 
     * @param {Array} a
     * @param {Array} b
     * @return {number}
     * @access public
     */
    chebyshev: function(a, b) {
        var n = this.__operation(a, b, function(an, bn) { return Math.abs(an - bn); });
        return Math.max.apply(null, n); // return maximum value
    },
    
    /**
     * Cosine similarity
     *
     * @param {Array} a
     * @param {Array} b
     * @return {number}
     * @access public
     */
    cosine: function(a, b) {
        return this.dot(a, b) / (this.norm(a) * this.norm(b));
    },
    
    /**
     * Return unit vector
     *
     * @param {Array} v
     * @returns {Array}
     * @access public
     */
    unit: function(v) {
        return this.divide(v, this.norm(v));
    },

    // DOT & CROSS PRODUCT

    /**
     * Calculating dot product of two vectors
     * Dot product of orthogonal vectors is 0
     *
     * @param {Array} a
     * @param {Array} b
     * @return {int|float}
     * @access public
     */
    dot: function(a, b) {
        return this.sumOfProducts(a, b); 
    },
    
    /**
     * Calculating cross product of two vectors
     * Cross product is perpendicular to both a and b
     *
     * @param {Array} a
     * @param {Array} b
     * @return {int|float}
     * @access public
     */
    cross: function(a, b) {
        var n = [];
        if (a === b) return 0;
        
        for (var i = 0; i < 3; i++){
            var id1 = (i + 1) % 3, id2 = (i + 2) % 3;
            n[i] = (a[id1] * b[id2]) - (a[id2] * b[id1]);
        }
        
        return n;
    },

    /**
     * Calculate scalar triple product
     * a · (b x c)
     *
     * @param {Array} a
     * @param {Array} b
     * @param {Array} c
     * @returns {float|int}
     * @access public
     */
    scalarTripleProduct: function(a, b, c) {
        return this.dot(a, this.cross(b, c));
    },

    /**
     * Calculate vector triple product
     * a x (b x c)
     *
     * @param {Array} a
     * @param {Array} b
     * @param {Array} c
     * @returns {Array}
     * @access public
     */
    vectorTripleProduct: function(a, b, c) {
        return this.cross(a, this.cross(b, c));
    },

    // ANGLE

    /**
     * Return the angle between two vectors on a 2D plane
     * The angle is from vector 1 to vector 2, positive counterclockwise
     * The result is between {-PI, PI}
     *
     * @todo extend to multiple dimensions
     * @param {Array} p1 - [x, y]
     * @param {Array} p2 - [x, y]
     * @returns {integer}
     * @access public
     */
    angleBetween : function(p1, p2) {
        var dtheta
            , twoPi = 2 * Math.PI;

        dtheta = Math.atan2(p1[1], p1[0]) - Math.atan2(p2[1], p2[0]);

        // normalization to {-PI, PI}
        while (dtheta > Math.PI) dtheta -= twoPi;
        while (dtheta < - Math.PI) dtheta += twoPi;

        return - dtheta;
    },

    /**
     * Angle between beginning of coordinate system and vector
     * The result is between {0, PI}
     *
     * @param {Array} a
     * @returns {Number}
     * @access public
     */
    angle: function(a) {
        return Math.acos(a[0] / this.norm(a));
    },

    // MATRIX TRANSFORMATIONS

    /**
     * Linear transformation of vector x by matrix A
     * 
     * @param {Array} x - vector
     * @param {Array} M - transformation matrix
     * @return {Array}
     * @access public
     */
    transform: function(x, M) {
        var y = [];
        for (var row = 0; row < M.length; row++)
            y[row] = this.sumOfProducts(M[row], x);
        
        return y;
    },
    
    /**
     * Vector rotation
     *
     * @param {Array} x - vector
     * @param {integer} degree - [-PI, PI]
     * @param {boolean} counteClockwise - true|false
     * @returns {integer}
     * @access public
     */
    rotate: function(x, degree, counteClockwise) {
        var direction = (counteClockwise) ? -1 : 1;
        var M = [
            [ Math.cos(degree), direction * Math.sin(degree) ],
            [ - direction * Math.sin(degree), Math.cos(degree) ]
        ];
        return this.transform(x, M);
    },

    /**
     * Vector shearing
     *
     * @param {Array} x - vector
     * @param {integer} k - shear factor
     * @param {boolean} parallelToX - shearing parallel to x (true) or y (false)
     * @returns {integer}
     * @access public
     */
    shear: function(x, k, parallelToX) {
        var M = [
            [ 1, (parallelToX) ? k : 0 ],
            [ (parallelToX) ? 0 : k, 1 ]
        ];

        return this.transform(x, M);
    },
    
    /**
     *
     */
    __arraySum: function(n) {
        return n.reduce(function(prev, curr){
            return prev + curr;
        });
    }
};
return {
        VectorAlgebra: VectorAlgebra
    };
});
//exports = module.exports = VectorAlgebra;