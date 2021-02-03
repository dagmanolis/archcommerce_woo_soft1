<?php

namespace webxl\archcommerce\services;

class ProductsSyncTablesService
{
    private $table_name;
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'archcommerce_products_sync_process';
    }


    public function store_products($products, $batch_size)
    {
        $products_array = array();
        foreach ($products as $product) {
            $serialized_product = json_encode($product);
            $products_array[] = ($serialized_product);
        }

        $offset = 0;
        $total =  count($products_array);

        if ($total <= $batch_size) {
            $total_batches = 1;
        } else {
            $total_batches = $total % $batch_size == 0 ? $total / $batch_size :  floor($total / $batch_size) + 1;
        }

        for ($i = 0; $i < $total_batches; $i++) {
            $products_batch = array_slice($products_array, $offset, $batch_size);
            $this->insert_products_to_db($products_batch);
            $offset += $batch_size;
        }
    }
    public function get_products($offset, $batch_size)
    {
        global $wpdb;
        $query = "SELECT product_data FROM $this->table_name LIMIT %d OFFSET %d";
        return $wpdb->get_col($wpdb->prepare($query, intval($batch_size), intval($offset)));
    }
    public function create_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $query = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id int NOT NULL AUTO_INCREMENT,
            product_data text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($query);
    }
    public function empty_table()
    {
        global $wpdb;
        $query = "TRUNCATE TABLE $this->table_name";
        $wpdb->query($query);
    }
    public function delete_table()
    {
        global $wpdb;
        $query = "DROP TABLE IF EXISTS $this->table_name";
        $wpdb->query($query);
    }
    private function insert_products_to_db($products)
    {
        global $wpdb;
        $place_holders = array();
        for ($i = 0; $i < count($products); $i++) {
            $place_holders[] = "('%s')";
        }
        $query = "INSERT INTO $this->table_name (product_data) VALUES " . implode(', ', $place_holders);
        $result = $wpdb->query($wpdb->prepare("$query", $products));
    }
}
