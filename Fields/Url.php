<?php

/**
 * Url field
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Fields;

class Url extends Text
{
    protected $_type = 'url';

    /**
     * Filtering field value
     *
     * @param string $value
     * @return string
     */
    public function apply_filter($value)
    {
        return esc_url($value);
    }

}