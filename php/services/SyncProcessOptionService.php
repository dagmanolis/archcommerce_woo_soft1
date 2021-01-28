<?php

namespace webxl\archcommerce\services;

class SyncProcessOptionService
{
    private $asp;
    public function __construct()
    {
        $this->reload();
    }
    public function get_asp()
    {
        return $this->asp;
    }
    public function is_status_ready_for_init()
    {
        return ($this->asp && ($this->asp["status"] == "" || $this->asp["status"] === "finished" || $this->asp["status"] === "canceled" || $this->asp["status"] === "failed"));
    }
    public function init_sync_process($batch_size)
    {
        $process_id = uniqid();
        $now = \DateTime::createFromFormat("Y-m-d H:s:i", gmdate("Y-m-d H:s:i", time()));
        $now->setTimezone(wp_timezone());
        $this->asp["status"] = "init";
        $this->asp["finished_at"] = "";
        $this->asp["process_id"] = $process_id;
        $this->asp["created_at"] = $now;
        $this->asp["batch_size"] = $batch_size;
        $this->asp["offset"] = 0;
        $this->asp["products_updated"] = 0;
        $this->update();
    }

    public function complete_init_process($total_soft1_products, $batch_size)
    {
        if ($total_soft1_products <= $batch_size) {
            $total_batches = 1;
        } else {
            $total_batches = $total_soft1_products % $batch_size == 0 ? $total_soft1_products / $batch_size :  floor($total_soft1_products / $batch_size) + 1;
        }
        $this->asp["total_products"] = $total_soft1_products;
        $this->asp["total_batches"] = $total_batches;
        $this->asp["current_batch"] = 0;
        $this->update();
    }
    public function finish_init_process_with_error()
    {
        $now = \DateTime::createFromFormat("Y-m-d H:s:i", gmdate("Y-m-d H:s:i", time()));
        $now->setTimezone(wp_timezone());
        $this->asp["products_updated"] = 0;
        $this->asp["total_products"] = 0;
        $this->asp["status"] = "finished";
        $this->asp["finished_at"] = $now;
        $this->update();
    }
    public function finish_init_process_with_no_products_to_update()
    {
        $now = \DateTime::createFromFormat("Y-m-d H:s:i", gmdate("Y-m-d H:s:i", time()));
        $now->setTimezone(wp_timezone());
        $this->asp["products_updated"] = 0;
        $this->asp["total_products"] = 0;
        $this->asp["status"] = "finished";
        $this->asp["finished_at"] = $now;
        $this->update();
    }
    public function terminate_process()
    {
        $now = \DateTime::createFromFormat("Y-m-d H:s:i", gmdate("Y-m-d H:s:i", time()));
        $now->setTimezone(wp_timezone());
        $this->asp["status"] = "finished";
        $this->asp["finished_at"] = $now;
        $this->update();
    }
    public function update_processed_process($updated)
    {
        $this->asp["offset"] = $this->asp["offset"] + $this->asp["batch_size"];
        $this->asp["products_updated"] = $this->asp["products_updated"] + $updated;
        $this->asp["status"] = "idle";
        $this->asp["current_batch"] += 1;
        $this->update();
    }
    public function get_status()
    {
        return $this->asp["status"];
    }
    public function get_offset()
    {
        return $this->asp["offset"];
    }
    public function get_total_products()
    {
        return $this->asp["total_products"];
    }
    public function get_batch_size()
    {
        return $this->asp["batch_size"];
    }
    public function set_status_to_idle()
    {
        $this->set_status("idle");
    }
    public function set_status_to_failed()
    {
        $this->set_status("failed");
    }
    public function set_status_to_processing()
    {
        $this->set_status("processing");
    }
    public function set_status_to_canceled()
    {
        $this->set_status("canceled");
    }
    public function is_status_idle()
    {
        return ($this->asp && $this->asp["status"] == "idle");
    }
    public function is_status_canceled()
    {
        return $this->asp["status"] === "canceled";
    }
    private function set_status($status)
    {
        $this->asp["status"] = $status;
        $this->update();
    }
    private function update()
    {
        update_option("archcommerce_sync_process", $this->asp);
        $this->reload();
    }
    private function reload()
    {
        $this->asp = get_option("archcommerce_sync_process");
    }
}
