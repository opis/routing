<?php

namespace Opis\Routing;

class Compiler implements CompilerInterface
{    
    
    public function compile($value, array $wheres = array(), $delimit = true)
    {
        foreach($wheres as $key => $pattern)
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
            if(isset($wheres[$key]))
            {
                $prefix = '(/?(?P<' . $key . '>(' . $wheres[$key] . '))'.$suffix.')' . $suffix;
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
    
    public function delimit($value)
    {
        return trim($value) == '' ? null : '#^'.$value.'$#u';
    }
    
    public function htmlencode($compiled)
    {
        return htmlspecialchars($compiled);
    }
    
}