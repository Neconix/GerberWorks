<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


use Exception;

class ParseException extends BaseException
{
    public function __construct($message, $code = 0, Exception $previous = null, $line = '')
    {
        parent::__construct($message, $code, $previous);
    }
}
