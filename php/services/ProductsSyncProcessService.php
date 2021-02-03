<?php

namespace webxl\archcommerce\services;

use webxl\archcommerce\services\contracts\IWooCommerceService;

class ProductsSyncProcessService
{
    private ArchCommerceApiService $archApiService;
    private ProductsSyncTablesService $tableService;
    private IWooCommerceService $wooCommerceService;
    private ProductsSyncProcessOptionService $productsSyncProcessOptionService;
    private SyncProductsSettingsOptionService $syncProductsSettingsOptionService;
    private WpCronSchedulerService $wpCronSchedulerService;
    private SubscriptionService $subscriptionService;
    public function __construct(
        ArchCommerceApiService $archApiService,
        ProductsSyncTablesService $tableService,
        IWooCommerceService $wooCommerceService,
        ProductsSyncProcessOptionService $productsSyncProcessOptionService,
        SyncProductsSettingsOptionService $syncProductsSettingsOptionService,
        WpCronSchedulerService $wpCronSchedulerService,
        SubscriptionService $subscriptionService
    ) {
        $this->archApiService = $archApiService;
        $this->tableService = $tableService;
        $this->wooCommerceService = $wooCommerceService;
        $this->productsSyncProcessOptionService = $productsSyncProcessOptionService;
        $this->syncProductsSettingsOptionService = $syncProductsSettingsOptionService;
        $this->wpCronSchedulerService = $wpCronSchedulerService;
        $this->subscriptionService = $subscriptionService;
    }
    public function init_sync_process()
    {
        try {
            if ($this->subscriptionService->get_subscription_status() !== "active")
                return;
            if ($this->productsSyncProcessOptionService->is_status_ready_for_init()) {
                $batch_size = $this->syncProductsSettingsOptionService->get_batch_size();
                $this->productsSyncProcessOptionService->init_sync_process($batch_size);
                if ($this->subscriptionService->is_customization_active())
                    $fetch_response = $this->archApiService->fetch_products($this->syncProductsSettingsOptionService->get_last_update_date());
                else
                    $fetch_response = $this->archApiService->fetch_products();
                $response_code = intval(wp_remote_retrieve_response_code($fetch_response));
                switch ($response_code) {
                    case 200:
                        $body = json_decode(wp_remote_retrieve_body($fetch_response));
                        if (isset($body->success) && $body->success == true) {
                            $this->productsSyncProcessOptionService->complete_init_process(count($body->products), $batch_size);
                            $this->tableService->store_products($body->products, $this->syncProductsSettingsOptionService->get_storing_batch_size());
                            $this->wpCronSchedulerService->schedule_process_sync_process();
                            $this->productsSyncProcessOptionService->set_status_to_idle();
                        } else if (isset($body->success) && $body->success == false) {
                            $this->productsSyncProcessOptionService->finish_init_process_with_error();
                        } else {
                            $this->productsSyncProcessOptionService->finish_init_process_with_error();
                            throw new \Exception("Couldn't fetch products from Soft1. Soft1 Response:" . print_r($fetch_response, true));
                        }
                        break;
                    case 204:
                        //no products to update
                        $this->productsSyncProcessOptionService->finish_init_process_with_no_products_to_update();
                        $this->syncProductsSettingsOptionService->update_last_update_date();
                        break;
                    default:
                        $this->productsSyncProcessOptionService->finish_init_process_with_error();
                        throw new \Exception("Couldn't fetch products from Soft1. Soft1 Response:" . print_r($fetch_response, true));
                        break;
                }
            }
        } catch (\Exception $ex) {
            $this->productsSyncProcessOptionService->set_status_to_failed();
            error_log("Init sync process failed: " . $ex->getMessage());
        }
    }

    public function process_sync_process()
    {
        try {
            if ($this->productsSyncProcessOptionService->is_status_idle()) {
                $offset = $this->productsSyncProcessOptionService->get_offset();
                $total_products = $this->productsSyncProcessOptionService->get_total_products();
                $batch_size = $this->productsSyncProcessOptionService->get_batch_size();
                if ($offset >= $total_products) {
                    //terminate asp
                    $this->productsSyncProcessOptionService->terminate_process();
                    $this->syncProductsSettingsOptionService->update_last_update_date();
                    $this->tableService->empty_table();
                    $this->wpCronSchedulerService->unschedule_process_sync_process();
                } else {
                    //process asp
                    $this->productsSyncProcessOptionService->set_status_to_processing();
                    $arch_products = $this->tableService->get_products($offset, $batch_size);
                    $updated = $this->wooCommerceService->update_products($arch_products);
                    if ($this->productsSyncProcessOptionService->is_status_canceled())
                        return;
                    $this->productsSyncProcessOptionService->update_processed_process($updated);
                }
            }
        } catch (\Exception $ex) {
            $this->productsSyncProcessOptionService->set_status_to_failed();
            error_log("Process sync process failed: " . $ex->getMessage());
        }
    }
}
