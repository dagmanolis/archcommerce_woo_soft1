<?php

namespace webxl\archcommerce\services;

class AjaxFunctionsService
{
    private WpCronSchedulerService $wpCronSchedulerService;
    private SyncTablesService $syncTablesService;
    private SyncProcessOptionService $syncProcessOptionService;
    public function __construct(
        WpCronSchedulerService $wpCronSchedulerService,
        SyncTablesService $syncTablesService,
        SyncProcessOptionService $syncProcessOptionService
    ) {
        $this->wpCronSchedulerService = $wpCronSchedulerService;
        $this->syncTablesService = $syncTablesService;
        $this->syncProcessOptionService = $syncProcessOptionService;
    }
    public function get_active_sync_process()
    {
        try {
            $asp = $this->syncProcessOptionService->get_asp();
            wp_send_json_success($asp, 200);
        } catch (\Exception $ex) {
            error_log("Ajax get active sync process failed: " . $ex->getMessage());
            wp_send_json_error($ex->getMessage(), 500);
        }
    }
    public function init_sync_process()
    {
        try {
            $this->wpCronSchedulerService->fire_init_sync_process();
            wp_send_json_success(null, 200);
        } catch (\Exception $ex) {
            error_log("Ajax init sync process failed: " . $ex->getMessage());
            wp_send_json_error($ex->getMessage(), 500);
        }
    }
    public function cancel_sync_process()
    {
        $this->syncProcessOptionService->set_status_to_canceled();
        $this->syncTablesService->empty_table();
        $this->wpCronSchedulerService->unschedule_process_sync_process();
    }
}
