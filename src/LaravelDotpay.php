<?php

namespace Alzo\LaravelDotpay;

use Alzo\LaravelDotpay\Exception\EmptyFieldException;
use URL;
use Log;

final class LaravelDotpay
{
    const PAYMENT_BUTTON_BACK_TYPE = 0;
    const PAYMENT_BACK_POST_REQUEST_TYPE = 1;
    const PAYMENT_REDIRECT_NONE_TYPE = 2;
    const PAYMENT_BUTTON_BACK_AND_POST_REQUEST_TYPE = 3;
    const PAYMENT_REDIRECT_TYPE = 4;

    /**
     * @var \Closure[]
     */
    private $successCallbacks;

    /**
     * @var \Closure[]
     */
    private $failedCallbacks;

    /**
     * @var array
     */
    private $config;

    /**
     * Allowed Dotpay IP servers list
     *
     * @var array
     */
    protected $allowed_servers;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var string
     */
    private $formUrl;

    /**
     * LaravelDotpay constructor.
     * @param $app
     */
    function __construct($app)
    {
        $this->config = $app['config']->get('dotpay');
        $this->allowed_servers = $this->config['allowed_servers'];

        $this->successCallbacks = array();
        $this->failedCallbacks = array();

        $this->formUrl = $this->config['environment'] == 'dev' ?
            "https://ssl.dotpay.pl/test_payment/" : "https://ssl.dotpay.pl/t2/";
    }

    public function enableDebug()
    {
        $this->debug = true;
    }

    public function disableDebug()
    {
        $this->debug = false;
    }

    /**
     * @param null $data
     * @return string
     *
     * @throws \Exception
     */
    public function createForm($data = null)
    {
        $successUrl      = URL::route($this->config['success_url']);
        $notificationURL = URL::route($this->config['notification_url']);

        $formStart = '<form class="dotpay-form" action="' . $this->formUrl . '" method="POST">';
        $inputTemplate = '<input type="hidden" name="[name]" value="[value]"/>';
        $formEnd = '</form>';

        $formData = [
            'id'                => $this->config['seller_id'],
            'description'       => isset($data['description']) ? $data['description'] : null,
            'channel'           => isset($data['channel']) ? $data['channel'] : null,
            'api_version'       => isset($data['api_version']) ? $data['api_version'] : "dev",
            'lang'              => isset($data['lang']) ? $data['lang'] : "pl",
            'control'           => isset($data['control']) ? $data['control'] : null,
            'amount'            => isset($data['amount']) ? $data['amount'] : null,
            'type'              => isset($data['type']) ? $data['type'] : self::PAYMENT_REDIRECT_TYPE,
            'firstname'         => isset($data['firstname']) ? $data['firstname'] : null,
            'lastname'          => isset($data['lastname']) ? $data['lastname'] : null,
            'email'             => isset($data['email']) ? $data['email'] : null,
            'p_email'           => $this->config['seller_email'],
            'p_info'            => $this->config['seller_info'],
            'URL'               => $successUrl,
            'URLC'              => $notificationURL,
        ];

        if ($formData['type'] == self::PAYMENT_REDIRECT_TYPE) {
            $formData['bylaw'] = 1;
            $formData['personal_data'] = 1;
        }

        $required = [
            'id',
            'description',
            'control',
            'amount',
            'firstname',
            'lastname',
            'email',
            'p_email',
            'p_info',
        ];

        foreach ($required as $item) {
            if (false == isset($formData[$item]) || strlen($formData[$item]) == 0) {
                throw new EmptyFieldException(
                    sprintf("Filed `%s` is required. Empty given", $item)
                );
            }
        }

        $form = $formStart . PHP_EOL;

        foreach ($formData as $key => $val) {
            $form .= str_replace(['[name]', '[value]'], [$key, $val], $inputTemplate) .PHP_EOL;
        }

        if (isset($data['button']) && $data['button']) {
            $form .= '<button class="dotpay-from-submit" type="submit">' . $data['button'] . '</button>';
        }

        $form .= $formEnd . PHP_EOL;

        return $form;
    }

    /**
     * @param $ip
     * @return bool
     */
    public function validateIP($ip)
    {
        return in_array($ip, $this->allowed_servers);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function validate(array $data)
    {
        $keys = [
            'id',
            'operation_number',
            'operation_type',
            'operation_status',
            'operation_amount',
            'operation_currency',
            'operation_withdrawal_amount',
            'operation_commission_amount',
            'operation_original_amount',
            'operation_original_currency',
            'operation_datetime',
            'operation_related_number',
            'control',
            'description',
            'email',
            'p_info',
            'p_email',
            'credit_card_issuer_identification_number',
            'credit_card_masked_number',
            'credit_card_brand_codename',
            'credit_card_brand_code',
            'credit_card_id',
            'channel',
            'channel_country',
            'geoip_country',
        ];

        $concatData = '';

        foreach ($keys as $key) {
            if (isset($data[$key]) && strlen($data[$key])) {
                $concatData .= $data[$key];
            }
        }

        $hash = hash('sha256', $this->config['PIN'] . $concatData);
        $signature = isset($data['signature']) ? $data['signature'] : null;

        $result = $signature && ($hash === $signature);

        if ($result) {
            $this->callSuccess($data);
        } else {
            $this->callFailed($data);
        }

        return $result;
    }

    /**
     * @param \Closure $callback
     */
    public function success(\Closure $callback)
    {
        $this->successCallbacks[] = $callback;
    }

    /**
     * @param \Closure $callback
     */
    public function failed(\Closure $callback)
    {
        $this->failedCallbacks[] = $callback;
    }

    /**
     * Dotpay URL request data
     * @param $data
     */
    private function callSuccess($data)
    {
        if (count($this->successCallbacks)) {
            foreach($this->successCallbacks as $callback) {
                if ($callback instanceof \Closure) {
                    $callback($data);
                }
            }
        }
    }

    /**
     * Dotpay URL request data
     * @param $data
     */
    private function callFailed($data)
    {
        if (count($this->failedCallbacks)) {
            foreach($this->failedCallbacks as $callback) {
                if ($callback instanceof \Closure) {
                    $callback($data);
                }
            }
        }
    }

    /**
     * Array of all allowed channels
     *
     * @return array
     */
    public function getChannels()
    {
        return include __DIR__ . "/lib/channels.php";
    }
}
