<?php

namespace PHPLegends\Routes;

class ActionInspector
{

    const TYPE_STATIC_METHOD = 1;

    const TYPE_CLOSURE = 2;

    const TYPE_DINAMIC_METHOD = 3;

    const TYPE_FUNCTION = 4;

    /**
     * 
     * @param Closure|string $action
     * @return string
     * */
    public static function getType($action)
    {
        if  ($action instanceof \Closure) {

            return static::TYPE_CLOSURE;              

        } elseif (function_exists($action)) {

            return static::TYPE_FUNCTION;
        }

        if (strpos($action, '::') === false) {

            throw new \UnexpectedValueException('Invalid value for action');
        }

        $reflection = new \ReflectionMethod($action);

        if ($reflection->isAbstract() || ! $reflection->isPublic()) {

            throw new \UnexpectedValueException('Action cannot use abstract or private methods');   
        }

        if ($reflection->isStatic()) {

            return static::TYPE_STATIC_METHOD;
        } 

        return static::TYPE_DINAMIC_METHOD;
    }

    public static function castToCallable($action)
    {
        $type = static::getType($action);

        if (in_array($type, [static::TYPE_FUNCTION, static::TYPE_CLOSURE])) {

            return $action;

        }  elseif ($type === static::TYPE_STATIC_METHOD) {

            return explode('::', $action);
        }

        list($class, $method) = explode('::', $action);

        return [new $class, $method];

    }
}