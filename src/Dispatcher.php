<?php
/* ===========================================================================
 * Copyright 2013-2017 The Opis Project
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

class Dispatcher implements IDispatcher
{
    use DispatcherTrait;

    protected $compiled = [];

    public function dispatch(Router $router, Context $context)
    {
        $this->router = $router;
        $this->context = $context;

        if(null === $route = $this->findRoute()){
            return null;
        }

        return $this->compile($route)->invokeAction();
    }

    public function compile(Route $route): CompiledRoute
    {
        $cid = spl_object_hash($this->context);
        $rid = spl_object_hash($route);

        if(!isset($this->compiled[$cid][$rid])){
            return $this->compiled[$cid][$rid] = new CompiledRoute($this->context, $route, $this->getExtraVariables());
        }

        return $this->compiled[$cid][$rid];
    }
}
