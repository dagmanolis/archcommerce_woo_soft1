<?php

namespace webxl\archcommerce\services;

class SettingsOptionService
{
    private $settings;
    public function __construct()
    {
        $this->reload();
    }
    public function has_sync_orders_realtime_enabled()
    {
        return isset($this->settings["sync_orders_realtime"]) ? $this->settings["sync_orders_realtime"] == "yes" : false;
    }
    public function get_email_and_password()
    {
        $this->reload();
        return [
            "email" => $this->settings["email"],
            "password" => $this->settings["password"]
        ];
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
