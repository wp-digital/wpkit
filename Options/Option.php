<?php

/**
 * WordPress settings option
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Oleksandr Strikha <alex@pingbull.no>
 *
 */

namespace WPKit\Options;

use WPKit\Fields\AbstractField;
use WPKit\Exception\WpException;
use WPKit\Fields\Factory\FieldFactory;
use WPKit\Fields\Text;
use WPKit\Helpers\Action;
use WPKit\Helpers\GlobalStorage;

class Option
{
    protected $_key = null;
    protected $_title = null;
    /**
     * @var AbstractField
     */
    protected $_field = null;
    protected $_field_init = null;

    protected $_value = null;

    /**
     * Create new settings option
     *
     * @param string $key
     * @param string $title
     * @param string|AbstractField|callable $field
     * @throws WpException
     */
    public function __construct($key, $title, $field = null)
    {
        $this->_key = $this->_get_unique_key($key);
        $this->_title = $title;
        $this->_field_init = $field;
    }

    protected function _get_unique_key($key)
    {
        $key = "option_" . sanitize_key($key);
        $keys = (array) GlobalStorage::get('option', 'keys');
        if(in_array($key, $keys)) {
            throw new WpException("Option \"{$this->_title}\" has non unique key");
        }
        array_push($keys, $key);
        GlobalStorage::set('option', $keys, 'keys');
        return $key;
    }

    /**
     * Get option value
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return get_option("option_" . sanitize_key($key));
    }

    /**
     * Get option key
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

    /**
     * Get option title
     *
     * @return string
     */
    public function get_title()
    {
        return $this->_title;
    }

    /**
     * Get option field label
     *
     * @return string
     * @throws WpException
     */
    public function get_label()
    {
        if(in_array($this->get_field()->get_type(), ['radio', 'image'])) {
            return $this->_title;
        }
        $id = $this->get_field()->get_id();
        return "<label for='$id'>{$this->_title}</label>";
    }

    /**
     * Get option field
     *
     * @return AbstractField
     * @throws WpException
     */
    public function get_field()
    {
        if($this->_field == null) {
            if($this->_field_init == null){
                $this->_field = new Text();
            }
            elseif(is_string($this->_field_init)) {
                $this->_field = FieldFactory::build($this->_field_init);

            }
            elseif(Action::is_callable($this->_field_init)) {
                $this->_field = Action::execute($this->_field_init);
                if(! $this->_field instanceof AbstractField) {
                    throw new WpException("Option \"{$this->_title}\" init function must return a Field.");
                }
            }
            else {
                throw new WpException("Invalid field type.");
            }

            $this->_field->set_name($this->_key);
            $this->_field->set_label($this->_title);
        }
        return $this->_field;
    }

    /**
     * Get option field html
     *
     * @throws WpException
     */
    public function render()
    {
        $this->get_field()->set_value( get_option($this->_key) );
        print $this->get_field()->render_field();
    }

    /**
     * Filtering option field value
     *
     * @param mixed $input
     * @return string
     * @throws WpException
     */
    public function filter($input)
    {
        $this->get_field()->set_value($input);
        $input = $this->get_field()->get_value();
        return $input;
    }

}