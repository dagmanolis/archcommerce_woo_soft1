<?php

namespace webxl\archcommerce\services;

use DateInterval;

class WpCronSchedulerService
{
    private SettingsOptionService $settingsOptionService;
    public function __construct(
        SettingsOptionService $settingsOptionService
    ) {
        $this->settingsOptionService = $settingsOptionService;
    }
    public function schedule_init_sync_process(\DateTime $starting_date = null)
    {
        if ($starting_date) {
            $_starting_date = $starting_date;
        } else {
            $_starting_date =  $this->settingsOptionService->get_cronjob_starting_time();
        }

        $now = \DateTime::createFromFormat("Y-m-d H:i:s", gmdate("Y-m-d H:i:s", time()));
        $now->setTimezone(wp_timezone());
        if ($now > $_starting_date) {
            $tomorrow = $now->add(new DateInterval("P1D"));
            $_starting_date = $tomorrow;
        }

        if ($_starting_date) {
            $this->unschedule_init_sync_process();
            wp_schedule_event($_starting_date->getTimestamp(), 'archcommerce_custom_interval', 'archcommerce_init_sync_process');
        }
    }
    public function unschedule_init_sync_process()
    {
        if (wp_next_scheduled("archcommerce_init_sync_process"))
            wp_clear_scheduled_hook("archcommerce_init_sync_process");
    }
    public function fire_init_sync_process()
    {
        wp_schedule_single_event(time(), 'archcommerce_init_sync_process');
    }
    public function schedule_process_sync_process()
    {
        $now = \DateTime::createFromFormat("Y-m-d H:s:i", gmdate("Y-m-d H:s:i", time()));
        $now->setTimezone(wp_timezone());
        if (!wp_next_scheduled("archcommerce_process_sync_process"))
            wp_schedule_event($now->getTimestamp(), 'every_minute', 'archcommerce_process_sync_process');
    }
    public function unschedule_process_sync_process()
    {
        if (wp_next_scheduled("archcommerce_process_sync_process"))
            wp_clear_scheduled_hook("archcommerce_process_sync_process");
    }
    public function register_custom_interval($schedules)
    {
        $cronjob_interval = $this->settingsOptionService->get_cronjob_custom_interval();
        if (isset($cronjob_interval) && !empty($cronjob_interval))
            $schedules["archcommerce_custom_interval"] = array(
                'interval' => (int)$cronjob_interval,
                'display'  => esc_html('ArchCommerce Custom Interval'),
            );
        return $schedules;
    }
    public function is_init_sync_process_unscheduled()
    {
        return !wp_next_scheduled("archcommerce_init_sync_process");
    }
}
