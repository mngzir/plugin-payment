CREATE TABLE  /*TABLE_PREFIX*/t_payments_log (
    pk_i_id INT NOT NULL AUTO_INCREMENT ,
    s_concept VARCHAR( 200 ) NOT NULL ,
    dt_date DATETIME NOT NULL ,
    s_code VARCHAR( 255 ) NOT NULL ,
    f_amount FLOAT NOT NULL ,
    i_amount BIGINT(20) NULL,
    s_currency_code VARCHAR( 3 ) NULL ,
    s_email VARCHAR( 200 ) NULL ,
    fk_i_user_id INT NULL ,
    fk_i_item_id INT NULL ,
    s_source VARCHAR( 10 ) NOT NULL,
    i_product_type VARCHAR( 15 ) NOT NULL,

    PRIMARY KEY(pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE  /*TABLE_PREFIX*/t_payments_packs (
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
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE /*TABLE_PREFIX*/t_payments_user (
    fk_i_user_id INT UNSIGNED NOT NULL,
    fk_i_pack_id INT UNSIGNED NULL,
    i_amount BIGINT(20) NULL,
    i_ads BIGINT(20) NULL,

        PRIMARY KEY (fk_i_user_id),
        FOREIGN KEY (fk_i_user_id) REFERENCES /*TABLE_PREFIX*/t_user (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE /*TABLE_PREFIX*/t_payments_premium (
    fk_i_item_id INT UNSIGNED NOT NULL,
    dt_date DATETIME NOT NULL ,
    s_keyword VARCHAR(250) NULL ,
    fk_i_payment_id INT NOT NULL,

        PRIMARY KEY (fk_i_item_id),
        FOREIGN KEY (fk_i_item_id) REFERENCES /*TABLE_PREFIX*/t_item (pk_i_id),
        FOREIGN KEY (fk_i_payment_id) REFERENCES /*TABLE_PREFIX*/t_payments_log (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE /*TABLE_PREFIX*/t_payments_publish (
    fk_i_item_id INT UNSIGNED NOT NULL,
    dt_date DATETIME NOT NULL ,
    b_paid BOOLEAN NOT NULL DEFAULT FALSE,
    s_keyword VARCHAR(250) NULL ,
    fk_i_payment_id INT NULL,

        PRIMARY KEY (fk_i_item_id),
        FOREIGN KEY (fk_i_item_id) REFERENCES /*TABLE_PREFIX*/t_item (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE /*TABLE_PREFIX*/t_payments_prices (
    fk_i_category_id INT UNSIGNED NOT NULL,
    f_publish_cost FLOAT NULL ,
    f_premium_cost FLOAT NULL ,

        PRIMARY KEY (fk_i_category_id),
        FOREIGN KEY (fk_i_category_id) REFERENCES /*TABLE_PREFIX*/t_category (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';