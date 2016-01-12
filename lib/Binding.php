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

class Binding
{
    protected $value;
    protected $arguments;
    protected $callback;
    
    public function __construct(Callback $callback = null, array $arguments = null, $value = null)
    {
        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->value = $value === null ? $this : $value;
    }
    
    public function value()
    {
        if($this->value === $this)
        {
            $this->value = $this->callback->invoke($this->arguments);
        }
        
        return $this->value;
    }
}
