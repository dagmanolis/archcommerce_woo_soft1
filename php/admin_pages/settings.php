<div class="wrap">
    <span class="dashicons dashicons-admin-settings"></span>
    <h1 class="wp-heading-inline"><?php _e("Settings", "archcommerce"); ?></h1>
    <hr class="wp-header-end" />
    <form method="post" action="options.php">
        <?php
        settings_fields('archcommerce_settings_group');
        do_settings_sections('archcommerce_settings');
        submit_button();
        ?>
    </form>
</div>