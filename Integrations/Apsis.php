<?php

/**
 * Apsis subscription API
 *
 * @package WPKit
 *
 * @link https://github.com/REDINKno/wpkit for the canonical source repository
 * @copyright Copyright (c) 2015, Redink AS
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author Dmitry Korelsky <dima@pingbull.no>
 *
 */

namespace WPKit\Integrations;

class Apsis
{
    /**
     * Apsis subscribe URL
     * @var string
     */
    private $_url = 'http://www.anpdm.com/public/process-subscription-form.aspx';

    /**
     * Query string
     * @var string
     */
    private $_queryString = 'Submit=Send&pf_CharSet=utf-8';

    /**
     * Apsis Form ID
     * @var string
     */
    private $_formId = null;

    /**
     * Apsis Account ID
     * @var string
     */
    private $_accountId = null;

    /**
     * Apsis Mailing list ids
     * @var array
     */
    private $_mailingListIds = [];

    /**
     * Apsis Delivery Format, default 'HTML'
     * @var string
     */
    private $_deliveryFormat = 'HTML';

    /**
     * Apsis OptIn Method, default 'SingleOptInMethod'
     * @var string
     */
    private $_optInMethod = 'SingleOptInMethod';

    /**
     * Apsis default param
     * @var string
     */
    private $_formType = 'OptInForm';

    /**
     * Apsis default param
     * @var string
     */
    private $_listById = '1';

    /**
     * Apsis default param
     * @var string
     */
    private $_version = '2';

    /**
     * Defaults
     * @var array
     */
    private $_params = [
        'formId' => null,
        'pf_MailinglistName1' => null,
        'pf_DeliveryFormat' => null,
        'pf_FormType' => null,
        'pf_OptInMethod' => null,
        'pf_CounterDemogrFields' => null,
        'pf_CounterMailinglists' => null,
        'pf_AccountId' => null,
        'pf_ListById' => null,
        'pf_Version' => null
    ];

    /**
     * Form data
     *
     * @var array
     */
    private $_formData = [];

    function __construct($formId = null, $accountId = null, $mailingListsIds = [], $formData = [])
    {
        $this->set_form_id($formId);
        $this->set_account_id($accountId);
        $this->set_mailing_list_ids($mailingListsIds);
        $this->set_form_data($formData);
    }

	/**
	 * Send all data to APSIS
	 *
	 * @return bool|\WP_Error
	 */
    public function subscribe()
    {
        $result = $this->_validate_vars();
        if (!is_wp_error($result)) {
            $this->_build_param_array();
            $this->_send_request();
        }

        return $result;
    }

    /**
     * Set from id
     *
     * @param string $formId
     */
    public function set_form_id($formId)
    {
        $this->_formId = $formId;
    }

    /**
     * Set account id
     *
     * @param string $accountId
     */
    public function set_account_id($accountId)
    {
        $this->_accountId = $accountId;
    }

    /**
     * Set mailing lists id
     *
     * @param array $mailingListIds
     */
    public function set_mailing_list_ids($mailingListIds)
    {
        if (is_array($mailingListIds)) {
            $this->_mailingListIds = $mailingListIds;
        } else {
            $this->_mailingListIds = [$mailingListIds];
        }
    }

    /**
     * Add mailing list id
     *
     * @param string /array $mailingListId
     */
    public function add_mailing_list_id($mailingListId)
    {
        if (is_array($mailingListId)) {
            array_push($this->_mailingListIds, $mailingListId);
        } else {
            $this->_mailingListIds[] = $mailingListId;
        }
    }

    /**
     * Set delivery format
     *
     * @param string $deliveryFormat
     */
    public function set_delivery_format($deliveryFormat)
    {
        $this->_deliveryFormat = $deliveryFormat;
    }

    /**
     * Set optIn method
     *
     * @param string $optInMethod
     */
    public function set_optIn_method($optInMethod)
    {
        $this->_optInMethod = $optInMethod;
    }

    /**
     * Set form data
     *
     * @param array $formData
     */
    public function set_form_data($formData)
    {
        $this->_formData = $formData;
    }

    /**
     * Update form data
     *
     * @param array $formData
     */
    public function update_form_data($formData)
    {
	    $this->_formData = array_merge($this->_formData, $formData);
    }

    private function _build_param_array()
    {
        $params = [
            'formId' => $this->_formId,
            'pf_DeliveryFormat' => $this->_deliveryFormat,
            'pf_FormType' => $this->_formType,
            'pf_OptInMethod' => $this->_optInMethod,
            'pf_CounterMailinglists' => count($this->_mailingListIds),
            'pf_AccountId' => $this->_accountId,
            'pf_ListById' => $this->_listById,
            'pf_Version' => $this->_version
        ];
        $this->_params = array_merge($this->_params, $params, $this->_formData);
        $this->_set_mailing_lists();
        $this->_update_counter_demogr_fields();

        foreach($this->_params as $key=>$value) {
            $this->_queryString .= "&" . $key . "=" . $value;
        }
    }

    private function _set_mailing_lists()
    {
	    $key = 1;
        foreach ($this->_mailingListIds as $id) {
            $this->_params["pf_MailinglistName{$key}"] = $id;
	        $key++;
        }
    }

    private function _update_counter_demogr_fields()
    {
        $counter = 0;
        foreach ($this->_formData as $key => $val) {
            if (false !== strpos($key, 'pf_Demographicfield')) {
                $counter++;
            }
        }
        $this->_params["pf_CounterDemogrFields"] = $counter;
    }

    private function _send_request()
    {
        //todo: use wp_remote_post();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_queryString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        curl_close($ch);
    }

    private function _validate_vars()
    {
        $valid = true;
        $errors = new \WP_Error();
        if ( null == $this->_formId || empty($this->_formId) ) {
            $errors->add('0', 'You should set Apsis Form ID');
            $valid = $errors;
        }
        if ( null == $this->_accountId || empty($this->_accountId) ) {
            $errors->add('1', 'You should set Apsis Account ID');
            $valid = $errors;
        }
        if ( null == $this->_mailingListIds || 0 == count($this->_mailingListIds) ) {
            $errors->add('2', 'You should set Apsis Mailing List ID');
            $valid = $errors;
        }

        return $valid;
    }

}