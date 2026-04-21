<?php

namespace webxl\archcommerce\lrvl\S1Service;

function GK_REQUEST()
{
}
function SendRequest(string $subdomain, array $body, string $apiMethod = null)
{
    $client = new Client();
    $response =  $client->request(
        'GET',
        sprintf("https://%s.oncloud.gr/s1services/%s", $subdomain, $apiMethod),
        [
            "headers" => [
                "Accept" => "*/*",
                "Content-Type" => "application/json",
                "Accept-Encoding" => "gzip, deflate, br",
            ],
            "body" => json_encode($body)
        ]
    );

    if ($response->getStatusCode() != 200)
        throw new \Exception(
            sprintf(
                "Soft1 Cloud response error.\r\nStatus Code: %s\r\nBody: %s",
                $response->getStatusCode(),
                $response->getBody()
            )
        );

    $responseBody = (string)$response->getBody()->getContents();

    //need to change encoding in order to properly decode soft1 request greek characters
    $current_encoding = mb_internal_encoding();
    mb_internal_encoding("ISO-8859-7");
    $convertedBody = mb_convert_encoding($responseBody, "utf-8");
    mb_internal_encoding($current_encoding);

    return json_decode($convertedBody);
}

function GK_S1_WEBSERVICE()
{
}


function Login($username, $password, $appId, $subdomain)
{
    $body = array(
        "service" => "login",
        "username" => $username,
        "password" => Crypt::decryptString($password),
        "appId" => $appId
    );

    return  $this->requestService->SendRequest($subdomain, $body);
}
function Authenticate(Soft1LoginResult $loginResult, $subdomain)
{
    $body = array(
        "service" => "authenticate",
        "clientID" => $loginResult->clientId,
        "COMPANY" => $loginResult->company,
        "BRANCH" => $loginResult->branch,
        "MODULE" => $loginResult->module,
        "REFID" => $loginResult->refid
    );
    return  $this->requestService->SendRequest($subdomain, $body);
}

function GetBrowser($subdomain, $clientId, $appId, $list, $filters, $offset = 0, $limit = null, $object = 'ITEM')
{
    $browserInfoResponse = $this->GetBrowserInfo(
        $subdomain,
        $clientId,
        $appId,
        $list,
        $filters,
        $object
    );

    if (!$browserInfoResponse->success)
    {
        throw new \Exception(
            sprintf(
                "Couldn't aquire reqID from getBrowserInfo request.\r\n%s",
                json_encode($browserInfoResponse, JSON_UNESCAPED_UNICODE)
            )
        );
    }

    if ($browserInfoResponse->totalcount <= 0)
        return array();

    $browserDataResponse = $this->GetBrowserData(
        $subdomain,
        $clientId,
        $appId,
        $browserInfoResponse->reqID,
        $offset,
        $limit
    );

    if (!$browserDataResponse->success)
    {
        throw new \Exception(
            sprintf(
                "Couldn't aquire data from getBrowserData request.\r\n%s",
                json_encode($browserInfoResponse, JSON_UNESCAPED_UNICODE)
            )
        );
    }

    return $this->AddColumnHeaders($browserInfoResponse, $browserDataResponse);
}

function GetBrowserInfo($subdomain, $clientId, $appId, $list, $filters, $object = 'ITEM')
{
    $body = array(
        "service" => "getBrowserInfo",
        "clientID" => $clientId,
        "appId" => $appId,
        "OBJECT" => $object,
        "LIST" => $list,
        "FILTERS" => $filters
    );

    return  $this->requestService->SendRequest($subdomain, $body);
}
function GetBrowserData($subdomain, $clientId, $appId, $reqId, $offset = 0, $limit = 100)
{
    $body = array(
        "service" => "getBrowserData",
        "clientID" => $clientId,
        "appId" => $appId,
        "reqID" => $reqId,
        "START" => $offset,
        "LIMIT" => $limit
    );

    return  $this->requestService->SendRequest($subdomain, $body);
}
function AddColumnHeaders($bowserInfo, $browserData)
{
    $products = array();
    foreach ($browserData->rows as $row)
    {
        $product = array();
        $product["id"] = $row[0];
        for ($i = 1; $i < count($row); $i++)
        {
            $product[$bowserInfo->columns[$i - 1]->header] = $row[$i];
        }
        $products[] = $product;
    }
    return $products;
}
function GetData($subdomain, $clientId, $appId, $object, $key, $locateInfo = null)
{
    $body = array(
        'service' => 'getData',
        'clientID' =>  $clientId,
        'appId' => $appId,
        'OBJECT' => $object,
        'KEY' =>  $key
    );

    if ($locateInfo)
        $body["LOCATEINFO"] = $locateInfo;

    return  $this->requestService->SendRequest($subdomain, $body);
}
function InsertData($subdomain, $clientId, $appId, $object, $data)
{
    return $this->SetData($subdomain, $clientId, $appId, $object, $data);
}
function UpdateData($subdomain, $clientId, $appId, $object, $data, $key)
{
    return $this->SetData($subdomain, $clientId, $appId, $object, $data, $key);
}
function SetData($subdomain, $clientId, $appId, $object, $data, $key = null)
{
    $body = array(
        'service' => 'setData',
        'clientID' =>  $clientId,
        'appId' => $appId,
        'OBJECT' => $object,
        'data' => $data
    );

    if ($key)
        $body["KEY"] = $key;

    return  $this->requestService->SendRequest($subdomain, $body);
}
function CallCustomMethod($subdomain, $method, $body)
{
    return  $this->requestService->SendRequest($subdomain, $body, $method);
}

//get client id

function GK_GET_CLIENT_ID()
{
}

function GetClientId($username, $password, $appId, $subdomain)
{
    $loginResponse = $this->soft1ApiService->Login($username, $password, $appId, $subdomain);
    if (!$loginResponse->success)
        throw new \Exception($loginResponse->error);

    $loginResult = new Soft1LoginResult();
    $loginResult->company = $loginResponse->objs[0]->COMPANY;
    $loginResult->branch = $loginResponse->objs[0]->BRANCH;
    $loginResult->module = $loginResponse->objs[0]->MODULE;
    $loginResult->refid = $loginResponse->objs[0]->REFID;
    $loginResult->clientId = $loginResponse->clientID;

    $authenticateResult = $this->soft1ApiService->Authenticate($loginResult, $subdomain);
    if (!$authenticateResult->success)
        throw new \Exception($authenticateResult->error);

    return $authenticateResult->clientID;
}

function GK_121()
{
}

function CreateWooProducts($rawProducts)
{
    $wooProducts = array();
    foreach ($rawProducts as $rawProduct)
    {
        $wooProduct = new WooProduct();
        $wooProduct->sku = isset($rawProduct["sku"]) ? $rawProduct["sku"] : "";
        $wooProduct->regular_price = isset($rawProduct["regular_price"]) ? $rawProduct["regular_price"] : "";
        $wooProduct->sale_price = isset($rawProduct["sale_price"]) ? $rawProduct["sale_price"] : "";
        $wooProduct->stock =  isset($rawProduct["stock"]) ? $rawProduct["stock"] : "";
        $wooProduct->stock_status =  isset($rawProduct["stock_status"]) ? $rawProduct["stock_status"] : "";
        $wooProduct->tags = isset($rawProduct["tags"]) ? $rawProduct["tags"] : "";
        $wooProduct->categories =  isset($rawProduct["categories"]) ? $rawProduct["categories"] : "";
        $wooProduct->catalog_visibility = isset($rawProduct["catalog_visibility"]) ? $rawProduct["catalog_visibility"] : "";
        $wooProduct->is_featured =  isset($rawProduct["is_featured"]) ? $rawProduct["is_featured"] : "";
        $wooProducts[] = $wooProduct;
    }
    return $wooProducts;
}

//archcommerce process

function GK_ARCHCOMMERCE_PROCESS()
{
}

function archcommerce_process_order_items($wc_order)
{
    $items = $wc_order->get_items();
    $result = array();
    foreach ($items as $item) :
        $product = $item->get_product();
        $result[] = array(
            'sku' => $product->get_sku(),
            'qty' => sprintf("%s", $item['qty']),
            'price' => $product->get_price(),
            'comments' => ""
        );
    endforeach;
    return $result;
}
function archcommerce_process_order_data($wc_order)
{
    $result = array();
    $result['shipping_method'] = $wc_order->get_shipping_method();
    $result['payment_method_title'] = $wc_order->get_payment_method_title();
    $result['payment_method'] = $wc_order->get_payment_method();
    $result['cart_tax'] = $wc_order->get_cart_tax();
    $result['currency'] = $wc_order->get_currency();
    $result['discount_tax'] = $wc_order->get_discount_tax();
    $result['discount_total'] = $wc_order->get_discount_total();
    $result['shipping_tax'] = $wc_order->get_shipping_tax();
    $result['shipping_total'] = $wc_order->get_shipping_total();
    $result['subtotal'] = $wc_order->get_subtotal();
    $result['total'] = $wc_order->get_total();
    $result['total_discount'] = $wc_order->get_total_discount();
    $result['total_tax'] = $wc_order->get_total_tax();
    $result['cod_payment_fee'] = 0;
    return $result;
}
function archcommerce_process_order_status($wc_order)
{
    return $wc_order->get_status();
}
function archcommerce_process_customer_note($wc_order)
{
    return $wc_order->get_customer_note();
}
function archcommerce_process_customer_info($wc_order)
{
    $result = array();
    $result['ip_address'] = $wc_order->get_customer_ip_address();
    $result['user_agent'] = $wc_order->get_customer_user_agent();
    return $result;
}

function archcommerce_process_customer_shipping($wc_order)
{
    $result = array();
    $result['first_name'] = $wc_order->get_shipping_first_name();
    $result['last_name'] = $wc_order->get_shipping_last_name();
    $result['company'] = $wc_order->get_shipping_company();
    $result['address_1'] = $wc_order->get_shipping_address_1();
    $result['address_2'] = $wc_order->get_shipping_address_2();
    $result['city'] = $wc_order->get_shipping_city();
    $result['state'] = $wc_order->get_shipping_state();
    $result['postcode'] = $wc_order->get_shipping_postcode();
    $result['country'] = $wc_order->get_shipping_country();
    return $result;
}
function archcommerce_process_customer_billing($wc_order)
{
    $result = array();
    $result['first_name'] = $wc_order->get_billing_first_name();
    $result['last_name'] = $wc_order->get_billing_last_name();
    $result['company'] = $wc_order->get_billing_company();
    $result['address_1'] = $wc_order->get_billing_address_1();
    $result['address_2'] = $wc_order->get_billing_address_2();
    $result['city'] = $wc_order->get_billing_city();
    $result['state'] = $wc_order->get_billing_state();
    $result['postcode'] = $wc_order->get_billing_postcode();
    $result['country'] = $wc_order->get_billing_country();
    $result['email'] = $wc_order->get_billing_email();
    $result['phone'] = $wc_order->get_billing_phone();
    return $result;
}

function archcommerce_invoice_requested($wc_order)
{
    return false;
}


//fetch products
function GK_FETCH_PRODUCTS()
{
}
function FetchProducts(\DateTime $lastUpdateDate = null)
{
    $user = Auth::user();
    $platformSettings = $user->platformSettings;

    if (is_null($lastUpdateDate))
    {
        $lastUpdateDate = new \DateTime();
        $lastUpdateDate->setTimezone(new \DateTimeZone("Europe/Athens"));
        $date_interval_string = sprintf("PT%dH", 1);
        $date_interval = new \DateInterval($date_interval_string);
        $lastUpdateDate->sub($date_interval);
    }


    $now = new \DateTime();

    $now->setTimezone(new \DateTimeZone("Europe/Athens"));

    $soft1_time_bug_offset = $platformSettings->soft1_time_bug_offset;

    if ($soft1_time_bug_offset < 0)
    {
        $date_interval_string = sprintf("PT%dH", abs($soft1_time_bug_offset));
        $date_interval = new \DateInterval($date_interval_string);
        $lastUpdateDate->sub($date_interval);
        $now->sub($date_interval);
    }
    else if ($soft1_time_bug_offset > 0)
    {
        $date_interval_string = sprintf("PT%dH", abs($soft1_time_bug_offset));
        $date_interval = new \DateInterval($date_interval_string);
        $lastUpdateDate->add($date_interval);
        $now->add($date_interval);
    }


    $filters = empty($platformSettings->browser_filters) ? "" : $platformSettings->browser_filters . "&";
    $filters = $filters .   sprintf(
        "ITEM.UPDDATE=%s&ITEM.UPDDATE_TO=%s",
        $lastUpdateDate->format('Y-m-d H:i:s'),
        substr_replace($now->format('Y-m-d H:i:s'), "00", -2)
    );


    if ($this->soft1GetClientIdService->IsClientIdStale($platformSettings->s1_client_id_aquired_at))
    {
        $soft1ClientId = $this->soft1GetClientIdService->GetClientId(
            $platformSettings->username,
            $platformSettings->password,
            $platformSettings->appId,
            $platformSettings->subdomain
        );
        $platformSettings->s1_client_id = $soft1ClientId;
        $platformSettings->s1_client_id_aquired_at = new \DateTime();
        $platformSettings->save();
    }
    else
        $soft1ClientId = $platformSettings->s1_client_id;

    //todo set limit from userSettings or platformSettings
    $rawProducts =  $this->soft1WebServiceApi->GetBrowser(
        $platformSettings->subdomain,
        $soft1ClientId,
        $platformSettings->appId,
        $platformSettings->browser_list,
        $filters,
        0,
        $platformSettings->browser_limit,
    );
    return $rawProducts;
}
function FetchUpdated(\DateTime $fromUpdateDate, \DateTime $toUpdateDate)
{
    $user = Auth::user();
    $platformSettings = $user->platformSettings;

    $soft1_time_bug_offset = $platformSettings->soft1_time_bug_offset;

    if ($soft1_time_bug_offset < 0)
    {
        $date_interval_string = sprintf("PT%dH", abs($soft1_time_bug_offset));
        $date_interval = new \DateInterval($date_interval_string);
        $fromUpdateDate->sub($date_interval);
        $toUpdateDate->sub($date_interval);
    }
    else if ($soft1_time_bug_offset > 0)
    {
        $date_interval_string = sprintf("PT%dH", abs($soft1_time_bug_offset));
        $date_interval = new \DateInterval($date_interval_string);
        $fromUpdateDate->add($date_interval);
        $toUpdateDate->add($date_interval);
    }


    $filters = empty($platformSettings->browser_filters) ? "" : $platformSettings->browser_filters . "&";
    $filters = $filters .   sprintf(
        "ITEM.UPDDATE=%s&ITEM.UPDDATE_TO=%s",
        $fromUpdateDate->format('Y-m-d H:i:s'),
        substr_replace($toUpdateDate->format('Y-m-d H:i:s'), "00", -2)
    );


    if ($this->soft1GetClientIdService->IsClientIdStale($platformSettings->s1_client_id_aquired_at))
    {
        $soft1ClientId = $this->soft1GetClientIdService->GetClientId(
            $platformSettings->username,
            $platformSettings->password,
            $platformSettings->appId,
            $platformSettings->subdomain
        );
        $platformSettings->s1_client_id = $soft1ClientId;
        $platformSettings->s1_client_id_aquired_at = new \DateTime();
        $platformSettings->save();
    }
    else
        $soft1ClientId = $platformSettings->s1_client_id;

    //todo set limit from userSettings or platformSettings
    $rawProducts =  $this->soft1WebServiceApi->GetBrowser(
        $platformSettings->subdomain,
        $soft1ClientId,
        $platformSettings->appId,
        $platformSettings->browser_list,
        $filters,
        0,
        $platformSettings->browser_limit,
    );
    return $rawProducts;
}

function GK_INSERT_ORDER()
{
}


function InsertOrder($order, $customer)
{
    $user = Auth::user();
    $this->platformSettings = $user->platformSettings;
    $this->orderStatusMappingsWooSoft1 = $user->orderStatusMappingsWooSoft1;
    $this->orderPaymentMappingsWooSoft1 = $user->orderPaymentMappingsWooSoft1;
    $this->order = $order;
    $this->customer = $customer;

    if ($this->soft1GetClientIdService->IsClientIdStale($this->platformSettings->s1_client_id_aquired_at))
    {
        $this->soft1ClientId = $this->soft1GetClientIdService->GetClientId(
            $this->platformSettings->username,
            $this->platformSettings->password,
            $this->platformSettings->appId,
            $this->platformSettings->subdomain
        );
        $this->platformSettings->s1_client_id = $this->soft1ClientId;
        $this->platformSettings->s1_client_id_aquired_at = new \DateTime();
        $this->platformSettings->save();
    }
    else
        $this->soft1ClientId  = $this->platformSettings->s1_client_id;
    // sales_doc
    $request_data['SALDOC'] = $this->BuildSalesDocument();

    // order items
    $orderItems = $this->BuildOrderItems();
    if ($orderItems == null) return false;
    $request_data['ITELINES'] = $orderItems;

    // expenses analytics
    $expn_lines = $this->BuildExpenseAnalytics();
    if (count($expn_lines) > 0)
        $request_data['EXPANAL'] = $expn_lines;

    // prepare and send request
    $request_data = (object)$request_data;

    $result = $this->soft1WebServiceApiService->InsertData(
        $this->platformSettings->subdomain,
        $this->soft1ClientId,
        $this->platformSettings->appId,
        'SALDOC',
        $request_data
    );

    return $result;
}
function InsertCustomer($customer, $code_mask = NULL)
{
    if (!isset($customer["billing"]) || !isset($customer["billing"]["email"]))
        return 0;

    $code = is_null($code_mask) || strlen($code_mask) == 0 ? $customer["billing"]["email"] : $code_mask;

    $address = $customer["billing"]["address_1"];

    if (strlen($customer["billing"]["address_2"]) > 0)
        $address = $customer["billing"]["address_1"] . ", " . $customer["billing"]["address_2"];

    $request_data["CUSTOMER"] = [
        "CODE" => $code,
        "NAME" => $customer["billing"]["last_name"] . " " . $customer["billing"]["first_name"],
        "ADDRESS" => $address,
        "ZIP" => $customer["billing"]["postcode"],
        "CITY" => $customer["billing"]["city"],
        "EMAIL" => $customer["billing"]["email"],
        "PHONE01" => $customer["billing"]["phone"],
    ];

    // prepare and send request
    $request_data = (object)$request_data;
    $result = $this->soft1WebServiceApiService->InsertData(
        $this->platformSettings->subdomain,
        $this->soft1ClientId,
        $this->platformSettings->appId,
        'CUSTOMER',
        $request_data
    );

    if (isset($result->success) && $result->success == true)
        return intval($result->id);

    return 0;
}
function GetTrdrByEmail($email)
{
    if (strlen($this->platformSettings->browser_list_customer) < 1)
        return 0;
    $filters = empty($this->platformSettings->browser_filters_customer) ? "" : $this->platformSettings->browser_filters_customer . "&";
    $filters = $filters . sprintf(
        "CUSTOMER.EMAIL=%s&CUSTOMER.EMAIL_TO=%s",
        $email,
        $email
    );

    $results =  $this->soft1WebServiceApiService->GetBrowser(
        $this->platformSettings->subdomain,
        $this->soft1ClientId,
        $this->platformSettings->appId,
        $this->platformSettings->browser_list_customer,
        $filters,
        0,
        1,
        "CUSTOMER"
    );

    if (count($results) > 0)
    {
        $trdr = $results[0]["id"];
        $trdr = str_replace("CUSTOMER;", "", $trdr);
        return intval($trdr);
    }
    else
    {
        return 0;
    }
}
function GetMtrlFromCode($code)
{
    $filters = empty($this->platformSettings->browser_filters) ? "" : $this->platformSettings->browser_filters . "&";
    $filters = $filters . sprintf(
        "ITEM.CODE=%s*",
        $code
    );
    $result =  $this->soft1WebServiceApiService->GetBrowser(
        $this->platformSettings->subdomain,
        $this->soft1ClientId,
        $this->platformSettings->appId,
        $this->platformSettings->browser_list,
        $filters,
        0,
        1
    );

    if (count($result) > 0)
    {
        $mtrl = $result[0]["id"];
        $mtrl = str_replace("ITEM;", "", $mtrl);
        return $mtrl;
    }

    return null;
}
function GetSaldocSeriesCode()
{
    $sales_doc_series_code = $this->platformSettings->saldoc_series_code;

    if (
        $this->order["invoice_requested"] == true
        && $this->platformSettings->saldoc_series_code_invoice != null
    )
        $sales_doc_series_code = $this->platformSettings->saldoc_series_code_invoice;

    return $sales_doc_series_code;
}
function GetCustomerTrdr()
{
    $customerTrdr = 0;

    //select customer trdr
    if ($this->platformSettings->use_default_customer == true)
    {
        $customerTrdr = $this->platformSettings->eshop_customer_trdr;
    }
    else
    {
        $customerCodeMask = $this->platformSettings->customer_code_mask;
        if (isset($this->customer["billing"]) && isset($this->customer["billing"]["email"]))
            $customerTrdr = $this->GetTrdrByEmail($this->customer["billing"]["email"]);
        if ($customerTrdr < 1)
            $customerTrdr = $this->InsertCustomer($this->customer, $customerCodeMask);
        if ($customerTrdr < 1)
            $customerTrdr = $this->platformSettings->eshop_customer_trdr;
    }
    return $customerTrdr;
}
function BuildSalesDocument()
{
    $sales_doc = array(
        'SERIES' => $this->GetSaldocSeriesCode(),
        'TRDR' => $this->GetCustomerTrdr(),
        'REMARKS' => $this->BuildOrderRemarks(),
    );

    $finstateId = $this->GetFinstateId();
    if ($finstateId > -1)
        $sales_doc['FINSTATES'] = $finstateId;

    $paymentId = $this->GetPaymentId();
    if ($paymentId > -1)
        $sales_doc['PAYMENT'] = $paymentId;

    $sales_doc = array((object)$sales_doc);
    return $sales_doc;
}
function BuildOrderRemarks()
{
    $remarks = "";
    $remarks .= $this->CreateRemarksLine("=== WooCommerce order info ===");
    $remarks .= $this->CreateRemarksSpace();
    //customer note
    $remarks .= $this->CreateRemarksLine("*** order id: " . $this->order["id"]);
    $remarks .= $this->CreateRemarksLine("*** order status: " . $this->order["status"]);
    $remarks .= $this->CreateRemarksLine("*** customer note: " . $this->customer["note"]);
    $remarks .= $this->CreateRemarksSpace();
    //order info
    $remarks .= $this->CreateRemarksTitle("Order info");
    foreach ($this->order["data"] as $key => $value) :
        $remarks .= $this->CreateRemarksLine($key . ": " . $value);
    endforeach;
    //customer billing
    $remarks .= $this->CreateRemarksSpace();
    $remarks .= $this->CreateRemarksTitle("Billing");
    foreach ($this->customer["billing"] as $key => $value) :
        $remarks .= $this->CreateRemarksLine($key . ": " . $value);
    endforeach;
    //customer shipping
    $remarks .= $this->CreateRemarksSpace();
    $remarks .= $this->CreateRemarksTitle("Shipping");
    foreach ($this->customer["shipping"] as $key => $value) :
        $remarks .= $this->CreateRemarksLine($key . ": " . $value);
    endforeach;
    //customer info and note
    $remarks .= $this->CreateRemarksSpace();
    $remarks .= $this->CreateRemarksTitle("Customer info and note");
    foreach ($this->customer["info"] as $key => $value) :
        $remarks .= $this->CreateRemarksLine($key . ": " . $value);
    endforeach;

    return $remarks;
}
function BuildExpenseAnalytics()
{
    $expn_lines  = array();
    // shipment expense
    if (
        isset($this->order["data"]['shipping_total']) &&
        $this->order["data"]['shipping_total'] > 0 &&
        !empty($this->platformSettings->shipping_expense_code)
    )
    {

        $expnResponse = $this->GetExpense($this->platformSettings->shipping_expense_code);
        if ($expnResponse->success === true)
        {
            $vatString = $expnResponse->data->EXPN[0]->VAT;
            $vatId = explode("|", $vatString)[0];
        }

        if (!isset($vatId)) return [];

        $vatPercent = $this->GetExpenseVatPercentage($vatId);

        if ($vatPercent > 0)
        {
            $paymentFee = floatval($this->order["data"]['shipping_total']);
            $expNetValue = $paymentFee / (1 + ($vatPercent / 100));
        }
        else
        {
            $expNetValue = 0;
        }

        if ($expNetValue > 0)
        {
            $shipment_expn_lines = array(
                'EXPN' => $this->platformSettings->shipping_expense_code,
                'EXPVAL' => $expNetValue
            );
            array_push($expn_lines, (object)$shipment_expn_lines);
        }
    }

    // cash on delivery payment fee expense
    if (
        isset($this->order["data"]['cod_payment_fee']) &&
        $this->order["data"]['cod_payment_fee'] > 0 &&
        !empty($this->platformSettings->cod_fee_expense_code)
    )
    {
        $expnResponse = $this->GetExpense($this->platformSettings->cod_fee_expense_code);
        if ($expnResponse->success === true)
        {
            $vatString = $expnResponse->data->EXPN[0]->VAT;
            $vatId = explode("|", $vatString)[0];
        }

        if (!isset($vatId)) return [];

        $vatPercent = $this->GetExpenseVatPercentage($vatId);

        if ($vatPercent > 0)
        {
            $paymentFee = floatval($this->order["data"]['cod_payment_fee']);
            $expNetValue = $paymentFee / (1 + ($vatPercent / 100));
        }
        else
        {
            $expNetValue = 0;
        }

        if ($expNetValue > 0)
        {
            $cod_expn_lines = array(
                'EXPN' => $this->platformSettings->cod_fee_expense_code,
                'EXPVAL' => $expNetValue
            );
            array_push($expn_lines, (object)$cod_expn_lines);
        }
    }

    return $expn_lines;
}
function GetFinstateId()
{
    foreach ($this->orderStatusMappingsWooSoft1 as $mapping)
    {
        if ($mapping->woo_status_name == $this->order["status"])
            return intval($mapping->soft1_status_id);
    }
    return -1;
}
function GetPaymentId()
{
    foreach ($this->orderPaymentMappingsWooSoft1 as $mapping)
    {
        if ($mapping->woo_payment_name == $this->order["data"]["payment_method"])
            return intval($mapping->soft1_payment_id);
    }
    return -1;
}
function GetExpense($expenseId)
{
    return $this->soft1WebServiceApiService->GetData(
        $this->platformSettings->subdomain,
        $this->soft1ClientId,
        $this->platformSettings->appId,
        'EXPENSES',
        $expenseId
    );
}
function GetExpenseVatPercentage($vatId): float
{
    $vatResponse = $this->soft1WebServiceApiService->GetData(
        $this->platformSettings->subdomain,
        $this->soft1ClientId,
        $this->platformSettings->appId,
        'VAT',
        $vatId
    );

    if ($vatResponse->success === true)
        return floatval($vatResponse->data->VAT[0]->PERCNT);
    else
        return 0;
}
function BuildOrderItems()
{
    $item_lines = array();
    if (isset($this->order["items"]))
    {
        foreach ($this->order["items"]  as $item) :
            $code = $item["sku"];
            $mtrl = $this->GetMtrlFromCode($code);
            if ($mtrl)
            {
                $price = floatval($item["price"]);
                $qnt = intval($item['qty']);
                $arr_obj = array(
                    'MTRL' => $mtrl,
                    'PRICE' =>  $price,
                    $this->platformSettings->quantity_field => $qnt,
                    'COMMENTS1' => $item['comments']
                );
                $item_lines[] =  (object)$arr_obj;
            }
        endforeach;
    }
    return $item_lines;
}
function CreateRemarksLine($line)
{
    return "$line\r\n";
}
function CreateRemarksTitle($title)
{
    $remarks = "";
    $remarks .= "===========================\r\n";
    $remarks .= "$title\r\n";
    $remarks .= "===========================\r\n";
    return $remarks;
}
function CreateRemarksSpace()
{
    $remarks = "";
    $remarks .= "\r\n";
    $remarks .= "\r\n";
    return $remarks;
}
