<?php

namespace webxl\archcommerce\services;

class CurrentMonthStatusService
{
    private ArchCommerceRequestService $requestService;
    public function __construct(ArchCommerceRequestService $requestService)
    {
        $this->requestService = $requestService;
    }
    public function get_status()
    {
        $response = $this->requestService->send_request('/api/v1.0/user/status');
        return json_decode(wp_remote_retrieve_body($response));
    }
}
