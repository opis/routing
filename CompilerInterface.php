<?php

namespace Opis\Routing;

interface CompilerInterface
{
    function compile($pattern, array $where = array(), $delimit = true);
    
    function delimit($compiled);
    
    function names($pattern);
    
    function values($pattern, $path);
    
    function htmlencode($value);
    
    function extract(array $names, array $values, array $defaults = array());
    
    function bind(array $values, array $bindings);
}