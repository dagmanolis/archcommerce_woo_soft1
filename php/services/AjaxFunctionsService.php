<?php

namespace webxl\archcommerce\services;

class AjaxFunctionsService
{
    private ProductsWpCronSchedulerService $productsWpCronSchedulerService;
    private ProductsSyncTablesService $productsSyncTablesService;
    private ProductsSyncProcessOptionService $productsSyncProcessOptionService;
    public function __construct(
        ProductsWpCronSchedulerService $productsWpCronSchedulerService,
        ProductsSyncTablesService $productsSyncTablesService,
        ProductsSyncProcessOptionService $productsSyncProcessOptionService
    ) {
        $this->productsWpCronSchedulerService = $productsWpCronSchedulerService;
        $this->productsSyncTablesService = $productsSyncTablesService;
        $this->productsSyncProcessOptionService = $productsSyncProcessOptionService;
    }
    public function get_active_products_sync_process()
    {
        try {
            $asp = $this->productsSyncProcessOptionService->get_asp();
            wp_send_json_success($asp, 200);
        } catch (\Exception $ex) {
            error_log("Ajax get active sync process failed: " . $ex->getMessage());
            wp_send_json_error($ex->getMessage(), 500);
        }
    }
    public function init_products_sync_process()
    {
        try {
            $this->productsWpCronSchedulerService->fire_init_sync_process();
            wp_send_json_success(null, 200);
        } catch (\Exception $ex) {
            error_log("Ajax init sync process failed: " . $ex->getMessage());
            wp_send_json_error($ex->getMessage(), 500);
        }
    }
    public function cancel_products_sync_process()
    {
        $this->productsSyncProcessOptionService->set_status_to_canceled();
        $this->productsSyncTablesService->empty_table();
        $this->productsWpCronSchedulerService->unschedule_process_sync_process();
    }
}
