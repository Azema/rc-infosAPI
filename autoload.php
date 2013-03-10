<?php

spl_autoload_register(function($className) {
    if (strpos($className, 'Rca\\') === 0) {
        $className = substr($className, 4);
        require_once str_replace('\\', '/', $className) . '.php';
        return true;
    }
    return false;
});