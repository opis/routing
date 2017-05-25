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

use Closure;
use Opis\Closure\SerializableClosure;

trait ClosureWrapperTrait
{
    /**
     * Wrap all closures
     *
     * @param   mixed   $value
     *
     * @return  mixed
     */
    protected static function wrapClosures($value)
    {
        if ($value instanceof Closure) {
            return SerializableClosure::from($value);
        } elseif (is_array($value)) {
            return array_map(static::class . '::' . __FUNCTION__, $value);
        } elseif ($value instanceof \stdClass) {
            return (object) array_map(static::class . '::' . __FUNCTION__, (array) $value);
        }
        return $value;
    }

    /**
     * Unwrap closures
     *
     * @param   mixed   $value
     *
     * @return  mixed
     */
    protected static function unwrapClosures($value)
    {
        if ($value instanceof SerializableClosure) {
            return $value->getClosure();
        } elseif (is_array($value)) {
            return array_map(static::class . '::' . __FUNCTION__, $value);
        } elseif ($value instanceof \stdClass) {
            return (object) array_map(static::class . '::' . __FUNCTION__, (array) $value);
        }
        return $value;
    }
}