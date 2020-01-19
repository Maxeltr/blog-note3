<?php

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

namespace MxmUser\Validator;

use Laminas\Validator\Identical;

class IdenticalStrings extends Identical {

    /**
     * Validator options
     *
     * @var array
     */
    protected $options = [
        'encoding' => null
    ];

    /**
     * Sets validator options
     *
     * @param  mixed $token
     */
    public function __construct($token = null) {
        parent::__construct($token);
    }

    /**
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @param  array|ArrayAccess $context
     * @throws Exception\InvalidArgumentException If context is not array or ArrayObject
     * @return bool
     */
    public function isValid($value, $context = null) {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException('The value is not scalar');
        }
        $this->setValue((string) $value);

        $token = $this->getToken();

        if (!$this->getLiteral() && $context !== null) {
            if (!is_array($context) && !($context instanceof ArrayAccess)) {
                throw new Exception\InvalidArgumentException(sprintf(
                                'Context passed to %s must be array, ArrayObject or null; received "%s"',
                                __METHOD__,
                                is_object($context) ? get_class($context) : gettype($context)
                ));
            }

            if (is_array($token)) {
                while (is_array($token)) {
                    $key = key($token);
                    if (!isset($context[$key])) {
                        break;
                    }
                    $context = $context[$key];
                    $token = $token[$key];
                }
            }

            // if $token is an array it means the above loop didn't went all the way down to the leaf,
            // so the $token structure doesn't match the $context structure
            if (is_array($token) || !isset($context[$token])) {
                $token = $this->getToken();
            } else {
                $token = $context[$token];
            }
        }

        if ($token === null) {
            $this->error(self::MISSING_TOKEN);
            return false;
        }

        if (!is_scalar($token)) {
            throw new InvalidArgumentException('The token is not scalar');
        }
        $token = (string) $token;

        $strict = $this->getStrict();
        if (($strict && ! $this->strictCompare($value, $token)) || (! $strict && ! $this->compare($value, $token))) {
            $this->error(self::NOT_SAME);
            return false;
        }

        return true;
    }

    private function compare($value, $token) {
        if (null !== $this->getEncoding()) {
            $value = mb_strtolower($value, $this->options['encoding']);
            $token = mb_strtolower($token, $this->options['encoding']);
        } else {
            $value = strtolower($value);
            $token = strtolower($token);
        }

        return $value == $token;
    }

    private function strictCompare($value, $token) {
        return $value === $token;
    }

    /**
     * Set the input encoding for the given string
     *
     * @param  string|null $encoding
     * @return self
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ExtensionNotLoadedException
     */
    public function setEncoding($encoding = null) {
        if ($encoding !== null) {
            if (!function_exists('mb_strtolower')) {
                throw new Exception\ExtensionNotLoadedException(sprintf(
                                '%s requires mbstring extension to be loaded',
                                get_class($this)
                ));
            }

            $encoding = strtolower($encoding);
            $mbEncodings = array_map('strtolower', mb_list_encodings());
            if (!in_array($encoding, $mbEncodings)) {
                throw new Exception\InvalidArgumentException(sprintf(
                                "Encoding '%s' is not supported by mbstring extension",
                                $encoding
                ));
            }
        }

        $this->options['encoding'] = $encoding;
        return $this;
    }

    /**
     * Returns the set encoding
     *
     * @return string
     */
    public function getEncoding() {
        if ($this->options['encoding'] === null && function_exists('mb_internal_encoding')) {
            $this->options['encoding'] = mb_internal_encoding();
        }

        return $this->options['encoding'];
    }

}
