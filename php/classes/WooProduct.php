<?php

namespace webxl\archcommerce\classes;

class WooProduct
{
    private $woo_product;

    public function __construct($id)
    {
        $this->woo_product = wc_get_product($id);
    }
    public function set_regular_price($price)
    {
        if (!empty($price)) {
            $this->woo_product->set_regular_price($price);
            $this->woo_product->set_price($price);
        }
    }
    public function set_sale_price($price)
    {
        if (!empty($price)) {
            $this->woo_product->set_sale_price($price);
            $this->woo_product->set_price($price);
        }
    }
    public function set_tags($tags)
    {
        if (!empty($tags)) {
            $this->woo_product->set_tag_ids(explode(",", $tags));
        }
    }
    public function set_categories($categories)
    {
        if (!empty($categories)) {
            $this->woo_product->set_category_ids(explode(",", $categories));
        }
    }

    public function set_stock($stock)
    {
        if (!empty($stock) || is_numeric($stock)) {
            $this->woo_product->set_manage_stock(true);
            wc_update_product_stock($this->woo_product, floatval($stock), 'set', true);
        }
    }

    public function set_stock_status($stock_status)
    {
        if (!empty($stock_status)) {
            $this->woo_product->set_manage_stock(false);

            if (is_numeric($stock_status)) {
                if (floatval($stock_status) <= 0)
                    $this->woo_product->set_stock_status("outofstock");
                else if (floatval($stock_status) > 0)
                    $this->woo_product->set_stock_status("instock");
            } else {
                switch ($stock_status) {
                    case "no":
                    case "outofstock":
                    case "false":
                    case false:
                        $this->woo_product->set_stock_status("outofstock");
                        break;
                    case "yes":
                    case "instock":
                    case "true":
                    case true:
                        $this->woo_product->set_stock_status("instock");
                        break;
                }
            }
        }
    }

    public function set_catalog_visibility($visibility)
    {
        if (!empty($visibility)) {
            switch ($visibility) {
                case "no":
                case "0":
                case "hidden":
                case "false":
                case false:
                    $this->woo_product->set_catalog_visibility("hidden");
                    break;
                case "yes":
                case "1":
                case "visible":
                case "true":
                case true:
                    $this->woo_product->set_catalog_visibility("visible");
                    break;
            }
        }
    }
    public function set_is_featured($featured)
    {
        if (!empty($featured)) {
            switch ($featured) {
                case "no":
                case "0":
                case "false":
                case false:
                    $this->woo_product->set_featured(false);
                    break;
                case "yes":
                case "1":
                case "true":
                case true:
                    $this->woo_product->set_featured(true);
                    break;
            }
        }
    }
    public function save()
    {
        $this->woo_product->save();
    }
}
