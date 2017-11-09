<?php

/**
 *  SMS maiup client to send sms 
 * 
 *  The contents of this file are subject to the terms of the GNU General
 *  Public License Version 3.0. You may not use this file except in
 *  compliance with the license. Any of the license terms and conditions
 *  can be waived if you get permission from the copyright holder.
 * 
 * By
 *       _               _  _                                 _            _    _    _ 
 *  ___ | |_  _   _   __| |(_)  ___   ___   __ _   ___   ___ | |__    ___ | |_ | |_ (_)
 * / __|| __|| | | | / _` || | / _ \ / __| / _` | / __| / __|| '_ \  / _ \| __|| __|| |
 * \__ \| |_ | |_| || (_| || || (_) |\__ \| (_| || (__ | (__ | | | ||  __/| |_ | |_ | |
 * |___/ \__| \__,_| \__,_||_| \___/ |___/ \__,_| \___| \___||_| |_| \___| \__| \__||_|
 *                                                                                     
 *
 * 
 * @project locker
 * @filename sms.php
 * @author studiosacchetti <studiosacchetti@gmail.com>
 * @date 04/11/2017
 * @time 9.48.05
 * @version 1.0
 * 
 * 
 * 
 */
//! Mailup sms plugin
class SMS {

    protected
            $smsEndpoint, //
            $listSecret,
            $listId,
            $listGuid,
            $campaignCode;

    /** @var \Base */
    protected
            $f3,
            $web;

    const
            E_APIERROR = 'Mailup API Error: %s',
            E_AUTHERROR = 'Auth failed: %s',
            E_METHODNOTSUPPORTED = 'METHOD %s not supported';

    public function __construct($inListId, $inListGuid, $inCampaignCode) {

        $this->smsEndpoint = 'https://sendsms.mailup.com/api/v2.0';

        $this->listId = $inListId;
        $this->listGuid = $inListGuid;
        $this->campaignCode = $inCampaignCode;

        $this->f3 = \Base::instance();
        $this->loadListSecret();
    }

    public function getListSecret() {
        return $this->listSecret;
    }

    public function setListSecret($inListSecret) {
        $this->listSecret = $inListSecret;
    }

    public function logOnWithPassword($username, $password) {
        $this->accountId = substr($username, 1);
        return $this->retreiveListSecret($username, $password);
    }

    public function logOnWithToken($username, $listsecret) {
        $this->accountId = substr($username, 1);
        $this->listSecret = $listsecret;
    }

    public function retreiveListSecret($user, $pass) {
        // @todo: GET, then POST if non-exist.
        $web = \Web::instance();
        $web->engine('curl');
        $result = $web->request($this->smsEndpoint . '/lists/' . $this->listId . '/listsecret', array(
            //'method' => 'POST',
            //'content' => http_build_query($params),
            'header' => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($user . ':' . $pass)
            )
        ));
        $result = json_decode($result['body']);

        if ($result->Code == 401) {
            trigger_error(sprintf(self::E_AUTHERROR, $result->Code));
        } elseif ($result->Code != 200 && $result->Code != 302) {
            trigger_error(sprintf(self::E_AUTHERROR, $result->Code) . " " . $result->Description);
        }



        $this->listSecret = $result->Data->ListSecret;
        $this->saveListSecret();
        return $this->listSecret;
    }

    /**
     * 
     */
    public function loadListSecret() {
        if ($this->f3->exists('SESSION.sms.listsecret')) {
            $this->listSecret = $this->f3->get('SESSION.sms.listsecret');
        }
    }

    /**
     * 
     */
    public function saveListSecret() {
        $this->f3->set('SESSION.sms.listsecret', $this->listSecret);
    }

    /**
     * Documentation for Transactional API located at
     * http://help.mailup.com/display/mailupapi/Transactional+SMS+using+APIs       
     * 
     * Account ID 
     * MailUp account ID (e.g. if the main user is "m59484", then the account ID  is 59848).  Integer
     * 
     * List ID
     * List identifier for the selected list within that account. Integer
     * 
     * List GUID 
     * Hash code that identifies the same list. This parameter is unique to a list 
     * within the entire MailUp system, unlike the List ID, which is unique only within a 
     * given MailUp account.      String
     * 
     * ListSecret 
     * The parameter described above and used to enable or disable the use of this API 
     * (it’s an auto-generated GUID).     String
     * 
     * CampaignCode 
     * Used to aggregate statistics. If the value specified does not exist, a new record 
     * is created in the “SMS” table, otherwise the existing one is reused.       String
     * 
     * Content 
     * The text of the message. No parsing nor length check is required.
     * SIZE LIMIT: messages longer than 459 characters are truncated by sending engine. String
     * 
     * Recipient
     * The recipient’s number. Include the international prefix (either with 00 or +), 
     * or the default list prefix will be applied. (Ask for MailUp standard regex for
     *  phone numbers). Only a single phone number is allowed String 
     * DynamicFields An array of value pairs that allows you to use merge tags in your
     * transactional text messages.	String
     * 
     * IsUnicode  (optional) Boolean value indicating whether to use Western alphabets
     *  only (0 means "false"), or Eastern alphabets as well, such as Arabic, Russian, 
     * Chinese, and so on (1 means "true"). 
     * It is up to you to decide if the message contains Unicode characters. Boolean
     * 
     * Sender	(optional) 
     * Message sender. It can be numeric or alphanumeric but some restrictions may apply.
     *  As a consequence, when the sender value is not allowed, message may be blocked 
     * or sender may be replaced. We recommend you to specify this value only when you 
     * are confident about the delivery result (e.g. you've already tested it using 
     * the MailUp web platform), otherwise it is better to not specify this parameter 
     * in the request
     * 
     * @param number $recipient
     * @param string $message
     * @param string $sender
     * @return string
     */
    public function sendSms($recipient, $message, $sender = '') {
        $web = \Web::instance();
        $web->engine('curl');

        $url = $this->smsEndpoint . '/sms/' . $this->accountId . '/' . $this->listId;
        var_dump($url);
        $settings = [
            'ListGuid' => $this->listGuid,
            'ListSecret' => $this->listSecret,
            'isUnicode' => 0,
            'Recipient' => $recipient,
            'Content' => $message,
            'CampaignCode' => $this->campaignCode,
            'Sender' => $sender,
            'DynamicFields' => NULL,
        ];

        $result = $web->request($url, array(
            'method' => 'POST',
            'content' => http_build_query($settings),
            'header' => array(
                'application/json;odata=verbose;charset=utf-8',
                'Accept: application/json;odata=verbose;charset=utf-8'
            ),
        ));

        $result = json_decode($result['body']);


        if ($result->Code == 401) {
            trigger_error(sprintf(self::E_AUTHERROR, $result->Code));
        } else if ($result->Code != 200 && $result->Code != 302) {
            trigger_error(sprintf(self::E_APIERROR, $result->Code) . " " . $result->Description);
        }



        return $result->State;
    }

}
