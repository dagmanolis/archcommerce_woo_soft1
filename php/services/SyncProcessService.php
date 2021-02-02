<?php

namespace webxl\archcommerce\services;

use webxl\archcommerce\services\contracts\IWooCommerceService;

class SyncProcessService
{
    private ArchCommerceApiService $archApiService;
    private SyncTablesService $tableService;
    private IWooCommerceService $wooCommerceService;
    private SyncProcessOptionService $syncProcessOptionService;
    private SettingsOptionService $settingsOptionService;
    private WpCronSchedulerService $wpCronSchedulerService;
    private SubscriptionService $subscriptionService;
    public function __construct(
        ArchCommerceApiService $archApiService,
        SyncTablesService $tableService,
        IWooCommerceService $wooCommerceService,
        SyncProcessOptionService $syncProcessOptionService,
        SettingsOptionService $settingsOptionService,
        WpCronSchedulerService $wpCronSchedulerService,
        SubscriptionService $subscriptionService
    ) {
        $this->archApiService = $archApiService;
        $this->tableService = $tableService;
        $this->wooCommerceService = $wooCommerceService;
        $this->syncProcessOptionService = $syncProcessOptionService;
        $this->settingsOptionService = $settingsOptionService;
        $this->wpCronSchedulerService = $wpCronSchedulerService;
        $this->subscriptionService = $subscriptionService;
    }
    public function init_sync_process()
    {
        try {
            if ($this->subscriptionService->get_subscription_status() !== "active")
                return;
            if ($this->syncProcessOptionService->is_status_ready_for_init()) {
                $batch_size = $this->settingsOptionService->get_batch_size();
                $this->syncProcessOptionService->init_sync_process($batch_size);
                if ($this->subscriptionService->is_customization_active())
                    $fetch_response = $this->archApiService->fetch_products($this->settingsOptionService->get_last_update_date());
                else
                    $fetch_response = $this->archApiService->fetch_products();
                $response_code = intval(wp_remote_retrieve_response_code($fetch_response));
                switch ($response_code) {
                    case 200:
                        $body = json_decode(wp_remote_retrieve_body($fetch_response));
                        if (isset($body->success) && $body->success == true) {
                            $this->tableService->store_products($body->products, $this->settingsOptionService->get_storing_batch_size());
                            $this->wpCronSchedulerService->schedule_process_sync_process();
                            $this->syncProcessOptionService->set_status_to_idle();
                        } else if (isset($body->success) && $body->success == false) {
                            $this->syncProcessOptionService->finish_init_process_with_error();
                        } else {
                            $this->syncProcessOptionService->finish_init_process_with_error();
                            throw new \Exception("Couldn't fetch products from Soft1. Soft1 Response:" . print_r($fetch_response, true));
                        }
                        break;
                    case 204:
                        //no products to update
                        $this->syncProcessOptionService->finish_init_process_with_no_products_to_update();
                        $this->settingsOptionService->update_last_update_date();
                        break;
                    default:
                        $this->syncProcessOptionService->finish_init_process_with_error();
                        throw new \Exception("Couldn't fetch products from Soft1. Soft1 Response:" . print_r($fetch_response, true));
                        break;
                }
            }
        } catch (\Exception $ex) {
            $this->syncProcessOptionService->set_status_to_failed();
            error_log("Init sync process failed: " . $ex->getMessage());
        }
    }

    public function process_sync_process()
    {
        try {
            if ($this->syncProcessOptionService->is_status_idle()) {
                $offset = $this->syncProcessOptionService->get_offset();
                $total_products = $this->syncProcessOptionService->get_total_products();
                $batch_size = $this->syncProcessOptionService->get_batch_size();
                if ($offset >= $total_products) {
                    //terminate asp
                    $this->syncProcessOptionService->terminate_process();
                    $this->settingsOptionService->update_last_update_date();
                    $this->tableService->empty_table();
                    $this->wpCronSchedulerService->unschedule_process_sync_process();
                } else {
                    //process asp
                    $this->syncProcessOptionService->set_status_to_processing();
                    $arch_products = $this->tableService->get_products($offset, $batch_size);
                    $updated = $this->wooCommerceService->update_products($arch_products);
                    if ($this->syncProcessOptionService->is_status_canceled())
                        return;
                    $this->syncProcessOptionService->update_processed_process($updated);
                }
            }
        } catch (\Exception $ex) {
            $this->syncProcessOptionService->set_status_to_failed();
            error_log("Process sync process failed: " . $ex->getMessage());
        }
    }
    private function merge_products_stock($products, $products_stock)
    {
    }
    private function get_products_stock()
    {
        $response = $this->archApiService->fetch_products_stock();
        $response_code = intval(wp_remote_retrieve_response_code($response));
        if ($response_code === 200) {
            $body = json_decode(wp_remote_retrieve_body($response));
            if (isset($body->success) && $body->success == true)
                return $body->products;
        }
        return null;
    }
}
