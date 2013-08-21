<?php

    $data = payment_get_custom(Params::getParam('extra'));
    if(isset($data['itemid'])) {
        if($data['itemid']=='dash') { // PACK PAYMENT FROM USER'S DASHBOARD
            $url = osc_user_dashboard_url();
        } else {
            $item     = Item::newInstance()->findByPrimaryKey($data['itemid']);
            $category = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);
            View::newInstance()->_exportVariableToView('category', $category);
            $url = osc_search_category_url();
        }
    } else {
        $url = osc_base_url();
    }
    osc_add_flash_error_message(__('You cancel the payment process or there was an error. If the error continue, please contact the administrator', 'payment'));
    osc_redirect_to($url);

?>