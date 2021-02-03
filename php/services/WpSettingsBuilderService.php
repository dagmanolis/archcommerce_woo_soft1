<?php

namespace webxl\archcommerce\services;

use webxl\archcommerce\services\EncryptService;

class WpSettingsBuilderService
{
    private EncryptService $encryptService;
    public function __construct(
        EncryptService $encryptService
    ) {
        $this->encryptService = $encryptService;
    }
    /**********************
     * INTERFACE METHODS
     ***********************/
    public function create_options()
    {


        $defaults = array(
            'email'  => '',
            'password' => '',
            'updates_limit' => 0,
            'sync_orders_realtime' => 'yes',
        );
        add_option('archcommerce_settings', $defaults);

        $now = \DateTime::createFromFormat("Y-m-d H:s:i", gmdate("Y-m-d H:s:i", time()));
        $now->setTimezone(wp_timezone());
        $defaults = array(
            'updates_limit' => 0,
            'batch_size' => 100,
            'cronjob_interval' => '',
            'cronjob_starting_time' => '',
            'last_update_date' => $now,
            'storing_batch_size' => 100,
        );
        add_option('archcommerce_sync_products_settings', $defaults);

        $defaults = array(
            'cronjob_interval' => '',
            'cronjob_starting_time' => '',
        );
        add_option('archcommerce_sync_orders_settings', $defaults);

        $defaults = array(
            'token' => '',
            'insert_orders_active' => false,
            'customization_active' => false,
            'subscription_expiration_date' => ""
        );
        add_option('archcommerce_data', $defaults);

        $defaults = array(
            "process_id" => "",
            "created_at" => "",
            "finished_at" => "",
            "status" => "",
            "total_products" => 0,
            "products_updated" => 0,
            "batch_size" => 0,
            "offset" => 0,
            "current_batch" => 0,
            "total_batches" => 0,
        );
        add_option('archcommerce_products_sync_process', $defaults);

        $defaults = array(
            "process_id" => "",
            "created_at" => "",
            "finished_at" => "",
            "status" => "",
            "orders_inserted" => 0
        );
        add_option('archcommerce_orders_sync_process', $defaults);
    }

    public function options_exists()
    {
        return (get_option('archcommerce_settings') !== false
            && get_option('archcommerce_data') !== false
            && get_option('archcommerce_products_sync_process') !== false
            && get_option('archcommerce_sync_products_settings') !== false
            && get_option('archcommerce_sync_orders_settings') !== false
            && get_option('archcommerce_orders_sync_process') !== false);
    }

    public function register_settings()
    {
        register_setting(
            'archcommerce_settings_group',
            'archcommerce_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings')
            )
        );
        register_setting(
            'archcommerce_settings_group',
            'archcommerce_sync_products_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_sync_products_settings')
            )
        );
        register_setting(
            'archcommerce_settings_group',
            'archcommerce_sync_orders_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_sync_orders_settings')
            )
        );
    }

    public function create_fields()
    {
        //connection settings
        add_settings_section(
            'archcommerce_connection_settings_section',
            __('Connection Settings', 'archcommerce'),
            array($this, 'render_connection_settings_section'),
            'archcommerce_settings_page'
        );
        add_settings_field(
            'email',
            __('email', 'archcommerce'),
            array($this, 'render_email'),
            'archcommerce_settings_page',
            'archcommerce_connection_settings_section'
        );
        add_settings_field(
            'password',
            __('password', 'archcommerce'),
            array($this, 'render_password'),
            'archcommerce_settings_page',
            'archcommerce_connection_settings_section'
        );
        //general settings
        add_settings_section(
            'archcommerce_general_settings_section',
            __('General Settings', 'archcommerce'),
            array($this, 'render_general_settings_section'),
            'archcommerce_settings_page'
        );
        add_settings_field(
            'sync_orders_realtime',
            __('Sync orders in realtime', 'archcommerce'),
            array($this, 'render_sync_orders_realtime'),
            'archcommerce_settings_page',
            'archcommerce_general_settings_section'
        );
        //cronjob settings
        add_settings_section(
            'archcommerce_cronjob_settings_section',
            __('Cronjob Settings', 'archcommerce'),
            array($this, 'render_cronjob_settings_section'),
            'archcommerce_settings_page'
        );
        //--products
        add_settings_field(
            'sync_products_cronjob_interval',
            __('sync products interval', 'archcommerce'),
            array($this, 'render_sync_products_cronjob_interval'),
            'archcommerce_settings_page',
            'archcommerce_cronjob_settings_section'
        );
        add_settings_field(
            'sync_products_cronjob_starting_time',
            __('sync products starting time', 'archcommerce'),
            array($this, 'render_sync_products_cronjob_starting_time'),
            'archcommerce_settings_page',
            'archcommerce_cronjob_settings_section'
        );
        //--orders
        add_settings_field(
            'sync_orders_cronjob_interval',
            __('sync orders interval', 'archcommerce'),
            array($this, 'render_sync_orders_cronjob_interval'),
            'archcommerce_settings_page',
            'archcommerce_cronjob_settings_section'
        );
        add_settings_field(
            'sync_orders_cronjob_starting_time',
            __('sync orders starting time', 'archcommerce'),
            array($this, 'render_sync_orders_cronjob_starting_time'),
            'archcommerce_settings_page',
            'archcommerce_cronjob_settings_section'
        );
        //advanced settings
        add_settings_section(
            'archcommerce_advanced_settings_section',
            __('Advanced Settings', 'archcommerce'),
            array($this, 'render_advanced_settings_section'),
            'archcommerce_settings_page'
        );
        add_settings_field(
            'batch_size',
            __('sync products update batch size', 'archcommerce'),
            array($this, 'render_batch_size'),
            'archcommerce_settings_page',
            'archcommerce_advanced_settings_section'
        );
        add_settings_field(
            'storing_batch_size',
            __('sync products storing batch size', 'archcommerce'),
            array($this, 'render_storing_batch_size'),
            'archcommerce_settings_page',
            'archcommerce_advanced_settings_section'
        );
        add_settings_field(
            'last_update_date',
            __('sync products last update date', 'archcommerce'),
            array($this, 'render_last_update_date'),
            'archcommerce_settings_page',
            'archcommerce_advanced_settings_section'
        );
    }

    public function email_password_are_empty()
    {
        $options = get_option("archcommerce_settings");
        return empty($options["email"]) || empty($options["password"]);
    }
    /**********************
     * RENDER SECTIONS
     ***********************/
    public function render_connection_settings_section()
    {
        echo '<p>';
        echo __('Enter credentials to connect to ArchCommerce service', "archcommerce");
        echo  '</p>';
        echo '<small>';
        echo __('*These settings are mandatory', "archcommerce");
        echo '</small>';
    }
    public function render_general_settings_section()
    {
        echo '<p>';
        echo __('General settings', "archcommerce");
        echo  '</p>';
    }
    public function render_advanced_settings_section()
    {
        echo '<p>';
        echo __('Advanced settings for ArchCommerce', "archcommerce");
        echo  '</p>';
        echo '<small style="color:red;">';
        echo __("*Do not change these settings unless you know what you are doing", "archcommerce");
        echo '</small>';
    }
    public function render_cronjob_settings_section()
    {
        echo '<p>';
        echo __('Setup cronjob for starting sync process', "archcommerce");
        echo  '</p>';
        echo '<small>';
        echo __('*These settings are mandatory', "archcommerce");
        echo '</small>';
    }

    /**********************
     * RENDER FIELDS
     ***********************/
    public function render_email()
    {
        $options = get_option('archcommerce_settings');
        echo '<input type="text" name="archcommerce_settings[email]" value="';
        echo $options['email'];
        echo '">';
    }
    public function render_password()
    {
        $options = get_option('archcommerce_settings');
        echo '<input type="password" name="archcommerce_settings[password]" value="';
        echo $options['password'];
        echo '">';
    }
    public function render_sync_orders_realtime()
    {
        $options = get_option('archcommerce_settings');
        echo '<input type="checkbox" name="archcommerce_settings[sync_orders_realtime]" value="yes" ';
        echo checked($options['sync_orders_realtime'], 'yes');
        echo '">';
    }
    public function render_batch_size()
    {
        $options = get_option('archcommerce_sync_products_settings');
        echo '<input type="number" name="archcommerce_sync_products_settings[batch_size]" value="';
        echo $options['batch_size'];
        echo '">';
    }
    public function render_storing_batch_size()
    {
        $options = get_option('archcommerce_sync_products_settings');
        echo '<input type="number" name="archcommerce_sync_products_settings[storing_batch_size]" value="';
        echo $options['storing_batch_size'];
        echo '">';
    }
    public function render_last_update_date()
    {
        $options = get_option('archcommerce_sync_products_settings');
        $display = $options['last_update_date'];
        echo '<input type="text" name="archcommerce_sync_products_settings[last_update_date]" placeholder="';
        _e("Y-m-d H:i:s", "archcommerce");
        echo '" value="';
        echo $options['last_update_date']->format("Y-m-d H:i:s");
        echo '">';
    }
    public function render_sync_products_cronjob_interval()
    {
        $options = get_option('archcommerce_sync_products_settings');
        echo '<input type="number" name="archcommerce_sync_products_settings[cronjob_interval]" placeholder="';
        _e("enter time in seconds or daily/twicedaily", "archcommerce");
        echo '" value="';
        echo $options['cronjob_interval'];
        echo '">';
    }
    public function render_sync_products_cronjob_starting_time()
    {
        $options = get_option('archcommerce_sync_products_settings');
        echo '<input type="text" name="archcommerce_sync_products_settings[cronjob_starting_time]" placeholder="';
        _e("Y-m-d H:i:s", "archcommerce");
        echo '" value="';
        echo empty($options['cronjob_starting_time']) ? "" : $options['cronjob_starting_time']->format("Y-m-d H:i:s");
        echo '">';
    }
    public function render_sync_orders_cronjob_interval()
    {
        $options = get_option('archcommerce_sync_orders_settings');
        echo '<input type="number" name="archcommerce_sync_orders_settings[cronjob_interval]" placeholder="';
        _e("enter time in seconds or daily/twicedaily", "archcommerce");
        echo '" value="';
        echo $options['cronjob_interval'];
        echo '">';
    }
    public function render_sync_orders_cronjob_starting_time()
    {
        $options = get_option('archcommerce_sync_orders_settings');
        echo '<input type="text" name="archcommerce_sync_orders_settings[cronjob_starting_time]" placeholder="';
        _e("Y-m-d H:i:s", "archcommerce");
        echo '" value="';
        echo empty($options['cronjob_starting_time']) ? "" : $options['cronjob_starting_time']->format("Y-m-d H:i:s");
        echo '">';
    }


    /**********************
     * SANITIZE
     ***********************/
    public function sanitize_settings($new_option)
    {
        $old_option = get_option("archcommerce_settings");
        if (!isset($new_option['email']))  $new_option['email']  =  "";
        if (!isset($new_option['password'])) $new_option['password']  =  "";
        if (!isset($new_option['sync_orders_realtime']))  $new_option['sync_orders_realtime']  =  false;
        if (!isset($new_option['updates_limit']))  $new_option['updates_limit']  =  0;

        //password santization
        if (empty($new_option["password"]))
            $new_option["password"] = $old_option["password"];

        //password encryption
        if ($old_option["password"] !== $new_option["password"])
            $new_option['password']  = $this->encryptService->encrypt($new_option['password']);

        return ($new_option);
    }
    public function sanitize_sync_products_settings($new_option)
    {
        $old_option = get_option("archcommerce_sync_products_settings");

        if (!isset($new_option['batch_size']))  $new_option['batch_size']  =  100;
        if (!isset($new_option['storing_batch_size']))  $new_option['storing_batch_size']  =  100;
        if (!isset($new_option['cronjob_interval']))  $new_option['cronjob_interval']  =  "";
        if (!isset($new_option['cronjob_starting_time']))  $new_option['cronjob_starting_time']  =  "";

        //last update date sanitization
        if ($old_option["last_update_date"] !== $new_option["last_update_date"]) {
            $last_update_date = \DateTime::createFromFormat("Y-m-d H:i:s", $new_option["last_update_date"], wp_timezone());
            if ($last_update_date)
                $new_option["last_update_date"] = $last_update_date;
            else
                $new_option["last_update_date"] = $old_option["last_update_date"];
        }

        //cronjob starting time sanitization
        if ($old_option["cronjob_starting_time"] !== $new_option["cronjob_starting_time"]) {
            $cron_starting_date = \DateTime::createFromFormat("Y-m-d H:i:s", $new_option["cronjob_starting_time"], wp_timezone());
            if ($cron_starting_date)
                $new_option["cronjob_starting_time"] = $cron_starting_date;
            else
                $new_option["cronjob_starting_time"] = $old_option["cronjob_starting_time"];
        }

        return ($new_option);
    }
    public function sanitize_sync_orders_settings($new_option)
    {
        $old_option = get_option("archcommerce_sync_orders_settings");
        if (!isset($new_option['cronjob_interval']))  $new_option['cronjob_interval']  =  "";
        if (!isset($new_option['cronjob_starting_time']))  $new_option['cronjob_starting_time']  =  "";

        //cronjob starting time sanitization
        if ($old_option["cronjob_starting_time"] !== $new_option["cronjob_starting_time"]) {
            $cron_starting_date = \DateTime::createFromFormat("Y-m-d H:i:s", $new_option["cronjob_starting_time"], wp_timezone());
            if ($cron_starting_date)
                $new_option["cronjob_starting_time"] = $cron_starting_date;
            else
                $new_option["cronjob_starting_time"] = $old_option["cronjob_starting_time"];
        }

        return ($new_option);
    }
}
