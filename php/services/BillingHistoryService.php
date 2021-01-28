<?php

namespace webxl\archcommerce\services;

class BillingHistoryService
{
    private ArchCommerceRequestService $requestService;
    public function __construct(ArchCommerceRequestService $requestService)
    {
        $this->requestService = $requestService;
    }
    public function get_history()
    {
        $url = "/api/v1.0/user/billings";
        return $this->requestService->send_request($url);
    }
}
