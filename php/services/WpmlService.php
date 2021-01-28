<?php

namespace webxl\archcommerce\services;


class WpmlService
{
    public function woocom_multilingual_exists_and_active()
    {
        return in_array('woocommerce-multilingual/wpml-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
    public function get_active_lang_codes()
    {
        $lang_codes = array();
        $langs = apply_filters('wpml_active_languages', null);
        foreach ($langs as $lang_code => $lang_object) {
            $lang_codes[] = $lang_code;
        }
        return $lang_codes;
    }
    public function get_wpml_object_id($object_id, $post_type, $lang_code)
    {
        return apply_filters('wpml_object_id',  $object_id,  $post_type, false, $lang_code);
    }
}
