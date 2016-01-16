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

use Serializable;

class Compiler implements Serializable
{    
    
    const CAPTURE_LEFT = 0;
    
    const CAPTURE_RIGHT = 1;
    
    const CAPTURE_TRAIL = 2;
    
    const OPT_SEPARATOR_TRAIL = 4;
    
    const STANDARD_MODE = 6;
    
    protected $startTag;
    
    protected $endTag;
    
    protected $separator;
    
    protected $captureLeft;
    
    protected $captureTrail;
    
    protected $addOptionalSeparator;
    
    protected $optional;
    
    protected $delimiter;
    
    protected $modifier;
    
    protected $wildcard;
    
    protected $comp;
    
    public function __construct($startTag = '{',
                                $endTag = '}',
                                $separator = '/',
                                $optional = '?',
                                $capture = self::STANDARD_MODE,
                                $delimiter = '`',
                                $modifier = 'u',
                                $wildcard = '[a-zA-Z0-9\.\,\-_%=]+')
    {
        
        $capture = (int) $capture;
        $this->startTag = $startTag;
        $this->endTag = $endTag;
        $this->separator = $separator;
        $this->optional = $optional;
        $this->captureLeft = ($capture & Compiler::CAPTURE_RIGHT) === Compiler::CAPTURE_LEFT;
        $this->captureTrail = ($capture & Compiler::CAPTURE_TRAIL) === Compiler::CAPTURE_TRAIL;
        $this->addOptionalSeparator = ($capture & Compiler::OPT_SEPARATOR_TRAIL) === Compiler::OPT_SEPARATOR_TRAIL;
        $this->delimiter = $delimiter;
        $this->modifier = $modifier;
        $this->wildcard = $wildcard;
        $this->comp = array(
            preg_quote($startTag, $delimiter),
            preg_quote($endTag, $delimiter),
            preg_quote($separator, $delimiter),
            preg_quote($optional, $delimiter)
        );
    }
    
    public function compile(Pattern $value, array $placeholders = array())
    {
        $names = $this->names($value);
        
        if(empty($names))
        {
            $value = preg_quote($value, $this->delimiter);
            list($st, $et, $sep, $opt) = $this->comp;
            goto TRAIL;
        }
        
        $wildcard = $this->wildcard;
        
        $names = array_map(function($name) use(&$wildcard){ return $wildcard; }, array_flip($names));
        
        $placeholders += $names;
        
        $value = preg_quote($value, $this->delimiter);
        
        list($st, $et, $sep, $opt) = $this->comp;
        
        $unmatched = array();
        
        $position = -1;
        
        foreach($placeholders as $key => $pattern)
        {
            $original = $key;
            $key = preg_quote($key, $this->delimiter);
            $pattern = '(?P<' . $key . '>(' . $pattern .'))';
            $count = 0;
            $position++;
            if($this->captureLeft)
            {
                $value = str_replace($sep . $st . $key . $et, $sep . $pattern, $value, $count);
                
                if($count == 0)
                {
                    if($position === 0 && strpos($value, $sep . $st . $key . $opt . $et) === 0)
                    {
                        $value = str_replace($sep . $st . $key . $opt . $et, '(' . $sep . $pattern .'?)?', $value, $count);
                    }
                    else
                    {
                        $value = str_replace($sep . $st . $key . $opt . $et, '(?:' . $sep . $pattern .')?', $value, $count);
                    }
                }
            }
            else
            {
                $value = str_replace($st . $key . $et . $sep, $pattern . $sep, $value, $count);
                if($count == 0)
                {
                    $value = str_replace($st . $key . $opt . $et . $sep, '(' . $pattern . $sep . ')?', $value, $count);
                }
            }
            if($count == 0)
            {
                $unmatched[$original] = $pattern;
            }
        }
        
        if(!empty($unmatched))
        {
            foreach($unmatched as $key => $pattern)
            {
                if($this->addOptionalSeparator)
                {
                    $pattern = $this->captureLeft ?  '(' . $sep . ')?' . $pattern : $pattern . '(' . $sep . ')?';
                }
                
                
                $value = str_replace($st . $key . $et, $pattern, $value, $count);
                
                if($count == 0)
                {
                    $value = str_replace($st . $key . $opt . $et, '('. $pattern . ')?', $value);
                }
            }
        }
        
        TRAIL:
        
        if($this->captureTrail)
        {
            if($this->captureLeft)
            {   
                if(substr($value, strlen($value) - strlen($sep)) !== $sep)
                {
                    $value = $value . '(' . $sep . ')?';
                }
            }
            else
            {
                if(substr($value, 0, strlen($sep)) !== $sep)
                {
                    $value = '(' . $sep . ')?' . $value;
                }
            }
            
        }
        
        return $value;
    }
    
    public function names(Pattern $pattern)
    {
        list($st, $et) = $this->comp;
        
        $regex = $this->delimiter . $st . '(.*?)' . $et . $this->delimiter;
        
        preg_match_all($regex, $pattern, $matches);
        
        $optional = $this->optional;
        
        return array_map(function($m) use(&$optional) { return trim($m, $optional); }, $matches[1]);
    }
    
    public function values($pattern, Path $path)
    {
        
        preg_match($this->delimit($pattern), $path, $parameters);
       
        $parameters = array_slice($parameters, 1);
        
        if(count($parameters) === 0)
        {
            return array();
        }
        
        $keys = array_filter(array_keys($parameters), function($value) use (&$parameters){
            return is_string($value) && strlen($value) > 0 && $parameters[$value] != null;
        });
        
        return array_intersect_key($parameters, array_flip($keys));
    }
    
    public function extract(array $names, array $values, array $defaults = array())
    {
        return array_intersect_key($values, array_flip($names)) + $defaults;
    }
    
    public function bind(array $values, array $bindings, array $specials = array())
    {
        $binded = array();
        
        foreach($bindings as $key => $callback)
        {   
            $callback = new Callback($callback);
            $arguments = $callback->getArguments($values, $specials, false);
            $binded[$key] = new Binding($callback, $arguments);
        }
        
        foreach($values as $key => $value)
        {
            if(!isset($binded[$key]))
            {
                $binded[$key] = new Binding(null, null, $value);
            }
        }
        
        return $binded;
    }
    
    public function build(Pattern $pattern, array $values = array())
    {
        $names = $this->names($pattern);
        foreach($names as $name)
        {
            if(isset($values[$name]))
            {
                $pattern = str_replace($this->startTag . $name . $this->endTag, $values[$name], $pattern, $count);
                if($count == 0)
                {
                    $pattern = str_replace($this->startTag . $name . $this->optional . $this->endTag, $values[$name], $pattern);
                }
            }
        }
        return $pattern;
    }
    
    public function delimit($value)
    {
        return $this->delimiter . '^' . $value . '$' . $this->delimiter . $this->modifier;
    }
    
    public function serialize()
    {
        return serialize(array(
            'startTag' => $this->startTag,
            'endTag' => $this->endTag,
            'separator' => $this->separator,
            'captureLeft' => $this->captureLeft,
            'captureTrail' => $this->captureTrail,
            'addOptionalSeparator' => $this->addOptionalSeparator,
            'optional' => $this->optional,
            'delimiter' => $this->delimiter,
            'modifier' => $this->modifier,
            'wildcard' => $this->wildcard,
            'comp' => $this->comp,
        ));
    }
    
    public function unserialize($data)
    {
        $object = unserialize($data);
        
        foreach($object as $key => $value)
        {
            $this->{$key} = $value;
        }
    }
}
