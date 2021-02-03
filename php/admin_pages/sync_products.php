<div class="wrap">
    <span class="dashicons dashicons-cart"></span>
    <h1 class="wp-heading-inline"><?php _e("Sync process monitoring", "archcommerce"); ?></h1>
    <hr class="wp-header-end" />
    <p id="loading">loading...</p>
    <p id="asp_error" class="asp_display"><?php _e("Something went wrong. See console for more info", "archcommerce"); ?></p>
    <p id="asp_notfound" class="asp_display"><?php _e("No sync process found", "archcommerce"); ?></p>
    <button id="init_sync_process" class="button-primary" disabled="true"><?php _e("init sync process", "archcommerce"); ?></button>
    <button id="cancel_sync_process" class="button-secondary" disabled="true"><?php _e("cancel sync process", "archcommerce"); ?></button>
    <p id="init_sync_porcess_error" style="display: none;"><?php _e("error initializing sync process", "archcommerce"); ?></p>

    <div id="asp_init" class="asp_display">
        <h2><?php _e("Initializing sync process", "archcommerce"); ?></h2>
        <p><strong><?php _e("process id:", "archcommerce"); ?></strong>&nbsp;<span class="asp_process_id"></span></p>
        <p><strong><?php _e("started at:", "archcommerce"); ?></strong>&nbsp;<span class="asp_started_at"></span></p>
        <p><strong><?php _e("batch size:", "archcommerce"); ?></strong>&nbsp;<span class="asp_batch_size"></span></p>
    </div>
    <div id="asp_running" class="asp_display">
        <h2><?php _e("Sync process is running", "archcommerce"); ?>&nbsp;&nbsp;&nbsp;<img src="<?php echo ARCHCOMMERCE_PLUGIN_URL ?>/images/loading.gif" width="32" /></h2>
        <p><strong><?php _e("process id:", "archcommerce"); ?></strong>&nbsp;<span class="asp_process_id"></span></p>
        <p><strong><?php _e("started at:", "archcommerce"); ?></strong>&nbsp;<span class="asp_started_at"></span></p>
        <p><strong><?php _e("status:", "archcommerce"); ?></strong>&nbsp;<span class="asp_status"></span></p>
        <p><strong><?php _e("soft1 updated products:", "archcommerce"); ?></strong>&nbsp;<span class="asp_total_products"></span></p>
        <p><strong><?php _e("batch size:", "archcommerce"); ?></strong>&nbsp;<span class="asp_batch_size"></span></p>
        <pre><strong><?php _e("woo products updated:", "archcommerce"); ?></strong>&nbsp;<span class="asp_products_updated"></span></pre>
        <pre><strong><?php _e("progress:", "archcommerce"); ?></strong>&nbsp;<span class="asp_percentage"></span></pre>
    </div>
    <div id="asp_finished" class="asp_display">
        <h2><?php _e("Last sync process", "archcommerce"); ?></h2>
        <p><strong><?php _e("process id:", "archcommerce"); ?></strong>&nbsp;<span class="asp_process_id"></span></p>
        <p><strong><?php _e("started at:", "archcommerce"); ?></strong>&nbsp;<span class="asp_started_at"></span></p>
        <p><strong><?php _e("finished at:", "archcommerce"); ?></strong>&nbsp;<span class="asp_finished_at"></span></p>
        <p><strong><?php _e("duration:", "archcommerce"); ?></strong>&nbsp;<span class="asp_duration"></span></p>
        <p><strong><?php _e("woo products updated:", "archcommerce"); ?></strong>&nbsp;<span class="asp_products_updated"></span></p>
        <p><strong><?php _e("soft1 updated products:", "archcommerce"); ?></strong>&nbsp;<span class="asp_total_products"></span></p>
    </div>
    <div id="asp_canceled" class="asp_display">
        <h2><?php _e("Last sync process was canceled", "archcommerce"); ?></h2>
    </div>
    <div id="asp_failed" class="asp_display">
        <h2><?php _e("Last sync process has failed", "archcommerce"); ?></h2>
        <p><?php _e("See webserver's error log for more info", "archcommerce"); ?></p>
    </div>
</div>