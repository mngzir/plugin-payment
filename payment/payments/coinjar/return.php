<?php

    $data = payment_get_custom(Params::getParam('extra'));

    $product_type = explode('x', Params::getParam('item_number'));
    $tx = Params::getParam('tx')==''?Params::getParam('tx'):Params::getParam('txn_id');
    $payment = ModelPayment::newInstance()->getPayment($tx);
    if (isset($payment['pk_i_id'])) {
        osc_add_flash_ok_message(__('Payment processed correctly', 'payment'));
        if($product_type[0]==101) {
            $item = Item::newInstance()->findByPrimaryKey($product_type[2]);
            $category = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);
            View::newInstance()->_exportVariableToView('category', $category);
            osc_redirect_to(osc_search_category_url());
        } else if($product_type[0]==201) {
            if(osc_is_web_user_logged_in()) {
                osc_redirect_to(osc_route_url('payment-user-menu'));
            } else {
                View::newInstance()->_exportVariableToView('item', Item::newInstance()->findByPrimaryKey($product_type[2]));
                osc_redirect_to(osc_item_url());
            }
        } else {
            if(osc_is_web_user_logged_in()) {
                osc_redirect_to(osc_route_url('payment-user-pack'));
            } else {
                // THIS SHOULD NOT HAPPEN
                osc_redirect_to(osc_base_path());
            }
        }
    } else {
        osc_add_flash_info_message(__('We are processing your payment, if we did not finish in a few seconds, please contact us', 'payment'));
        if($product_type[0]==301) {
            if(osc_is_web_user_logged_in()) {
                osc_redirect_to(osc_route_url('payment-user-pack'));
            } else {
                // THIS SHOULD NOT HAPPEN
                osc_redirect_to(osc_base_path());
            }
        } else {
            if(osc_is_web_user_logged_in()) {
                osc_redirect_to(osc_route_url('payment-user-menu'));
            } else {
                View::newInstance()->_exportVariableToView('item', Item::newInstance()->findByPrimaryKey($product_type[2]));
                osc_redirect_to(osc_item_url());
            }
        }
    }
?>