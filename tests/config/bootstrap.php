<?php

/**
 * @author Neconix (prostoe@gmail.com)
 */

$ps = PATH_SEPARATOR;

$includePath = get_include_path().$ps.__DIR__.'/../../api';
set_include_path($includePath);

spl_autoload_register(function($className) {
    $classBaseName = substr($className, strrpos($className, '\\') + 1);
    include "{$classBaseName}.php";
});
