<?php

namespace webxl\archcommerce\services;

class PluginUpdaterService
{
    public function check_for_updates()
    {
        $updateChecker = \Puc_v4_Factory::buildUpdateChecker(
            'https://github.com/dagmanolis/archcommerce_woo_soft1/',
            ARCHCOMMERCE_PLUGIN_FULL_FILE,
            'archcommerce'
        );

        $updateChecker->setAuthentication(ARCHCOMMERCE_GITHUB_PERSONAL_ACCESS_TOKEN);
    }
}
