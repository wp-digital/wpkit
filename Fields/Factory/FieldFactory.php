<?php

/**
 * Fields factory
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Fields\Factory;

use WPKit\Fields\AbstractField;
use WPKit\Exception\WpException;

class FieldFactory
{

    /**
     * Get field object by class name
     *
     * @param string $field_type field class name
     * @param array $options field options
     * @return AbstractField
     * @throws \WPKit\Exception\WpException
     */
    public static function build($field_type, array $options = [])
    {
        $field_class = "WPKit\\Fields\\$field_type";

        if(class_exists($field_class)) {
            /**
             * @var $object AbstractField
             */
            $object = new $field_class();
            if(method_exists($object, 'set_options')) {
                $object->set_options($options);
            }
            return $object;
        }
        else {
            throw new WpException("Class $field_class does not exist.");
        }
    }

}