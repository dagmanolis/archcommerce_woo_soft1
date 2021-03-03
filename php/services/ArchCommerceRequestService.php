<?php

namespace webxl\archcommerce\services;

use webxl\archcommerce\services\EncryptService;

class ArchCommerceRequestService
{
    private DataOptionService $dataOptionService;
    private SettingsOptionService $settingsOptionService;
    private EncryptService $encryptService;

    public function __construct(
        EncryptService $encryptService,
        DataOptionService $dataOptionService,
        SettingsOptionService $settingsOptionService
    ) {
        $this->encryptService = $encryptService;
        $this->dataOptionService = $dataOptionService;
        $this->settingsOptionService = $settingsOptionService;
    }
    public function send_request($url, $body = null, $method = 'GET', $authorize = true)
    {
        $headers = array();
        $headers["Accept"] = "application/json";
        if ($authorize) {
            $token = $this->get_token();
            $headers['Authorization'] = 'Bearer ' . $token;
        }
        switch (strtolower($method)) {
            case 'post':
                $wp_remote_options = array(
                    'headers' => $headers,
                    'body' => $body,
                );
                if (defined("WP_DEBUG") && WP_DEBUG == true) {
                    $_url = ARCHCOMMERCE_SERVICE_URL . $url .  '?XDEBUG_SESSION_START=1';
                    $wp_remote_options["sslverify"] = false;
                } else {
                    $_url = ARCHCOMMERCE_SERVICE_URL . $url;
                }
                $response = wp_remote_post(
                    $_url,
                    $wp_remote_options
                );
                break;
            case 'get':
                $wp_remote_options = array(
                    'headers' => $headers,
                    'body' => $body,
                );
                if (defined("WP_DEBUG") && WP_DEBUG == true) {
                    $body["XDEBUG_SESSION_START"] = 1;
                    $wp_remote_options["sslverify"] = false;
                }
                $query_params = http_build_query($body);
                $_url = empty($query_params) ?
                    ARCHCOMMERCE_SERVICE_URL . $url :
                    ARCHCOMMERCE_SERVICE_URL . $url . '?' . $query_params;

                $response = wp_remote_get(
                    $_url,
                    $wp_remote_options
                );
                break;
        }
        return $response;
    }

    private function get_token()
    {
        $token = $this->dataOptionService->get_token();
        if (empty($token)) {
            $response = $this->login();
            $body = json_decode(wp_remote_retrieve_body($response));
            if (isset($body->access_token)) {
                $token = $body->access_token;
                $this->dataOptionService->set_token($token);
            }
        }
        return $token;
    }

    private function login()
    {
        $email_and_password = $this->settingsOptionService->get_email_and_password();
        $body = array(
            "email" => $email_and_password["email"],
            "password" => $this->encryptService->decrypt($email_and_password["password"]),
        );
        $url = '/api/v1.0/login';
        return $this->send_request($url, $body, 'post', false);
    }
}
