<?php if ( ! defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

    class PaymentDataTable extends DataTable
    {

        public function __construct() {
            osc_add_filter('datatable_payment_class', array(&$this, 'row_class'));
        }

        public function table($params) {
            $this->addTableHeader();
            $this->mSearch = new Search(true);
            $this->getDBParams($params);
            // do Search
            $this->processData(ModelPayment::newInstance()->searchPacks($this->start, $this->limit));
            //$this->totalFiltered = $this->total = $this->total;

            return $this->getData();
        }

        private function addTableHeader() {

            $this->addColumn('status-border', '');
            $this->addColumn('status', __('Status', 'payment'));
            $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
            $this->addColumn('name', __('Name', 'payment'));
            $this->addColumn('title', __('Title', 'payment'));
            $this->addColumn('days', __('Days active', 'payment'));
            $this->addColumn('pictures', __('# of pictures', 'payment'));
            $this->addColumn('ads', __('# of ads', 'payment'));
            $this->addColumn('premium', __('Premium', 'payment'));
            $this->addColumn('expiration', __('Expiration', 'payment'));
            $this->addColumn('price', __('Price', 'payment'));

            $dummy = &$this;
            osc_run_hook("admin_payment_table", $dummy);
        }

        private function processData($packs) {
            if(!empty($packs)) {

                $csrf_token_url = osc_csrf_token_url();
                foreach($packs as $aRow) {
                    View::newInstance()->_exportVariableToView('item', $aRow);
                    $row     = array();
                    $options = array();
                    // -- prepare data --
                    // prepare item title
                    $title = mb_substr($aRow['s_title'], 0, 30, 'utf-8');
                    if($title != $aRow['s_title']) {
                        $title .= '...';
                    }

                    //icon open add new window
                    $title .= '<span class="icon-new-window"></span>';

                    // Options of each row
                    $options_more = array();
                    //, name, title, days, pictures, ads, premium, expiration, price
                    $options[] = '<a href="javascript:edit_dialog('.$aRow['pk_i_id'].',\''.$aRow['s_name'].'\',\''.$aRow['s_title'].'\','.$aRow['i_days'].','.$aRow['i_pictures'].','.$aRow['i_ads'].','.$aRow['b_premium'].',\''.$aRow['dt_expiration'].'\','.$aRow['i_price'].','.$aRow['b_enabled'].');">' . __('Edit', 'payment') . '</a>';
                    $options[] = '<a href="javascript:delete_dialog('.$aRow['pk_i_id'].');">' . __('Delete', 'payment') . '</a>';
                    if($aRow['b_enabled']==1) {
                        $options[] = '<a href="'.osc_route_admin_url('payment-admin-packs', array('plugin_action' => 'disable', 'id[]' => $aRow['pk_i_id'])).'&amp;'.$csrf_token_url.'">'.__('Disable', 'payment').'</a>';
                    } else {
                        $options[] = '<a href="'.osc_route_admin_url('payment-admin-packs', array('plugin_action' => 'enable', 'id[]' => $aRow['pk_i_id'])).'&amp;'.$csrf_token_url.'">'.__('Enable', 'payment').'</a>';
                    }

                    $options_more = osc_apply_filter('more_actions_manage_payment', $options_more, $aRow);
                    // more actions
                    $moreOptions = '<li class="show-more">'.PHP_EOL.'<a href="#" class="show-more-trigger">'. __('Show more') .'...</a>'. PHP_EOL .'<ul>'. PHP_EOL;
                    foreach( $options_more as $actual) {
                        $moreOptions .= '<li>'.$actual."</li>".PHP_EOL;
                    }
                    $moreOptions .= '</ul>'. PHP_EOL .'</li>'.PHP_EOL;

                    $options = osc_apply_filter('actions_manage_payment', $options, $aRow);
                    // create list of actions
                    $auxOptions = '<ul>'.PHP_EOL;
                    foreach( $options as $actual) {
                        $auxOptions .= '<li>'.$actual.'</li>'.PHP_EOL;
                    }
                    if(!empty($options_more)) {
                        $auxOptions  .= $moreOptions;
                    }
                    $auxOptions  .= '</ul>'.PHP_EOL;

                    $actions = '<div class="actions">'.$auxOptions.'</div>'.PHP_EOL;

                    // fill a row
                    $status = $this->get_row_status($aRow);
                    $row['status-border'] = '';
                    $row['status'] = $status['text'];
                    $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" enabled="' . $aRow['b_enabled'] . '"/>';
                    $row['name'] = $aRow['s_name'];
                    $row['title'] = $aRow['s_title'].$actions;
                    $row['days'] = $aRow['i_days'];
                    $row['pictures'] = $aRow['i_pictures'];
                    $row['ads'] = $aRow['i_ads'];
                    $row['premium'] = $aRow['b_premium'];
                    $row['expiration'] = $aRow['dt_expiration'];
                    $row['price'] = $aRow['i_price'];

                    $row = osc_apply_filter('payment_processing_row', $row, $aRow);

                    $this->addRow($row);
                    $this->rawRows[] = $aRow;
                }

            }
        }

        private function getDBParams($_get) {

            if(!isset($_get['iDisplayStart'])) {
                $_get['iDisplayStart'] = 0;
            }
            if(!isset($_get['iDisplayLength'])) {
                $_get['iDisplayLength'] = 10;
            }

            if(!is_numeric($_get['iPage']) || $_get['iPage'] < 1) {
                Params::setParam('iPage', 1 );
                $this->iPage = 1;
            } else {
                $this->iPage = $_get['iPage'];
            }

            // set start and limit using iPage param
            $start = ($this->iPage - 1) * $_get['iDisplayLength'];

            $this->start = intval( $start );
            $this->limit = intval( $_get['iDisplayLength'] );

        }

        public function rawRows() {
            return $this->rawRows;
        }

        public function row_class($class, $rawRow, $row) {
            $status = $this->get_row_status($rawRow);
            $class[] = $status['class'];
            return $class;
        }

        private function get_row_status($rawRow) {
            if($rawRow['b_enabled']==1) {
                return array(
                    'class' => 'status-active',
                    'text'  => __('Active')
                );
            }

            return array(
                'class' => 'status-inactive',
                'text'  => __('Inactive')
            );
        }

    }

?>
