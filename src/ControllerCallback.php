<?php
/* ===========================================================================
 * Copyright 2018-2020 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Routing;

class ControllerCallback
{

    protected string $method;

    protected string $className;

    protected bool $isStatic;

    /**
     * @var self[]
     */
    protected static array $instances = [];

    /**
     * Constructor
     *
     * @param   string $class
     * @param   string $method
     * @param   boolean $static (optional)
     */
    protected function __construct(string $class, string $method, bool $static = false)
    {
        $this->className = $class;
        $this->method = $method;
        $this->isStatic = $static;
    }

    /**
     * Make the instances of this class being a valid callable value
     */
    public function __invoke()
    {

    }

    /**
     * Returns the class name
     *
     * @return  string
     */
    public function getClass(): string
    {
        return $this->className;
    }

    /**
     * Returns the param's name that references the method
     *
     * @return  string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Indicates if the referenced method is static or not
     *
     * @return  boolean
     */
    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /**
     * @param string $class
     * @param string $method
     * @param bool $static
     * @return ControllerCallback
     */
    public static function get(string $class, string $method, bool $static = false): self
    {
        $key = trim($class) . ($static ? '::' : '->') . trim($method);

        if (!isset(static::$instances[$key])) {
            static::$instances[$key] = new static($class, $method, $static);
        }

        return static::$instances[$key];
    }
}