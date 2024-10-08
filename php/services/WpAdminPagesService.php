<?php

namespace webxl\archcommerce\services;

class WpAdminPagesService
{

    public function create_admin_menu()
    {
        $page_title = __("ArchCommerce Status", "archcommerce");
        $menu_title = __("ArchCommerce", "archcommerce");
        $menu_slug =  "archcommerce_admin_main_page";
        $capability =  'manage_options';
        $icon_url = ARCHCOMMERCE_PLUGIN_URL . "/images/icon.png";
        $page_render_function = function () {
            $this->render_page("status");
        };
        $position = 66;
        add_menu_page($page_title,  $menu_title, $capability,  $menu_slug, $page_render_function, $icon_url, $position);

        $parent_slug = $menu_slug;
        $page_title = __("ArchCommerce Status", "archcommerce");
        $menu_title = __("Status", "archcommerce");
        $menu_slug =  "archcommerce_admin_status_subpage";
        $capability =  'manage_options';
        $page_render_function = function () {
            $this->render_page("status");
        };
        $position = 5;
        add_submenu_page($parent_slug,  $page_title,  $menu_title,  $capability,  $menu_slug, $page_render_function, $position);

        $page_title = __("ArchCommerce Products Sync", "archcommerce");
        $menu_title = __("Sync products", "archcommerce");
        $menu_slug =  "archcommerce_admin_sync_products_subpage";
        $capability =  'manage_options';
        $page_render_function = function () {
            $this->render_page("sync_products");
        };
        $position = 10;
        add_submenu_page($parent_slug,  $page_title,  $menu_title,  $capability,  $menu_slug, $page_render_function, $position);

        $page_title = __("ArchCommerce Orders Sync", "archcommerce");
        $menu_title = __("Sync orders", "archcommerce");
        $menu_slug =  "archcommerce_admin_sync_orders_subpage";
        $capability =  'manage_options';
        $page_render_function = function () {
            $this->render_page("sync_orders");
        };
        $position = 10;
        add_submenu_page($parent_slug,  $page_title,  $menu_title,  $capability,  $menu_slug, $page_render_function, $position);

        $page_title = __("ArchCommerce Settings", "archcommerce");
        $menu_title = __("Settings", "archcommerce");
        $menu_slug =  "archcommerce_admin_settings_subpage";
        $capability =  'manage_options';
        $page_render_function = function () {
            $this->render_page("settings");
        };
        $position = 20;
        add_submenu_page($parent_slug,  $page_title,  $menu_title,  $capability,  $menu_slug, $page_render_function, $position);

        remove_submenu_page('archcommerce_admin_main_page', 'archcommerce_admin_main_page');
    }

    public function render_page($page)
    {
        ob_start();
        require_once(ARCHCOMMERCE_PLUGIN_DIR . "php/admin_pages/$page.php");
        echo ob_get_clean();
    }
}
