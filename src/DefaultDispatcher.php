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

use Opis\Http\{Request, Responses\HtmlResponse, Response};

class DefaultDispatcher implements Dispatcher
{
    use Traits\Dispatcher;

    /** @var callable */
    private $httpError = null;

    public function __construct(callable $httpError = null)
    {
        if ($httpError === null) {
            $httpError = fn(int $code) => new Response($code);
        }
        $this->httpError = $httpError;
    }

    /**
     * @param Router $router
     * @return mixed
     */
    public function dispatch(Router $router, Request $request): Response
    {
        $route = $this->findRoute($router, $request);

        if ($route === null) {
            return ($this->httpError)(404);
        }

        $invoker = $router->resolveInvoker($route, $request);
        $guards = $route->getRouteCollection()->getGuards();

        /**
         * @var string $name
         * @var callable|null $callback
         */
        foreach ($route->getGuards() as $name => $callback) {
            if ($callback === null) {
                if (!isset($guards[$name])) {
                    continue;
                }
                $callback = $guards[$name];
            }

            $args = $invoker->getArgumentResolver()->resolve($callback);

            if (false === $callback(...$args)) {
                return ($this->httpError)(404);
            }
        }

        $list = $route->getProperties()['middleware'] ?? [];

        if (empty($list)) {
            $result = $invoker->invokeAction();
            if (!$result instanceof Response) {
                $result = new HtmlResponse($result);
            }
            return $result;
        }

        $queue = new \SplQueue();
        $next = function () use ($queue, $invoker) {
            do {
                if ($queue->isEmpty()) {
                    $result = $invoker->invokeAction();
                    if (!$result instanceof Response) {
                        $result = new HtmlResponse($result);
                    }
                    return $result;
                }
                /** @var Middleware $middleware */
                $middleware = $queue->dequeue();
            } while (!is_callable($middleware));

            $args = $invoker->getArgumentResolver()->resolve($middleware);
            $result = $middleware(...$args);
            if (!$result instanceof Response) {
                $result = new HtmlResponse($result);
            }
            return $result;
        };

        foreach ($list as $item) {
            if (is_subclass_of($item, Middleware::class, true)) {
                $queue->enqueue(new $item($next));
            }
        }

        return $next();
    }
}