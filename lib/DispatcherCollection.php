<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2016 Marius Sarca
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

class DispatcherCollection
{
    /** @var Dispatcher[] */
    protected $dispatchers = array();

    /** @var  Dispatcher */
    protected $defaultDispatcher;

    /**
     * DispatcherCollection constructor.
     * @param Dispatcher|null $dispatcher
     */
    public function __construct(Dispatcher $dispatcher = null)
    {
        $this->defaultDispatcher = $dispatcher;
    }

    /**
     * @param string $name
     * @param Dispatcher $dispatcher
     * @return DispatcherCollection
     */
    public function register(string $name, Dispatcher $dispatcher): self
    {
        $this->dispatchers[$name] = $dispatcher;
        return $this;
    }


    /**
     * @return Dispatcher
     */
    public function defaultDispatcher(): Dispatcher
    {
        if($this->defaultDispatcher === null){
            $this->defaultDispatcher = new Dispatcher();
        }
        return $this->dispatchers;
    }

    /**
     * @param string $name
     * @return false|Dispatcher
     */
    public function get(string $name)
    {
        return $this->dispatchers[$name] ?? $this->defaultDispatcher();
    }
}
