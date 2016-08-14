<?php
/**
 * @author Neconix (prostoe@gmail.com)
 */

namespace gerberworks;


class BaseException extends \Exception
{
    /**
     * Возвращает строку с сообщениями для всех дочерних исключений
     * @return string Строка с сообщениями
     */
    public function getFlatMsg() {
        return static::flatMsg($this);
    }

    /**
     * Возвращает строку с сообщениями для всех дочерних исключений
     * @return string Строка с сообщениями
     */
    public static function flatMsg(\Exception $exception) {
        $msg = $exception->getMessage();
        $prev = $exception->getPrevious();
        while ($prev != null) {
            $msg .= ': ' . $prev->getMessage();
            $prev = $prev->getPrevious();
        }
        return $msg;
    }
}
