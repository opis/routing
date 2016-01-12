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

namespace Opis\Routing\Collections;

use Iterator;
use ArrayAccess;
use Serializable;

abstract class AbstractCollection implements Iterator, ArrayAccess, Serializable
{
    protected $collection = array();
    
    protected function checkType($value)
    {
        
    }
    
    public function rewind()
    {
        return reset($this->collection);
    }
    
    public function current()
    {
        return current($this->collection);
    }
    
    public function key()
    {
        return key($this->collection);
    }
    
    public function next()
    {
        return next($this->collection);
    }
    
    public function valid()
    {
        return key($this->collection) !== null;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->checkType($value);
        
        if (is_null($offset))
        {
            $this->collection[] = $value;
        }
        else
        {
            $this->collection[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }
    
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }
    
    public function toArray()
    {
        return $this->collection;
    }
    
    public function serialize()
    {
        return serialize($this->collection);
    }
    
    public function unserialize($data)
    {
        $this->collection = unserialize($data);
    }
    
}
