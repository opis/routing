<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

use RuntimeException;
use Opis\Routing\Traits\{
    Filter as FilterTrait,
    Bindings as BindingTrait
};
use Opis\Utils\RegexBuilder;

class Route
{
    use FilterTrait{
        getPlaceholders as getLocalPlaceholders;
        filter as private setFilter;
        guard as private setGuard;
        placeholder as private setPlaceholder;
    }
    use BindingTrait {
        getBindings as getLocalBindings;
        getDefaults as getLocalDefaults;
        bind as private setBinding;
        implicit as private setImplicit;
    }

    /** @var  RouteCollection */
    private $collection;

    /** @var string */
    private $pattern;

    /** @var callable */
    private $action;

    /** @var string|null */
    private $name;

    private int $priority = 0;

    /** @var string */
    private $id;

    /** @var string[] */
    private array $method;

    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @var array
     */
    private array $properties = [];

    private bool $inheriting = false;

    /**
     * Route constructor.
     * @param RouteCollection $collection
     * @param string $id
     * @param string $pattern
     * @param callable $action
     * @param string[] $method
     * @param int $priority
     * @param string|null $name
     */
    public function __construct(
        RouteCollection $collection,
        string $id,
        string $pattern,
        callable $action,
        array $method = ['GET'],
        int $priority = 0,
        string $name = null
    ) {
        $this->collection = $collection;
        $this->id = $id;
        $this->pattern = $pattern;
        $this->action = $action;
        $this->name = $name;
        $this->priority = $priority;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * Get the route's pattern
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Get the route's callback
     *
     * @return  callable
     */
    public function getAction(): callable
    {
        return $this->action;
    }

    /**
     * Get the name of the route
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->collection;
    }

    /**
     * @return string[]
     */
    public function getMethod(): array
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return array
     */
    public function getDefaults(): array
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__] = $this->getLocalDefaults() + $this->collection->getDefaults();
        }

        return $this->cache[__FUNCTION__];
    }

    /**
     * @return callable[]
     */
    public function getBindings(): array
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__] = $this->getLocalBindings() + $this->collection->getBindings();
        }

        return $this->cache[__FUNCTION__];
    }

    /**
     * @return array
     */
    public function getPlaceholders(): array
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__] = $this->getLocalPlaceholders() + $this->collection->getPlaceholders();
        }

        return $this->cache[__FUNCTION__];
    }

    public function bind(string $name, callable $callback): self
    {
        if ($this->inheriting && isset($this->getLocalBindings()[$name])) {
            return $this;
        }

        return $this->setBinding($name, $callback);
    }

    public function placeholder(string $name, $value): self
    {
        if ($this->inheriting && isset($this->getLocalPlaceholders()[$name])) {
            return $this;
        }

        return $this->setPlaceholder($name, $value);
    }

    public function implicit(string $name, $value): self
    {
        if ($this->inheriting && array_key_exists($name, $this->getLocalDefaults())) {
            return $this;
        }

        return $this->setImplicit($name, $value);
    }

    public function filter(string $name, callable $callback = null): self
    {
        if ($this->inheriting && array_key_exists($name, $this->filters)) {
            return $this;
        }

        return $this->setFilter($name, $callback);
    }

    public function guard(string $name, callable $callback = null): self
    {
        if ($this->inheriting && array_key_exists($name, $this->guards)) {
            return $this;
        }

        return $this->setGuard($name, $callback);
    }

    /**
     * @param string ...$middleware
     * @return static
     */
    public function middleware(string ...$middleware): self
    {
        if ($this->inheriting && isset($this->properties['middleware'])) {
            return $this;
        }
        $this->properties['middleware'] = $middleware;
        return $this;
    }

    /**
     * @param string $value
     * @return static
     */
    public function domain(string $value): self
    {
        if ($this->inheriting && isset($this->properties['domain'])) {
            return $this;
        }
        $this->properties['domain'] = $value;
        return $this;
    }

    /**
     * @param bool $value
     * @return static
     */
    public function secure(bool $value = true): self
    {
        if ($this->inheriting && isset($this->properties['secure'])) {
            return $this;
        }

        $this->properties['secure'] = $value;
        return $this;
    }

    /**
     * Define a new placeholder
     *
     * @param   string $name
     * @param   string $value
     * @return  static|Route
     */
    public function where(string $name, string $value): self
    {
        return $this->placeholder($name, $value);
    }

    /**
     * @param string $name
     * @param string[] $values
     * @return static|Route
     */
    public function whereIn(string $name, array $values): self
    {
        if (empty($values)) {
            return $this;
        }

        $delimiter = $this->collection->getRegexBuilder()->getOptions()[RegexBuilder::REGEX_DELIMITER];

        $value = implode('|', array_map(function ($value) use ($delimiter) {
            return preg_quote($value, $delimiter);
        }, $values));

        return $this->placeholder($name, $value);
    }

    /**
     * @param string $name
     * @param array|null $config
     * @return static
     */
    public function mixin(string $name, array $config = null): self
    {
        $collection = $this->getRouteCollection();
        $mixins = $collection->getMixins();
        if (!isset($mixins[$name])) {
            throw new RuntimeException("Unknown mixin name " . $name);
        }
        $mixins[$name]($this, $config);
        return $this;
    }

    public function __serialize(): array
    {
        return [
            'collection' => $this->collection,
            'pattern' => $this->pattern,
            'action' => $this->action,
            'method' => $this->method,
            'name' => $this->name,
            'priority' => $this->priority,
            'id' => $this->id,
            'properties' => $this->properties,
            'placeholders' => $this->placeholders,
            'filters' => $this->filters,
            'guards' => $this->guards,
            'bindings' => $this->bindings,
            'defaults' => $this->defaults,
        ];
    }

    public static function setIsInheriting(Route $route, bool $value)
    {
        $route->inheriting = $value;
    }
}
