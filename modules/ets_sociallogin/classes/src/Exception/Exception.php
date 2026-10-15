<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Exception;

/**
 * ETSHybridauth Base Exception
 */

if (!defined('_PS_VERSION_')) { exit; }

class Exception extends \Exception implements ExceptionInterface
{
    /**
    * Shamelessly Borrowed from Slimframework
    *
    * @param $object
    */
    public function debug($object)
    {
        $title   = "ETSHybridauth Exception\r\n";
        $code    = $this->getCode();
        $message = $this->getMessage();
        $file    = $this->getFile();
        $line    = $this->getLine();
        $trace   = $this->getTraceAsString();

        $html  = $title."\r\n";
        $html .= "ETSHybridauth has encountered the following error:\n";
        $html .= "Details\n";
        $html .= "Exception:".get_class($this);
        $html .= "Message:".print_r($message);
        $html .= "File:".print_r($file);
        $html .= "File:".print_r($line);
        $html .= "File:".print_r($code);
        $html .= "Trace\r\n";
        $html .= print_r($trace);
        if ($object) {
            $html .= "Debug\r\n";
            $obj_dump = print_r($object, true);
            $html .= get_class($object)." extends ".get_parent_class($object).print_r($obj_dump);
        }
        $html .= "Session\r\n";
	    $cookie_dump = print_r($_COOKIE, true);
	    $html .= $cookie_dump;

        echo $title.$html;
    }
}
