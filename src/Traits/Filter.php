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

trait Filter
{
    /** @var array */
    private $placeholders = [];

    /** @var callable[] */
    private $guards = [];

    /** @var callable[] */
    private $filters = [];

    /**
     * Get placeholders
     *
     * @return  array
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * Get filters
     *
     * @return callable[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get guards
     *
     * @return callable[]
     */
    public function getGuards(): array
    {
        return $this->guards;
    }

    /**
     * Add a placeholder
     *
     * @param string $name
     * @param $value
     * @return static
     */
    public function placeholder(string $name, $value): self
    {
        $this->placeholders[$name] = $value;
        return $this;
    }

    /**
     * Add global filter
     *
     * @param string $name
     * @param callable|null $callback
     * @return static
     */
    public function filter(string $name, callable $callback = null): self
    {
        $this->filters[$name] = $callback;
        return $this;
    }

    /**
     * Add global guard
     *
     * @param string $name
     * @param callable|null $callback
     * @return static
     */
    public function guard(string $name, callable $callback = null): self
    {
        $this->guards[$name] = $callback;
        return $this;
    }
}