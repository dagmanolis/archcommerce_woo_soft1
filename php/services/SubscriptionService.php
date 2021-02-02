<?php

namespace webxl\archcommerce\services;

class SubscriptionService
{
    private ArchCommerceRequestService $requestService;
    private DataOptionService $dataOptionService;
    public function __construct(
        ArchCommerceRequestService $requestService,
        DataOptionService $dataOptionService
    ) {
        $this->requestService = $requestService;
        $this->dataOptionService = $dataOptionService;
    }
    public function refresh()
    {
        $subscription = $this->get_subscription();
        if (isset($subscription->id)) {
            $date = \DateTime::createFromFormat("Y-m-d H:s:i", $subscription->expires_at);
            $date->setTimezone(wp_timezone());
            $this->dataOptionService->set_subscription_expiration_date($date);
            $this->dataOptionService->set_insert_orders_active($subscription->insert_orders_active);
            $this->dataOptionService->set_customization_active($subscription->customization_active);
        }
    }
    public function get_subscription_days_left()
    {
        $subscription_expiration_date = $this->dataOptionService->get_subscription_expiration_date();
        if ($subscription_expiration_date) {
            $now = \DateTime::createFromFormat("Y-m-d H:s:i", gmdate("Y-m-d H:s:i", time()));
            $now->setTimezone(wp_timezone());
            $date_diff = date_diff($now, $subscription_expiration_date);
            return intval($date_diff->format("%R%a days"));
        } else {
            return null;
        }
    }
    public function get_subscription_status()
    {
        $days_left = $this->get_subscription_days_left();
        if ($days_left) {
            if ($days_left > 0) {
                return 'active';
            } else {
                return 'expired';
            }
        } else {
            return null;
        }
    }
    public function is_insert_orders_active()
    {
        return $this->dataOptionService->is_insert_orders_active();
    }
    public function is_customization_active()
    {
        return $this->dataOptionService->is_customization_active();
    }
    private function get_subscription()
    {
        $response = $this->requestService->send_request('/api/v1.0/user/subscription');
        return json_decode(wp_remote_retrieve_body($response));
    }
}
