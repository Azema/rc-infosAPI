<?php

spl_autoload_register(function($className) {
    if (strpos($className, 'Rca\\') === 0) {
    	$className = str_replace('\\', '/', $className) . '.php';
    	if (file_exists(APPLICATION_PATH . '/library/' . $className)) {
    		require_once APPLICATION_PATH . '/library/' . $className;
    	} else {
	        $className = substr($className, 4);
    	    require_once $className;
    	}
        return true;
    }
    return false;
}, true, true);