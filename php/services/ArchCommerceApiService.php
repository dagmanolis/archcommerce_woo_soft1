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
    ) {
        $this->encryptService = $encryptService;
        $this->requestService = $requestService;
        $this->settingsOptionService = $settingsOptionService;
    }
    public function fetch_products(\DateTime $last_update_date)
    {
        $body = array(
            "last_update_date" => $last_update_date->format("Y-m-d H:i:s")
        );
        $url = "/api/v1.0/soft1/product";
        return $this->requestService->send_request($url, $body);
    }
    public function insert_order($order)
    {
        $url = '/api/v1.0/soft1/order';
        return $this->requestService->send_request($url, $order, 'POST');
    }
    public function check_credentials()
    {
        $email_and_password = $this->settingsOptionService->get_email_and_password();
        $body = array(
            "email" => $email_and_password["email"],
            "password" => $this->encryptService->decrypt($email_and_password["password"]),
        );
        $url = '/api/v1.0/check_credentials';
        return $this->requestService->send_request($url, $body, 'post', false);
    }
}
