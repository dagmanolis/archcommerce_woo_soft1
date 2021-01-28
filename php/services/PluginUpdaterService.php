<?php

namespace webxl\archcommerce\services;

class PluginUpdaterService
{
    public function check_for_updates()
    {
        $updateChecker = \Puc_v4_Factory::buildUpdateChecker(
            'https://bitbucket.org/webxl/archcommerce_woo_soft1',
            ARCHCOMMERCE_PLUGIN_FULL_FILE,
            'archcommerce'
        );

        $updateChecker->setAuthentication(array(
            'consumer_key' => ARCHCOMMERCE_BITBUCKET_OAUTH_PUBLIC_KEY,
            'consumer_secret' => ARCHCOMMERCE_BITBUCKET_OAUTH_SECRET_KEY,
        ));
    }
}
