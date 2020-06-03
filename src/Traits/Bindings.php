<?php
/* ===========================================================================
 * Copyright 2020 Zindex Software
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

namespace Opis\Routing\Traits;

trait Bindings
{
    /** @var callable[] */
    private $bindings = [];

    /** @var array */
    private $defaults = [];

    /**
     * Get bindings
     *
     * @return  callable[]
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Get default values
     *
     * @return  array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Binding
     *
     * @param   string $name
     * @param   callable $callback
     * @return  static
     */
    public function bind(string $name, callable $callback): self
    {
        $this->bindings[$name] = $callback;
        return $this;
    }

    /**
     * Set a default value
     *
     * @param   string $name
     * @param   mixed $value
     * @return  static
     */
    public function implicit(string $name, $value): self
    {
        $this->defaults[$name] = $value;
        return $this;
    }
}