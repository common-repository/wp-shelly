<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace SOSIDEE_SHELLY\SRC;
defined( 'SOSIDEE_SHELLY' ) or die( 'you were not supposed to be here' );

class Json
{
    private static $ERRORS = array(
         JSON_ERROR_NONE => 'None'
        ,JSON_ERROR_DEPTH => 'Maximum stack depth exceeded'
        ,JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch'
        ,JSON_ERROR_CTRL_CHAR => 'Unexpected control character found'
        ,JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
        ,JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        ,JSON_ERROR_RECURSION => 'Recursive reference(s), cannot be encoded'
    );
    
    public static function getError($index = -1) {
        $ret = 'Unknown';
        if ( $index < 0 ) {
            $index = json_last_error();
        }
        if ( array_key_exists($index, self::$ERRORS) ) {
            $ret = self::$ERRORS[$index];
        }
        return $ret;
    }

    public static function encode($value, $throw_exception = true) {
        $ret = false;

        $json = json_encode($value, JSON_UNESCAPED_UNICODE);
        if ( $json !== false && !is_null($json) ) {
            $ret = $json;
        } else {
            if ($throw_exception) {
                $error = "SOS\Json.encode() " . self::getError() . " for value=$value";
                throw new \Exception($error);
            }
        }
        return $ret;
    }

    private static function decodeString($string, $as_array = false, $throw_exception = false) {
        $func = "SOS\Json." . ($as_array ? "getArray" : "decode");
        $ret = json_decode($string, $as_array);
        $index = json_last_error();
        if( is_null($ret) && $index == 0 ) {
            $ret = false;
            $error = "$func: unknown error for string=$string";
            if ($throw_exception) {
                throw new \Exception($error);
            }
        }
        if ($index != 0) {
            $ret = false;
            $error = "$func: " . self::getError($index) . " for string=$string";
            if ($throw_exception) {
                throw new \Exception($error);
            }
        }
        return $ret;
    }
    
    public static function decode($string, $throw_exception = false) {
        return self::decodeString($string, false, $throw_exception);
    }
    
    public static function getArray($string, $throw_exception = false) {
        return self::decodeString($string, true, $throw_exception);
    }

}