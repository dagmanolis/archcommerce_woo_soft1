<?php

namespace webxl\archcommerce\services;

class ArchCommerceApiService
{
    private ArchCommerceRequestService $requestService;
    private SettingsOptionService $settingsOptionService;
    private EncryptService $encryptService;
    public function __construct(
        EncryptService $encryptService,
        ArchCommerceRequestService $requestService,
        SettingsOptionService $settingsOptionService
    )
    {
        $this->encryptService = $encryptService;
        $this->requestService = $requestService;
        $this->settingsOptionService = $settingsOptionService;
    }
    public function fetch_udpated_products_form_date(\DateTime $last_update_date)
    {
        $url = "/api/v" . ARCHCOMMERCE_API_VERSION . "/soft1/products/date/" . $last_update_date->format("Y-m-d-H-i-s");
        return $this->requestService->send_request($url);
    }
    public function insert_order($order)
    {
        $url = '/api/v' . ARCHCOMMERCE_API_VERSION . '/soft1/order';
        return $this->requestService->send_request($url, $order, 'POST');
    }
    public function check_credentials()
    {
        $email_and_password = $this->settingsOptionService->get_email_and_password();
        $body = array(
            "email" => $email_and_password["email"],
            "password" => $this->encryptService->decrypt($email_and_password["password"]),
        );
        $url = '/api/v' . ARCHCOMMERCE_API_VERSION . '/check_credentials';
        return $this->requestService->send_request($url, $body, 'post', false);
    }
}
