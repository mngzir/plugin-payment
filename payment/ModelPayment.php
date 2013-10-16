<?php
    /*
     *      OSCLass – software for creating and publishing online classified
     *                           advertising platforms
     *
     *                        Copyright (C) 2013 OSCLASS
     *
     *       This program is free software: you can redistribute it and/or
     *     modify it under the terms of the GNU Affero General Public License
     *     as published by the Free Software Foundation, either version 3 of
     *            the License, or (at your option) any later version.
     *
     *     This program is distributed in the hope that it will be useful, but
     *         WITHOUT ANY WARRANTY; without even the implied warranty of
     *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *             GNU Affero General Public License for more details.
     *
     *      You should have received a copy of the GNU Affero General Public
     * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */

    /**
     * Model database for payments classes
     *
     * @package OSClass
     * @subpackage Model
     * @since 3.0
     */
    class ModelPayment extends DAO
    {
        /**
         * It references to self object: ModelPayment.
         * It is used as a singleton
         *
         * @access private
         * @since 3.0
         * @var ModelPayment
         */
        private static $instance ;

        /**
         * It creates a new ModelPayment object class ir if it has been created
         * before, it return the previous object
         *
         * @access public
         * @since 3.0
         * @return ModelPayment
         */
        public static function newInstance() {
            if( !self::$instance instanceof self ) {
                self::$instance = new self ;
            }
            return self::$instance ;
        }

        /**
         * Construct
         */
        function __construct() {
            parent::__construct();
        }

        public function getTable_log() {
            return DB_TABLE_PREFIX.'t_payments_log';
        }

        public function getTable_user() {
            return DB_TABLE_PREFIX.'t_payments_user';
        }

        public function getTable_premium() {
            return DB_TABLE_PREFIX.'t_payments_premium';
        }

        public function getTable_publish() {
            return DB_TABLE_PREFIX.'t_payments_publish';
        }

        public function getTable_prices() {
            return DB_TABLE_PREFIX.'t_payments_prices';
        }

        public function getTable_packs() {
            return DB_TABLE_PREFIX.'t_payments_packs';
        }

        /**
         * Import sql file
         * @param type $file
         */
        public function import($file)
        {
            $path = osc_plugin_resource($file) ;
            $sql = file_get_contents($path);

            if(! $this->dao->importSQL($sql) ){
                throw new Exception( "Error importSQL::ModelPayment<br>".$file ) ;
            }
        }

        public function install() {

            $this->import('payment/struct.sql');

            osc_set_preference('version', '300', 'payment', 'INTEGER');
            osc_set_preference('default_premium_cost', '1.0', 'payment', 'STRING');
            osc_set_preference('allow_premium', '0', 'payment', 'BOOLEAN');
            osc_set_preference('default_publish_cost', '1.0', 'payment', 'STRING');
            osc_set_preference('pay_per_post', '0', 'payment', 'BOOLEAN');
            osc_set_preference('premium_days', '7', 'payment', 'INTEGER');
            osc_set_preference('currency', 'USD', 'payment', 'STRING');
            osc_set_preference('pack_price_1', '', 'payment', 'STRING');
            osc_set_preference('pack_price_2', '', 'payment', 'STRING');
            osc_set_preference('pack_price_3', '', 'payment', 'STRING');

            osc_set_preference('paypal_api_username', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('paypal_api_password', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('paypal_api_signature', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('paypal_email', '', 'payment', 'STRING');
            osc_set_preference('paypal_standard', '1', 'payment', 'BOOLEAN');
            osc_set_preference('paypal_sandbox', '1', 'payment', 'BOOLEAN');
            osc_set_preference('paypal_enabled', '0', 'payment', 'BOOLEAN');

            osc_set_preference('blockchain_btc_address', '', 'payment', 'STRING');
            osc_set_preference('blockchain_enabled', '0', 'payment', 'BOOLEAN');

            osc_set_preference('braintree_merchant_id', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('braintree_public_key', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('braintree_private_key', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('braintree_encryption_key', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('braintree_sandbox', 'sandbox', 'payment', 'STRING');
            osc_set_preference('braintree_enabled', '0', 'payment', 'BOOLEAN');

            osc_set_preference('stripe_secret_key', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('stripe_public_key', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('stripe_secret_key_test', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('stripe_public_key_test', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('stripe_sandbox', 'sandbox', 'payment', 'STRING');
            osc_set_preference('stripe_enabled', '0', 'payment', 'BOOLEAN');

            osc_set_preference('coinjar_merchant_user', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('coinjar_merchant_password', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('coinjar_api_key', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('coinjar_sb_merchant_user', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('coinjar_sb_merchant_password', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('coinjar_sb_api_key', payment_crypt(''), 'payment', 'STRING');
            osc_set_preference('coinjar_merchant_reference', osc_sanitizeString(osc_page_title()), 'payment', 'STRING');
            osc_set_preference('coinjar_sandbox', 'sandbox', 'payment', 'STRING');
            osc_set_preference('coinjar_enabled', '0', 'payment', 'BOOLEAN');

            $this->dao->select('pk_i_id') ;
            $this->dao->from(DB_TABLE_PREFIX.'t_item') ;
            $result = $this->dao->get();
            if($result) {
                $items  = $result->result();
                $date = date("Y-m-d H:i:s");
                foreach($items as $item) {
                    $this->createItem($item['pk_i_id'], 1, $date);
                }
            }

            $description[osc_language()]['s_title'] = '{WEB_TITLE} - Publish option for your ad: {ITEM_TITLE}';
            $description[osc_language()]['s_text'] = '<p>Hi {CONTACT_NAME}!</p><p>We just published your item ({ITEM_TITLE}) on {WEB_TITLE}.</p><p>{START_PUBLISH_FEE}</p><p>In order to make your ad available to anyone on {WEB_TITLE}, you should complete the process and pay the publish fee. You could do that on the following link: {PUBLISH_LINK}</p><p>{END_PUBLISH_FEE}</p><p>{START_PREMIUM_FEE}</p><p>You could make your ad premium and make it to appear on top result of the searches made on {WEB_TITLE}. You could do that on the following link: {PREMIUM_LINK}</p><p>{END_PREMIUM_FEE}</p><p>This is an automatic email, if you already did that, please ignore this email.</p><p>Thanks</p>';
            $res = Page::newInstance()->insert(
                array('s_internal_name' => 'email_payment', 'b_indelible' => '1'),
                $description
                );

        }

        public function premiumOff($id) {
            return $this->dao->delete($this->getTable_premium(), array('fk_i_item_id' => $id));
        }

        public function deleteItem($id) {
            $this->premiumOff($id);
            return $this->dao->delete($this->getTable_publish(), array('fk_i_item_id' => $id));
        }

        public function deletePrices($id) {
            return $this->dao->delete($this->getTable_prices(), array('fk_i_category_id' => $id));
        }

        /**
         * Remove data and tables related to the plugin.
         */
        public function uninstall() {
            $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_premium()));
            $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_publish()));
            $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_user()));
            $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_prices()));
            $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_log()));
            $this->dao->query(sprintf('DROP TABLE %s', $this->getTable_packs()));

            $page = Page::newInstance()->findByInternalName('email_payment');
            Page::newInstance()->deleteByPrimaryKey($page['pk_i_id']);

            osc_delete_preference('version', 'payment');
            osc_delete_preference('default_premium_cost', 'payment');
            osc_delete_preference('allow_premium', 'payment');
            osc_delete_preference('default_publish_cost', 'payment');
            osc_delete_preference('pay_per_post', 'payment');
            osc_delete_preference('premium_days', 'payment');
            osc_delete_preference('currency', 'payment');
            osc_delete_preference('pack_price_1', 'payment');
            osc_delete_preference('pack_price_2', 'payment');
            osc_delete_preference('pack_price_3', 'payment');

            osc_delete_preference('paypal_api_username', 'payment');
            osc_delete_preference('paypal_api_password', 'payment');
            osc_delete_preference('paypal_api_signature', 'payment');
            osc_delete_preference('paypal_email', 'payment');
            osc_delete_preference('paypal_standard', 'payment');
            osc_delete_preference('paypal_sandbox', 'payment');
            osc_delete_preference('paypal_enabled', 'payment');

            osc_delete_preference('blockchain_btc_address', 'payment');
            osc_delete_preference('blockchain_enabled', 'payment');

            osc_delete_preference('braintree_merchant_id', 'payment');
            osc_delete_preference('braintree_public_key', 'payment');
            osc_delete_preference('braintree_private_key', 'payment');
            osc_delete_preference('braintree_encryption_key', 'payment');
            osc_delete_preference('braintree_sandbox', 'payment');
            osc_delete_preference('braintree_enabled', 'payment');

            osc_delete_preference('stripe_secret_key', 'payment');
            osc_delete_preference('stripe_public_key', 'payment');
            osc_delete_preference('stripe_secret_key_test', 'payment');
            osc_delete_preference('stripe_public_key_test', 'payment');
            osc_delete_preference('stripe_sandbox', 'payment');
            osc_delete_preference('stripe_enabled', 'payment');
        }

        public function versionUpdate() {
            if( osc_get_preference('version', 'payment') < 200 ) {
                $this->dao->query(sprintf('ALTER TABLE %s ADD i_amount BIGINT(20) NULL AFTER f_amount', ModelPayment::newInstance()->getTable_log()));
                $this->dao->query(sprintf('ALTER TABLE %s ADD i_amount BIGINT(20) NULL AFTER f_amount', ModelPayment::newInstance()->getTable_user()));

                $this->dao->select('*') ;
                $this->dao->from($this->getTable_user());
                $result = $this->dao->get();
                if($result) {
                    $wallets = $result->result();
                    foreach($wallets as $w) {
                        $this->dao->update($this->getTable_user(), array('i_amount' => $w['f_amount']*1000000000000), array('fk_i_user_id' => $w['fk_i_user_id']));
                    }
                }

                $this->dao->select('*') ;
                $this->dao->from($this->getTable_log());
                $result = $this->dao->get();
                if($result) {
                    $logs = $result->result();
                    foreach($logs as $log) {
                        $this->dao->update($this->getTable_log(), array('i_amount' => $log['f_amount']*1000000000000), array('pk_i_id' => $log['pk_i_id']));
                    }
                }
                osc_set_preference('version', 200, 'payment', 'INTEGER');
                osc_reset_preferences();
            }

            if( osc_get_preference('version', 'payment') < 300 ) {
                $this->dao->query(sprtinf('RENAME TABLE  %st_payments_wallet TO %st_payments_user', DB_TABLE_PREFIX, DB_TABLE_PREFIX));
                $this->dao->query(sprintf('ALTER TABLE %s ADD fk_i_pack_id INT UNSIGNED NULL AFTER i_amount', ModelPayment::newInstance()->getTable_user()));
                $this->dao->query(sprintf('ALTER TABLE %s ADD i_ads BIGINT(20) NOT NULL DEFAULT 0 AFTER i_amount', ModelPayment::newInstance()->getTable_user()));
                $sql = "CREATE TABLE  /*TABLE_PREFIX*/t_payments_packs (
                    pk_i_id INT NOT NULL AUTO_INCREMENT ,
        b_enabled BOOLEAN NOT NULL DEFAULT TRUE,
        b_premium BOOLEAN NOT NULL DEFAULT TRUE,
        s_name VARCHAR( 200 ) NOT NULL ,
        s_title VARCHAR( 200 ) NOT NULL ,
        i_days INT(10) NULL,
        i_pictures INT(10) NULL,
        i_ads INT(10) NULL,
        i_expiration INT(10) NULL,
        dt_expiration DATETIME NOT NULL ,
        i_price BIGINT(20) NULL,

        PRIMARY KEY(pk_i_id)
    ) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';";
                $this->dao->importSQL($sql);
                osc_set_preference('version', '300', 'payment', 'INTEGER');
                osc_reset_preferences();
            }

        }

        public function searchPacks($start, $limit) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_packs());
            $this->dao->limit($limit, $start);
            $result = $this->dao->get();
            if($result) {
                return $result->result();
            }
            return false;
        }

        public function listPacks() {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_packs());
            $this->dao->where('b_enabled', 1);
            $result = $this->dao->get();
            if($result) {
                return $result->result();
            }
            return false;
        }

        public function getPaymentByCode($code, $source) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_log());
            $this->dao->where('s_code', $code);
            $this->dao->where('s_source', $source);
            $result = $this->dao->get();
            if($result) {
                return $result->row();
            }
            return false;
        }

        public function getPayment($paymentId) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_log());
            $this->dao->where('pk_i_id', $paymentId);
            $result = $this->dao->get();
            if($result) {
                return $result->row();
            }
            return false;
        }

        public function getPublishData($itemId) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_publish());
            $this->dao->where('fk_i_item_id', $itemId);
            $result = $this->dao->get();
            if($result) {
                return $result->row();
            }
            return false;
        }

        public function getPremiumData($itemId) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_premium());
            $this->dao->where('fk_i_item_id', $itemId);
            $result = $this->dao->get();
            if($result) {
                return $result->row();
            }
            return false;
        }

        public function createItem($itemId, $paid = 0, $date = NULL, $paypal = NULL) {
            if($date==NULL) { $date = date("Y-m-d H:i:s"); };
            $this->dao->insert($this->getTable_publish(), array('fk_i_item_id' => $itemId, 'dt_date' => $date, 'b_paid' => $paid, 'fk_i_payment_id' => $paypal));
        }

        public function getPublishPrice($categoryId) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_prices());
            $this->dao->where('fk_i_category_id', $categoryId);
            $result = $this->dao->get();
            if($result) {
                $cat = $result->row();
                if(isset($cat['f_publish_cost'])) {
                    return $cat["f_publish_cost"];
                }
            }
            return osc_get_preference('default_publish_cost', 'payment');
        }

        public function getPremiumPrice($categoryId) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_prices());
            $this->dao->where('fk_i_category_id', $categoryId);
            $result = $this->dao->get();
            if($result) {
                $cat = $result->row();
                if(isset($cat['f_premium_cost'])) {
                    return $cat["f_premium_cost"];
                }
            }
            return osc_get_preference('default_premium_cost', 'payment');
        }

        public function getUser($userId) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_user());
            $this->dao->where('fk_i_user_id', $userId);
            $result = $this->dao->get();
            if($result) {
                $row = $result->row();
                $row['formatted_amount'] = (isset($row['i_amount'])?$row['i_amount']:0)/1000000000000;
                return $row;
            }
            return false;
        }

        public function getPack($id) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_packs());
            $this->dao->where('pk_i_id', $id);
            $result = $this->dao->get();
            if($result) {
                return $result->row();
            }
            return false;
        }

        public function getCategoriesPrices() {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_prices());
            $result = $this->dao->get();
            if($result) {
                return $result->result();
            }
            return array();
        }

        public function publishFeeIsPaid($itemId) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_publish());
            $this->dao->where('fk_i_item_id', $itemId);
            $result = $this->dao->get();
            $row = $result->row();
            if($row) {
                if($row['b_paid']==1) {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        }

        public function premiumFeeIsPaid($itemId) {
            $this->dao->select('*') ;
            $this->dao->from($this->getTable_premium());
            $this->dao->where('fk_i_item_id', $itemId);
            $this->dao->where(sprintf("TIMESTAMPDIFF(DAY,dt_date,'%s') < %d", date('Y-m-d H:i:s'), osc_get_preference("premium_days", "payment")));
            $result = $this->dao->get();
            $row = $result->row();
            if(isset($row['dt_date'])) {
                return true;
            }
            return false;
        }


        public function purgeExpired() {
            $this->dao->select("fk_i_item_id");
            $this->dao->from($this->getTable_premium());
            $this->dao->where(sprintf("TIMESTAMPDIFF(DAY,dt_date,'%s') >= %d", date('Y-m-d H:i:s'), osc_get_preference("premium_days", "payment")));
            $result = $this->dao->get();
            if($result) {
                $items = $result->result();
                $mItem = new ItemActions(false);
                foreach($items as $item) {
                    $mItem->premium($item['fk_i_item_id'], false);
                    $this->premiumOff($item['fk_i_item_id']);
                };
            };
        }


        /**
         * Create a record on the DB for the paypal transaction
         *
         * @param string $concept
         * @param string $code
         * @param float $amount
         * @param string $currency
         * @param string $email
         * @param integer $user
         * @param integer $item
         * @param string $product_type (publish fee, premium, pack and which category)
         * @param string $source
         * @return integer $last_id
         */
        public function saveLog($concept, $code, $amount, $currency, $email, $user, $item, $product_type, $source) {
            $this->dao->insert($this->getTable_log(), array(
                's_concept' => $concept,
                'dt_date' => date("Y-m-d H:i:s"),
                's_code' => $code,
                'i_amount' => $amount*1000000000000,
                's_currency_code' => $currency,
                's_email' => $email,
                'fk_i_user_id' => $user,
                'fk_i_item_id' => $item,
                'i_product_type' => $product_type,
                's_source' => $source
                ));
            return $this->dao->insertedId();
        }

        public function updateUserPack($user, $pack) {
            $wallet = $this->getUser($user);
            if(isset($wallet['i_amount'])) {
                return $this->dao->update($this->getTable_user(), array('fk_i_pack_id' => $pack), array('fk_i_user_id' => $user));
            } else {
                return $this->dao->insert($this->getTable_user(), array('fk_i_user_id' => $user, 'i_amount' => 0, 'fk_i_pack_id' => $pack));
            }
        }

        public function insertPrice($category, $publish_fee, $premium_fee) {
            return $this->dao->replace($this->getTable_prices(), array('fk_i_category_id' => $category, 'f_publish_cost' => $publish_fee, 'f_premium_cost' => $premium_fee));
        }

        public function insertPack($enabled, $short_name, $title, $days, $pictures, $ads, $premium, $expiration, $price) {
            return $this->dao->insert($this->getTable_packs(), array(
                'b_enabled' => $enabled,
                'b_premium' => $premium,
                's_name' => $short_name,
                's_title' => $title,
                'i_days' => $days,
                'i_pictures' => $pictures,
                'i_ads' => $ads,
                'i_expiration' => 0, //$expiration,
                'dt_expiration' => 0, //$expiration,
                'i_price' => $price
            ));
        }

        public function updatePack($id, $enabled, $short_name, $title, $days, $pictures, $ads, $premium, $expiration, $price) {
            return $this->dao->update(
                $this->getTable_packs(),
                array(
                    'b_enabled' => $enabled,
                    'b_premium' => $premium,
                    's_name' => $short_name,
                    's_title' => $title,
                    'i_days' => $days,
                    'i_pictures' => $pictures,
                    'i_ads' => $ads,
                    'i_expiration' => 0, //$expiration,
                    'dt_expiration' => 0, //$expiration,
                    'i_price' => $price
                ),
                array(
                    'pk_i_id' => $id
                )
            );
        }

        public function statusPack($id, $status) {
            return $this->dao->update($this->getTable_packs(), array('b_enabled' => $status), array('pk_i_id' => $id));
        }

        public function deletePack($id) {
            return $this->dao->delete($this->getTable_packs(), array('pk_i_id' => $id));
        }

        public function payPublishFee($itemId, $paymentId) {
            $paid = $this->getPublishData($itemId);
            if(empty($paid)) {
                $this->createItem($itemId, 1, date("Y-m-d H:i:s"), $paymentId);
            } else {
                $this->dao->update($this->getTable_publish(), array('b_paid' => 1, 'dt_date' => date("Y-m-d H:i:s"), 'fk_i_payment_id' => $paymentId), array('fk_i_item_id' => $itemId));
            }
            $mItems = new ItemActions(false);
            return $mItems->enable($itemId);
        }

        public function payPremiumFee($itemId, $paymentId) {
            $paid = $this->getPremiumData($itemId);
            if(empty($paid)) {
                $this->dao->insert($this->getTable_premium(), array('dt_date' => date("Y-m-d H:i:s"), 'fk_i_payment_id' => $paymentId, 'fk_i_item_id' => $itemId));
            } else {
                $this->dao->update($this->getTable_premium(), array('dt_date' => date("Y-m-d H:i:s"), 'fk_i_payment_id' => $paymentId), array('fk_i_item_id' => $itemId));
            }
            $mItem = new ItemActions(false);
            return $mItem->premium($itemId, true);
        }

        public function addWallet($user, $amount) {
            $amount = (int)($amount*1000000000000);
            $wallet = $this->getUser($user);
            if(isset($wallet['i_amount'])) {
                return $this->dao->update($this->getTable_user(), array('i_amount' => $amount+$wallet['i_amount']), array('fk_i_user_id' => $user));
            } else {
                return $this->dao->insert($this->getTable_user(), array('fk_i_user_id' => $user, 'i_amount' => $amount));
            }

        }

    }

?>