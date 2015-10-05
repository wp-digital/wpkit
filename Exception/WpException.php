<?php

/**
 * WPKit Exception
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Exception;

class WpException extends \Exception
{
    public function __toString()
    {
        $message  = "<strong style='font-size: 150%'>Exception</strong><br/>";
        $message .= "<strong style='font-size: 120%'> {$this->message}</strong><br/>";
        $message .= "File: {$this->file}:{$this->line}<br/>";
        $message .= "Trace: <br/>";
        foreach($this->getTrace() as $step){
            $step = (object) $step;
            if(isset($step->file)){
                $message .= "&nbsp;&nbsp;File: {$step->file}:{$step->line}<br/>&nbsp;&nbsp;&nbsp;&nbsp;Function: {$step->function}()</br>";
            }
        }
        wp_die($message);
        return "";
    }
}