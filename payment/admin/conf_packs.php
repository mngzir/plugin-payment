<?php if ( ! defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');


require_once dirname(__FILE__)."/PaymentDataTable.php";

if(Params::getParam('plugin_action')=='new') {
    $pack_id = Params::getParam('pack_id');
    $enabled = Params::getParam('enabled')!=1?0:1;
    $short_name = Params::getParam('short_name');
    $title = Params::getParam('title');
    $days = Params::getParam('days');
    $pictures = Params::getParam('pictures');
    $ads = Params::getParam('ads');
    $premium = Params::getParam('premium');
    $expiration = Params::getParam('expiration');
    $price = Params::getParam('price');

    if($pack_id=='') { // ADD
        ModelPayment::newInstance()->insertPack($enabled, $short_name, $title, $days, $pictures, $ads, $premium, $expiration, $price);
        osc_add_flash_ok_message(__('Pack created correctly', 'payment'), 'admin');
    } else{ // EDIT
        ModelPayment::newInstance()->updatePack($pack_id, $enabled, $short_name, $title, $days, $pictures, $ads, $premium, $expiration, $price);
        osc_add_flash_ok_message(__('Pack updated correctly', 'payment'), 'admin');
    }
    ob_get_clean();
    osc_redirect_to(osc_route_admin_url('payment-admin-packs'));
} else if(Params::getParam('plugin_action')=='enable') {
    $ids = Params::getParam('id');
    $mp = ModelPayment::newInstance();
    if(is_array($ids)) { foreach($ids as $id) { $mp->statusPack($id, 1); } }
} else if(Params::getParam('plugin_action')=='disable') {
    $ids = Params::getParam('id');
    $mp = ModelPayment::newInstance();
    if(is_array($ids)) { foreach($ids as $id) { $mp->statusPack($id, 0); } }
} else if(Params::getParam('plugin_action')=='delete') {
    $ids = Params::getParam('id');
    $mp = ModelPayment::newInstance();
    if(is_array($ids)) { foreach($ids as $id) { $mp->deletePack($id); } }
}


// set default iDisplayLength
if( Params::getParam('iDisplayLength') != '' ) {
    Cookie::newInstance()->push('listing_iDisplayLength', Params::getParam('iDisplayLength'));
    Cookie::newInstance()->set();
} else {
    // set a default value if it's set in the cookie
    if( Cookie::newInstance()->get_value('listing_iDisplayLength') != '' ) {
        Params::setParam('iDisplayLength', Cookie::newInstance()->get_value('listing_iDisplayLength'));
    } else {
        Params::setParam('iDisplayLength', 10 );
    }
}

// Table header order by related
if( Params::getParam('sort') == '') {
    Params::setParam('sort', 'date');
}
if( Params::getParam('direction') == '') {
    Params::setParam('direction', 'desc');
}

$page  = (int)Params::getParam('iPage');
if($page==0) { $page = 1; };
Params::setParam('iPage', $page);

$params = Params::getParamsAsArray("get");

$paymentDataTable = new PaymentDataTable();
$aData = $paymentDataTable->table($params);
$aRawRows = $paymentDataTable->rawRows();
$columns    = $aData['aColumns'];
$rows       = $aData['aRows'];

if(count($aData['aRows']) == 0 && $page!=1) {
    $total = (int)$aData['iTotalDisplayRecords'];
    $maxPage = ceil( $total / (int)$aData['iDisplayLength'] );

    $url = osc_admin_base_url(true).'?'.$_SERVER['QUERY_STRING'];

    if($maxPage==0) {
        $url = preg_replace('/&iPage=(\d)+/', '&iPage=1', $url);
        $this->redirectTo($url);
    }

    if($page > 1) {
        $url = preg_replace('/&iPage=(\d)+/', '&iPage='.$maxPage, $url);
        $this->redirectTo($url);
    }
}

View::newInstance()->_exportVariableToView('aData', $aData);

$bulk_options = array(
    array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions', 'payment')),
    array('value' => 'enable', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected packs?', 'payment'), strtolower(__('Enable', 'payment'))), 'label' => __('Enable', 'payment')),
    array('value' => 'disable', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected packs?', 'payment'), strtolower(__('Disable', 'payment'))), 'label' => __('Disable', 'payment')),
    array('value' => 'delete', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected packs?', 'payment'), strtolower(__('Delete', 'payment'))), 'label' => __('Delete', 'payment'))
);
$bulk_options = osc_apply_filter("payment_bulk_filter", $bulk_options);
?>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#dialog-payment-delete").dialog({
                autoOpen: false,
                modal: true,
                title: '<?php echo osc_esc_js(__('Delete payment', 'payment')); ?>'
            });

            $("#dialog-payment-new").dialog({
                autoOpen: false,
                modal: true,
                width: "600px",
                title: '<?php echo osc_esc_js(__('Delete payment', 'payment')); ?>'
            });

            // dialog bulk actions
            $("#dialog-bulk-actions").dialog({
                autoOpen: false,
                modal: true
            });
            $("#bulk-actions-submit").click(function() {
                if($("#bulk-actions-submit").prop("clicked")==false) {
                    $("#bulk-actions-submit").prop("clicked", true);
                    $("#datatablesForm").submit();
                }
            });
            $("#bulk-actions-cancel").click(function() {
                $("#datatablesForm").attr('data-dialog-open', 'false');
                $('#dialog-bulk-actions').dialog('close');
            });
            // dialog bulk actions function
            $("#datatablesForm").submit(function() {
                if( $("#bulk_actions option:selected").val() == "" ) {
                    return false;
                }

                if( $("#datatablesForm").attr('data-dialog-open') == "true" ) {
                    return true;
                }

                $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
                $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
                $("#datatablesForm").attr('data-dialog-open', 'true');
                $("#bulk-actions-submit").prop("clicked", false);
                $("#dialog-bulk-actions").dialog('open');
                return false;
            });

            // check_all bulkactions
            $("#check_all").change(function(){
                var isChecked = $(this).prop("checked");
                $('.col-bulkactions input').each( function() {
                    if( isChecked == 1 ) {
                        this.checked = true;
                    } else {
                        this.checked = false;
                    }
                });
            });
        });

        // dialog delete function
        function delete_dialog(pack_id) {
            $("#dialog-payment-delete input[name='id[]']").attr('value', pack_id);
            $("#dialog-payment-delete").dialog('open');
            return false;
        }
        function new_dialog() {
            $("#pack_id").prop("value", "");
            $("#short_name").prop("value", "");
            $("#title").prop("value", "");
            $("#days").prop("value", "");
            $("#pictures").prop("value", "");
            $("#ads").prop("value", "");
            $("#premium").prop("checked", false);
            $("#enabled").prop("checked", true);
            $("#expiration").prop("value", "");
            $("#price").prop("value", "");
            $("#dialog-payment-new").dialog('open');
        }
        function edit_dialog(id, name, title, days, pictures, ads, premium, expiration, price, enabled) {
            $("#pack_id").prop("value", id);
            $("#short_name").prop("value", name);
            $("#title").prop("value", title);
            $("#days").prop("value", days);
            $("#pictures").prop("value", pictures);
            $("#ads").prop("value", ads);
            $("#premium").prop("checked", (premium==1));
            $("#enabled").prop("checked", (enabled==1));
            $("#expiration").prop("value", expiration);
            $("#price").prop("value", price);
            $("#dialog-payment-new").dialog('open');
        }
    </script>
    <h2 class="render-title"><?php _e('Manage payment packs', 'payment'); ?> <a href="javascript:new_dialog();" class="btn btn-mini"><?php _e('Add new', 'payment'); ?></a></h2>
    <div class="relative">
        <div id="listing-toolbar">
            <div class="float-right">
                <form method="get" action="<?php echo osc_admin_base_url(true); ?>"  class="inline nocsrf">
                    <?php foreach( Params::getParamsAsArray('get') as $key => $value ) { ?>
                        <?php if( $key != 'iDisplayLength' ) { ?>
                            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo osc_esc_html($value); ?>" />
                        <?php } } ?>
                    <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
                        <option value="10"><?php printf(__('%d packs', 'payment'), 10); ?></option>
                        <option value="25" <?php if( Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d packs', 'payment'), 25); ?></option>
                        <option value="50" <?php if( Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d packs', 'payment'), 50); ?></option>
                        <option value="100" <?php if( Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d packs', 'payment'), 100); ?></option>
                    </select>
                </form>
            </div>
        </div>
        <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post" data-dialog-open="false">
            <input type="hidden" name="page" value="plugins" />
            <input type="hidden" name="action" value="renderplugin" />
            <input type="hidden" name="route" value="payment-admin-packs" />
            <div id="bulk-actions">
                <label>
                    <?php osc_print_bulk_actions('bulk_actions', 'plugin_action', $bulk_options, 'select-box-extra'); ?>
                    <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html( __('Apply', 'payment') ); ?>" />
                </label>
            </div>
            <div class="table-contains-actions">
                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <?php foreach($columns as $k => $v) {
                            echo '<th class="col-'.$k.'">'.$v.'</th>';
                        }; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if( count($rows) > 0 ) { ?>
                        <?php foreach($rows as $key => $row) { ?>
                            <tr class="<?php echo implode(' ', osc_apply_filter('datatable_payment_class', array(), $aRawRows[$key], $row)); ?>">
                                <?php foreach($row as $k => $v) { ?>
                                    <td class="col-<?php echo $k; ?>"><?php echo $v; ?></td>
                                <?php }; ?>
                            </tr>
                        <?php }; ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="<?php echo count($columns); ?>" class="text-center">
                                <p><?php _e('No data available in table'); ?></p>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div id="table-row-actions"></div> <!-- used for table actions -->
            </div>
        </form>
    </div>
<?php
function showingResults(){
    $aData = __get('aData');
    echo '<ul class="showing-results"><li><span>'.osc_pagination_showing((Params::getParam('iPage')-1)*$aData['iDisplayLength']+1, ((Params::getParam('iPage')-1)*$aData['iDisplayLength'])+count($aData['aRows']), $aData['iTotalDisplayRecords'], $aData['iTotalRecords']).'</span></li></ul>';
}
osc_add_hook('before_show_pagination_admin','showingResults');
osc_show_pagination_admin($aData);
?>
    <form id="dialog-payment-delete" method="post" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="route" value="payment-admin-packs" />
        <input type="hidden" name="plugin_action" value="delete" />
        <input type="hidden" name="id[]" value="" />
        <div class="form-horizontal">
            <div class="form-row">
                <?php echo osc_apply_filter('admin_dialog_delete_listing_text', __('Are you sure you want to delete this listing?', 'payment')); ?>
            </div>
            <div class="form-actions">
                <div class="wrapper">
                    <a class="btn" href="javascript:void(0);" onclick="$('#dialog-item-delete').dialog('close');"><?php _e('Cancel', 'payment'); ?></a>
                    <input id="item-delete-submit" type="submit" value="<?php echo osc_esc_html( __('Delete', 'payment') ); ?>" class="btn btn-red" />
                </div>
            </div>
        </div>
    </form>
    <div id="dialog-bulk-actions" title="<?php _e('Bulk actions', 'payment'); ?>" class="has-form-actions hide">
        <div class="form-horizontal">
            <div class="form-row"></div>
            <div class="form-actions">
                <div class="wrapper">
                    <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel', 'payment'); ?></a>
                    <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-red" ><?php echo osc_esc_html( __('Delete', 'payment') ); ?></a>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
    <form id="dialog-payment-new" method="post" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
        <input type="hidden" name="page" value="plugins" />
        <input type="hidden" name="action" value="renderplugin" />
        <input type="hidden" name="route" value="payment-admin-packs" />
        <input type="hidden" name="plugin_action" value="new" />
        <input type="hidden" id="pack_id" name="pack_id" value="" />
        <div class="form-horizontal">
            <div class="form-row" >
                <div class="form-label"><?php _e('Enable this offer', 'payment'); ?></div>
                <div class="form-controls">
                    <div class="form-label-checkbox">
                        <label>
                            <input type="checkbox" id="enabled" name="enabled" value="1" />
                            <?php _e('Allow users to purchase this offer', 'payment'); ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-row" >
                <div class="form-label"><?php _e('Short name', 'payment'); ?></div>
                <div class="form-controls">
                    <input type="text" id="short_name" name="short_name" value="" />
                </div>
            </div>
            <div class="form-row" >
                <div class="form-label"><?php _e('Name to show to the users', 'payment'); ?></div>
                <div class="form-controls">
                    <input type="text" id="title" name="title" value="" />
                </div>
            </div>
            <div class="form-row" >
                <div class="form-label"><?php _e("Days of expiration", 'payment'); ?></div>
                <div class="form-controls">
                    <input type="text" id="days" name="days" value="" />
                    <span class="help-box"><?php _e("0 for unlimited, empty to use Osclass's default", 'payment'); ?></span>
                </div>
            </div>
            <div class="form-row" >
                <div class="form-label"><?php _e("Pictures allowed in the listing", 'payment'); ?></div>
                <div class="form-controls">
                    <input type="text" id="pictures" name="pictures" value="" />
                    <span class="help-box"><?php _e("empty to use Osclass's default", 'payment'); ?></span>
                </div>
            </div>
            <div class="form-row" >
                <div class="form-label"><?php _e('Number of listings in this pack', 'payment'); ?></div>
                <div class="form-controls">
                    <input type="text" id="ads" name="ads" value="" />
                </div>
            </div>
            <div class="form-row" >
                <div class="form-label"><?php _e('Expiration of this offer', 'payment'); ?></div>
                <div class="form-controls">
                    <input type="text" id="expiration" name="expiration" value="" />
                </div>
            </div>
            <div class="form-row" >
                <div class="form-label"><?php _e('Price', 'payment'); ?></div>
                <div class="form-controls">
                    <input type="text" id="price" name="price" value="" /> <?php echo osc_get_preference('currency', 'payment'); ?>
                </div>
            </div>
            <div class="form-row" >
                <div class="form-label"><?php _e('Mark the listings as premium?', 'payment'); ?></div>
                <div class="form-controls">
                    <div class="form-label-checkbox">
                        <label>
                            <input type="checkbox" id="premium" name="premium" value="1" />
                            <?php _e('Listings will be marked as premium automatically', 'payment'); ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <div class="wrapper">
                    <a class="btn" href="javascript:void(0);" onclick="$('#dialog-payment-new').dialog('close');"><?php _e('Cancel', 'payment'); ?></a>
                    <input id="payment-submit" type="submit" value="<?php echo osc_esc_html( __('Add', 'payment')); ?>" class="btn btn-red" />
                </div>
            </div>
        </div>
    </form>