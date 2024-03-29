<?php
// -----
// A Zen Cart payment method to accept customers' payment via credit-card
// transmitted via phone call.
//
// Copyright (C) 2024, Vinos de Frutas Tropicales (lat9).
//
class call_in_payment extends base
{
    public
        $code,
        $title,
        $description,
        $sort_order,
        $order_status,
        $enabled,
        $email_footer,
        $zone;

    protected
        $_check;

    public function __construct()
    {
        global $order;

        $this->code = 'call_in_payment';
        $this->title = MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_TITLE;
        $this->description = (IS_ADMIN_FLAG === true) ? MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_DESCRIPTION_ADMIN : MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_CALL_IN_PAYMENT_SORT_ORDER') ? (int)MODULE_PAYMENT_CALL_IN_PAYMENT_SORT_ORDER : null;

        if (null === $this->sort_order) {
            return false;
        }

        $this->enabled = (MODULE_PAYMENT_CALL_IN_PAYMENT_STATUS === 'True' && MODULE_PAYMENT_CALL_IN_PAYMENT_PHONE !== '');
        if (IS_ADMIN_FLAG === true && MODULE_PAYMENT_CALL_IN_PAYMENT_PHONE === '') {
            $this->title .= '<span class="alert"> (not configured - needs phone number)</span>';
        }

        $this->order_status = (int)MODULE_PAYMENT_CALL_IN_PAYMENT_ORDER_STATUS_ID;

        if (isset($order) && is_object($order)) {
            $this->update_status();
        }

        $this->email_footer = sprintf(MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_EMAIL_FOOTER, MODULE_PAYMENT_CALL_IN_PAYMENT_PHONE);

        $this->zone = (int)MODULE_PAYMENT_CALL_IN_PAYMENT_ZONE;
    }

    public function update_status()
    {
        global $order, $db;

        if ($this->enabled === true && $this->zone > 0 && isset($order->billing['country']['id'])) {
            $check_flag = false;
            $check = $db->Execute(
                "SELECT zone_id 
                   FROM " . TABLE_ZONES_TO_GEO_ZONES . " 
                  WHERE geo_zone_id = {$this->zone}
                    AND zone_country_id = " . (int)$order->billing['country']['id'] . " 
               ORDER BY zone_id"
            );
            foreach ($check as $next_zone) {
                if ($next_zone['zone_id'] < 1 || $next_zone['zone_id'] === $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag === false) {
                $this->enabled = false;
            }
        }
    }

    public function javascript_validation()
    {
        return false;
    }

    public function selection()
    {
        return [
            'id' => $this->code,
            'module' => $this->title,
        ];
    }

    public function pre_confirmation_check()
    {
        return false;
    }

    public function confirmation()
    {
        return [
            'title' => MODULE_PAYMENT_CALL_IN_PAYMENT_TEXT_DESCRIPTION
        ];
    }

    public function process_button()
    {
        return false;
    }

    public function before_process()
    {
        return false;
    }

    public function after_process()
    {
        return false;
    }

    function get_error()
    {
        return false;
    }

    public function check()
    {
        global $db;

        if (!isset($this->_check)) {
            $check_query = $db->Execute(
                "SELECT configuration_value 
                   FROM " . TABLE_CONFIGURATION . " 
                  WHERE configuration_key = 'MODULE_PAYMENT_CALL_IN_PAYMENT_STATUS'
                  LIMIT 1"
            );
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    public function install()
    {
        global $db;

        $call_in_phone = (defined('STORE_TELEPHONE_CUSTSERVICE')) ? STORE_TELEPHONE_CUSTSERVICE : '';
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " 
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) 
             VALUES 
                ('Enable Zelle&reg; Payment Module', 'MODULE_PAYMENT_CALL_IN_PAYMENT_STATUS', 'True', 'Do you want to accept credit-card payments via phone call?', 6, 1, NULL, 'zen_cfg_select_option([\'True\', \'False\'], ', now()),

                ('Phone Number to Call', 'MODULE_PAYMENT_CALL_IN_PAYMENT_PHONE', '$call_in_phone', 'What phone number should customers call to make their payment?', 6, 1, NULL, NULL, now()),

                ('Sort order of display.', 'MODULE_PAYMENT_CALL_IN_PAYMENT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', 6, 1, NULL, NULL, now()),

                ('Payment Zone', 'MODULE_PAYMENT_CALL_IN_PAYMENT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', 6, 1, 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now()),

                ('Set Order Status', 'MODULE_PAYMENT_CALL_IN_PAYMENT_ORDER_STATUS_ID', " . DEFAULT_ORDERS_STATUS_ID . ", 'Set the status of orders made with this payment module to this value', 6, 1, 'zen_get_order_status_name', 'zen_cfg_pull_down_order_statuses(', now())"
        );
    }

    public function remove()
    {
        global $db;
        $db->Execute(
            "DELETE FROM " . TABLE_CONFIGURATION . " 
              WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')"
        );
    }

    public function keys()
    {
        return [
            'MODULE_PAYMENT_CALL_IN_PAYMENT_STATUS', 
            'MODULE_PAYMENT_CALL_IN_PAYMENT_ZONE', 
            'MODULE_PAYMENT_CALL_IN_PAYMENT_ORDER_STATUS_ID', 
            'MODULE_PAYMENT_CALL_IN_PAYMENT_SORT_ORDER', 
            'MODULE_PAYMENT_CALL_IN_PAYMENT_PHONE',
        ];
    }
}
