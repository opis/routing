<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013 Marius Sarca
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

class Compiler implements CompilerInterface
{    
    
    public function compile($value, array $placeholders = array(), $delimit = true)
    {
        foreach($placeholders as $key => $pattern)
        {
            $value = str_replace('/{'.$key.'}', '/(?P<' . $key . '>(' . $pattern . '))', $value);
            $value = str_replace('/{' . $key. '?}', "(?:/(?P<{$key}>({$pattern})))?", $value);
        }
        
        $value = preg_replace('/\/\{([^?]+)\}/', '/(?P<$1>([a-zA-Z0-9\.\,\-_%=]+))', $value);
        $value = preg_replace('/\/\{([^?]+)\?\}/', '(?:/(?P<$1>([a-zA-Z0-9\.\,\-_%=]+)))?', $value);
        
        if(preg_match('/^\{([^?]+)(\?)?\}/', $value, $match))
        {
            $key = $match[1];
            $suffix = isset($match[2]) ? '?' : '';
            $start = strlen($key) + ($suffix === '' ? 2 : 3);
            if(isset($placeholders[$key]))
            {
                $prefix = '(/?(?P<' . $key . '>(' . $placeholders[$key] . '))'.$suffix.')' . $suffix;
            }
            else
            {
                $prefix = '(/?(?P<$1>([a-zA-Z0-9\.\,\-_%=]+))'.$suffix.')' . $suffix;
            }
            
            $value = $prefix . substr($value, $start);
        }
        
        return $delimit ? $this->delimit($value) : $value;
    }
    
    public function names($pattern)
    {
        preg_match_all('/\{(.*?)\}/', $pattern, $matches);
        
        return array_map(function($m) { return trim($m, '?'); }, $matches[1]);
    }
    
    public function values($pattern, $path)
    {
        
        preg_match($pattern, $path, $parameters);
       
        $parameters = array_slice($parameters, 1);
        
        if(count($parameters) === 0)
        {
            return array();
        }
        
        $keys = array_filter(array_keys($parameters), function($value){
            return is_string($value) && strlen($value) > 0;
        });
        
        return array_intersect_key($parameters, array_flip($keys));
    }
    
    public function extract(array $names, array $values, array $defaults = array())
    {
        
        $parameters = array_intersect_key($values, array_flip($names));
        
        foreach($names as $key)
        {
            if(isset($defaults[$key]) && (!isset($parameters[$key]) || empty($parameters[$key])))
            {
                $parameters[$key] = $defaults[$key];
            }
        }
        
        return $parameters;
    }
    
    public function bind(array $values, array $bindings)
    {
        foreach($values as $key => &$value)
        {
            if(isset($bindings[$key]))
            {
                $value = $bindings[$key]($value);
            }
        }
        return $values;
    }
    
    public function build($pattern, array $values = array())
    {
        $names = $this->names($pattern);
        foreach($names as $name)
        {
            if(isset($values[$name]))
            {
                $pattern = str_replace('{'.$name.'}', $values[$name], $pattern);
                $pattern = str_replace('{'.$name.'?}', $values[$name], $pattern);
            }
        }
        return $pattern;
    }
    
    public function delimit($value)
    {
        return trim($value) == '' ? null : '#^'.$value.'$#u';
    }
    
    public function htmlencode($compiled)
    {
        return htmlspecialchars($compiled);
    }
    
}