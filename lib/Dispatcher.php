<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2015 Marius Sarca
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

class Dispatcher
{

    public function dispatch(Router $router, Path $path, Route $route)
    {
        return $this->call($route->getAction(), $route->compile()->bind($path), $router->getSpecialValues());
    }

    protected function call($action, array $values, array $specials = array())
    {
        $arguments = array();
        $callback = new Callback($action);
        $parameters = $callback->getParameters();

        foreach ($parameters as $param) {
            $name = $param->getName();
            
            if (isset($values[$name])) {
                $arguments[] = $values[$name]->value();
            } elseif (isset($specials[$name])) {
                $arguments[] = $specials[$name];
            } elseif ($param->isOptional()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                $arguments[] = null;
            }
        }
        
        return $callback->invoke($arguments);
    }
}
