<?php

/**
 * Ohanzee Components by Kohana
 *
 * @package    Ohanzee
 * @author     Se#
 * @copyright  2007-2014 Kohana Team
 * @link       http://ohanzee.org/
 * @license    http://ohanzee.org/license
 * @version    0.1.0
 *
 * BSD 2-CLAUSE LICENSE
 * 
 * This license is a legal agreement between you and the Kohana Team for the use
 * of Kohana Framework and Ohanzee Components (the "Software"). By obtaining the
 * Software you agree to comply with the terms and conditions of this license.
 * 
 * Copyright (c) 2007-2014 Kohana Team
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 * 1) Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * 2) Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Ohanzee\Helper;

trait Arr
{
    /**
     * @var  string  default delimiter for path()
     */
    public static $delimiter = '.';
    
    /**
     * @var  string  default value for the missed key
     */
    public static $default = 'not found';
    
    /**
     * @var string array source property
     */
    public static $arraySrcProperty = 'data';
    
    /**
     * Gets a value from an array-property using a dot separated path.
     *
     *     // Get the value of $array['foo']['bar']
     *     $value = $this->{'foo.bar'};
     *
     * Using a wildcard "*" will search intermediate arrays and return an array.
     *
     *     // Get the values of "color" in theme
     *     $colors = $this->{'theme.*.color'};
     *
     * @param   mixed   $path       key path string (delimiter separated) or array of keys
     * @return  mixed
     */
    public function __get($path)
    {
        $array = $this->{static::$arraySrcProperty};
        
        if (!static::isArray($array)) {
            // This is not an array!
            return $default;
        }
   
        if (array_key_exists($path, $array)) {
            // No need to do extra processing
            return $array[$path];
        }

        $delimiter = static::$delimiter;

        // Remove starting delimiters and spaces
        $path = ltrim($path, "{$delimiter} ");

        // Remove ending delimiters, spaces, and wildcards
        $path = rtrim($path, "{$delimiter} *");

        // Split the keys by delimiter
        $keys = explode($delimiter, $path);

        do {
            $key = array_shift($keys);

            if (ctype_digit($key)) {
                // Make the key an integer
                $key = (int) $key;
            }

            if (isset($array[$key])) {
                if (!$keys) {
                    // Found the path requested
                    return $array[$key];
                }
                if (!static::isArray($array[$key])) {
                    // Unable to dig deeper
                    break;
                }
                // Dig down into the next part of the path
                $array = $array[$key];
            } elseif ($key === '*') {
                // Handle wildcards
                $values = array();
                foreach ($array as $arr) {
                    if ($value = static::path($arr, implode('.', $keys))) {
                        $values[] = $value;
                    }
                }

                if ($values) {
                    // Found the values requested
                    return $values;
                }
                // Unable to dig deeper
                break;
            } else {
                // Unable to dig deeper
                break;
            }
        } while ($keys);

        // Unable to find the value requested
        return static::$default;
    }
    
    /**
     * Test if a value is an array with an additional check for array-like objects.
     *
     *     // Returns true
     *     Arr::isArray(array());
     *     Arr::isArray(new ArrayObject);
     *
     *     // Returns false
     *     Arr::isArray(false);
     *     Arr::isArray('not an array!');
     *     Arr::isArray(Database::instance());
     *
     * @param   mixed   $value  value to check
     * @return  boolean
     */
    public static function isArray($value)
    {
        if (is_array($value)) {
            return true;
        }
        // Traversable is the interface that makes an object foreach'able,
        // it is implemented by the SPL Iterator and IteratorAggregate classes.
        return (is_object($value) && $value instanceof Traversable);
    }
}
