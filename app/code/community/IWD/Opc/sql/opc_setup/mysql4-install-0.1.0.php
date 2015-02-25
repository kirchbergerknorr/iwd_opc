<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('opc/customer')}`(
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `payer_id` VARCHAR(255) NOT NULL DEFAULT '',
    `customer_id` INT(10) UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY `IDX_UNIQUE_PAYER_ID` (`payer_id`),
    UNIQUE KEY `IDX_UNIQUE_CUSTOMER_ID` (`customer_id`),
    KEY `FK_CUSTOMER_PAYPAL_CUSTOMER_ID` (`customer_id`),
    CONSTRAINT `FK_CUSTOMER_PAYPAL_CUSTOMER_ID` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();