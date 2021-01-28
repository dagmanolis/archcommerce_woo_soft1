<?php

namespace webxl\archcommerce\services;

class UpdatesHistoryService
{
    private ArchCommerceRequestService $requestService;
    public function __construct(ArchCommerceRequestService $requestService)
    {
        $this->requestService = $requestService;
    }
    public function get_history()
    {
        $url = "/api/v1.0/user/updates_history";
        return $this->requestService->send_request($url);
    }
}
