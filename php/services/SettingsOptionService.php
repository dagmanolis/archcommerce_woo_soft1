<?php

namespace webxl\archcommerce\services;

class SettingsOptionService
{
    private $settings;
    public function __construct()
    {
        $this->reload();
    }
    public function get_batch_size()
    {
        return $this->settings["batch_size"];
    }
    public function get_last_update_date(): \DateTime
    {
        return $this->settings["last_update_date"];
    }
    public function get_storing_batch_size()
    {
        return $this->settings["storing_batch_size"];
    }
    public function get_cronjob_starting_time()
    {
        $this->reload();
        return $this->settings["cronjob_starting_time"];
    }
    public function get_cronjob_custom_interval()
    {
        $this->reload();
        return $this->settings["cronjob_interval"];
    }
    public function has_sync_orders_enabled()
    {
        return isset($this->settings["sync_orders"]) ? $this->settings["sync_orders"] == "yes" : false;
    }
    public function get_email_and_password()
    {
        $this->reload();
        return [
            "email" => $this->settings["email"],
            "password" => $this->settings["password"]
        ];
    }

    public function update_last_update_date()
    {
        $now = \DateTime::createFromFormat("Y-m-d H:s:i", gmdate("Y-m-d H:s:i", time()));
        $now->setTimezone(wp_timezone());
        $this->settings["last_update_date"] =  $now;
        $this->update();
    }
    private function update()
    {
        update_option("archcommerce_settings", $this->settings);
        $this->reload();
    }
    public function reload()
    {
        $this->settings = get_option("archcommerce_settings");
    }
}
