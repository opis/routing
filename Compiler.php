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
    
    protected $startTag = '{';
    
    protected $endTag = '}';
    
    protected $separator = '/';
    
    protected $precede = false;
    
    protected $optional = '?';
    
    protected $delimiter = '`';
    
    protected $modifier = 'u';
    
    protected $wildcard = '[a-zA-Z0-9\.\,\-_%=]+';
    
    protected $comp;
    
    public function __construct()
    {
        $this->comp = array(
            'st' => preg_match($this->startTag, $this->delimiter),
            'et' => preg_match($this->endTag, $this->delimiter),
            'sep' => preg_match($this->separator, $this->delimiter),
            'opt' => preg_match($this->optional, $this->delimiter)
        );
    }
    
    public function compile($value, array $placeholders = array(), $delimit = true)
    {
        $value = preg_quote($value, $this->delimiter);
        
        list($st, $et, $sep, $opt) = $this->comp;
        
        foreach($placeholders as $key => $pattern)
        {
            $key = preg_quote($key, $this->delimiter);
            $pattern = '(?P<' . $key . '>(' . $pattern .'))';
            if($this->precede)
            {
                $value = str_replace($sep . $st . $key . $et, $sep . $pattern, $value);
                $value = str_replace($sep . $st . $key . $opt . $et, '(?:' . $sep . $pattern .')?', $value);
            }
            else
            {
                $value = str_replace($st . $key . $et . $sep, $pattern . $sep, $value);
                $value = str_replace($st . $key . $opt . $et . $sep, '(' . $pattern . $sep . ')?', $value);
            }
        }
        
        $wild = '(?P<$1>(' . $this->wildcard . '))';
        if($this->precede)
        {
            $pfx = $this->delimiter . $sep . $st . '([^' . $opt . ']+)';
            $sfx = $et . $this->delimiter;
            $wildOpt = '(?:' . $sep . $wild .')?';
        }
        else
        {
            $pfx = $this->delimiter . $st . '([^' . $opt . ']+)';
            $sfx = $sep . $et . $this->delimiter;
            $wildOpt = '(' . $wild . $sep . ')?';
        }
        
        $value = preg_replace($pfx . $sfx, $sep . $wild, $value);
        $value = preg_replace($pfx . $opt . $sfx, $wildOpt, $value);
        
        return $value;
    }
    
    public function names($pattern)
    {
        $regex = $this->delimiter . $this->comp['st'] . '(.*)' . $this->comp['et'] . $this->delimiter;
        
        preg_match_all($regex, $pattern, $matches);
        
        return array_map(function($m) { return trim($m, $this->optional); }, $matches[1]);
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
        
        $result = array();
        
        foreach($names as $key)
        {
            if(isset($parameters[$key]))
            {
                $result[$key] = $parameters[$key];
            }
            else
            {
                $result[$key] = isset($defaults[$key]) ? $defaults[$key] : null;
            }
        }
        
        return $result;
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
                $pattern = str_replace($this->startTag . $name . $this->endTag, $values[$name], $pattern);
                $pattern = str_replace($this->startTag . $name . $this->optional . $this->endTag, $values[$name], $pattern);
            }
        }
        return $pattern;
    }
    
    public function delimit($value)
    {
        return $this->delimiter . '^' . $value . '$' . $this->delimit . $this->modifier;
    }
    
}