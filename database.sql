SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `accounts` (
  `guid` varchar(32) NOT NULL,
  `name` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `account_type` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `commodity_guid` varchar(32) DEFAULT NULL,
  `commodity_scu` int(11) NOT NULL,
  `non_std_scu` int(11) NOT NULL,
  `parent_guid` varchar(32) DEFAULT NULL,
  `code` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `description` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `hidden` int(11) DEFAULT NULL,
  `placeholder` int(11) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `billterms` (
  `guid` varchar(32) NOT NULL,
  `name` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `description` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `refcount` int(11) NOT NULL,
  `invisible` int(11) NOT NULL,
  `parent` varchar(32) DEFAULT NULL,
  `type` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `duedays` int(11) DEFAULT NULL,
  `discountdays` int(11) DEFAULT NULL,
  `discount_num` bigint(20) DEFAULT NULL,
  `discount_denom` bigint(20) DEFAULT NULL,
  `cutoff` int(11) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `books` (
  `guid` varchar(32) NOT NULL,
  `root_account_guid` varchar(32) NOT NULL,
  `root_template_guid` varchar(32) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `budgets` (
  `guid` varchar(32) NOT NULL,
  `name` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `description` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `num_periods` int(11) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `budget_amounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `budget_guid` varchar(32) NOT NULL,
  `account_guid` varchar(32) NOT NULL,
  `period_num` int(11) NOT NULL,
  `amount_num` bigint(20) NOT NULL,
  `amount_denom` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `commodities` (
  `guid` varchar(32) NOT NULL,
  `namespace` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `mnemonic` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `fullname` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `cusip` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `fraction` int(11) NOT NULL,
  `quote_flag` int(11) NOT NULL,
  `quote_source` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `quote_tz` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `customers` (
  `guid` varchar(32) NOT NULL,
  `name` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `id` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `notes` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `active` int(11) NOT NULL,
  `discount_num` bigint(20) NOT NULL,
  `discount_denom` bigint(20) NOT NULL,
  `credit_num` bigint(20) NOT NULL,
  `credit_denom` bigint(20) NOT NULL,
  `currency` varchar(32) NOT NULL,
  `tax_override` int(11) NOT NULL,
  `addr_name` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr1` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr2` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr3` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr4` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_phone` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `addr_fax` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `addr_email` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `shipaddr_name` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `shipaddr_addr1` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `shipaddr_addr2` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `shipaddr_addr3` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `shipaddr_addr4` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `shipaddr_phone` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `shipaddr_fax` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `shipaddr_email` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `terms` varchar(32) DEFAULT NULL,
  `tax_included` int(11) DEFAULT NULL,
  `taxtable` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `employees` (
  `guid` varchar(32) NOT NULL,
  `username` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `id` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `language` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `acl` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `active` int(11) NOT NULL,
  `currency` varchar(32) NOT NULL,
  `ccard_guid` varchar(32) DEFAULT NULL,
  `workday_num` bigint(20) NOT NULL,
  `workday_denom` bigint(20) NOT NULL,
  `rate_num` bigint(20) NOT NULL,
  `rate_denom` bigint(20) NOT NULL,
  `addr_name` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr1` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr2` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr3` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr4` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_phone` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `addr_fax` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `addr_email` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `entries` (
  `guid` varchar(32) NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_entered` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `description` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `action` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `notes` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `quantity_num` bigint(20) DEFAULT NULL,
  `quantity_denom` bigint(20) DEFAULT NULL,
  `i_acct` varchar(32) DEFAULT NULL,
  `i_price_num` bigint(20) DEFAULT NULL,
  `i_price_denom` bigint(20) DEFAULT NULL,
  `i_discount_num` bigint(20) DEFAULT NULL,
  `i_discount_denom` bigint(20) DEFAULT NULL,
  `invoice` varchar(32) DEFAULT NULL,
  `i_disc_type` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `i_disc_how` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `i_taxable` int(11) DEFAULT NULL,
  `i_taxincluded` int(11) DEFAULT NULL,
  `i_taxtable` varchar(32) DEFAULT NULL,
  `b_acct` varchar(32) DEFAULT NULL,
  `b_price_num` bigint(20) DEFAULT NULL,
  `b_price_denom` bigint(20) DEFAULT NULL,
  `bill` varchar(32) DEFAULT NULL,
  `b_taxable` int(11) DEFAULT NULL,
  `b_taxincluded` int(11) DEFAULT NULL,
  `b_taxtable` varchar(32) DEFAULT NULL,
  `b_paytype` int(11) DEFAULT NULL,
  `billable` int(11) DEFAULT NULL,
  `billto_type` int(11) DEFAULT NULL,
  `billto_guid` varchar(32) DEFAULT NULL,
  `order_guid` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gnclock` (
  `Hostname` varchar(255) DEFAULT NULL,
  `PID` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `invoices` (
  `guid` varchar(32) NOT NULL,
  `id` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `date_opened` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `date_posted` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `notes` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `active` int(11) NOT NULL,
  `currency` varchar(32) NOT NULL,
  `owner_type` int(11) DEFAULT NULL,
  `owner_guid` varchar(32) DEFAULT NULL,
  `terms` varchar(32) DEFAULT NULL,
  `billing_id` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `post_txn` varchar(32) DEFAULT NULL,
  `post_lot` varchar(32) DEFAULT NULL,
  `post_acc` varchar(32) DEFAULT NULL,
  `billto_type` int(11) DEFAULT NULL,
  `billto_guid` varchar(32) DEFAULT NULL,
  `charge_amt_num` bigint(20) DEFAULT NULL,
  `charge_amt_denom` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jobs` (
  `guid` varchar(32) NOT NULL,
  `id` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `name` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `reference` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `active` int(11) NOT NULL,
  `owner_type` int(11) DEFAULT NULL,
  `owner_guid` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lots` (
  `guid` varchar(32) NOT NULL,
  `account_guid` varchar(32) DEFAULT NULL,
  `is_closed` int(11) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `orders` (
  `guid` varchar(32) NOT NULL,
  `id` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `notes` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `reference` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `active` int(11) NOT NULL,
  `date_opened` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_closed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `owner_type` int(11) NOT NULL,
  `owner_guid` varchar(32) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `prices` (
  `guid` varchar(32) NOT NULL,
  `commodity_guid` varchar(32) NOT NULL,
  `currency_guid` varchar(32) NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `source` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `type` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `value_num` bigint(20) NOT NULL,
  `value_denom` bigint(20) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `recurrences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `obj_guid` varchar(32) NOT NULL,
  `recurrence_mult` int(11) NOT NULL,
  `recurrence_period_type` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `recurrence_period_start` date NOT NULL,
  `recurrence_weekend_adjust` varchar(2048) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `schedxactions` (
  `guid` varchar(32) NOT NULL,
  `name` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `enabled` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `last_occur` date DEFAULT NULL,
  `num_occur` int(11) NOT NULL,
  `rem_occur` int(11) NOT NULL,
  `auto_create` int(11) NOT NULL,
  `auto_notify` int(11) NOT NULL,
  `adv_creation` int(11) NOT NULL,
  `adv_notify` int(11) NOT NULL,
  `instance_count` int(11) NOT NULL,
  `template_act_guid` varchar(32) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `obj_guid` varchar(32) NOT NULL,
  `name` varchar(4096) CHARACTER SET utf8 NOT NULL,
  `slot_type` int(11) NOT NULL,
  `int64_val` bigint(20) DEFAULT NULL,
  `string_val` varchar(4096) CHARACTER SET utf8 DEFAULT NULL,
  `double_val` double DEFAULT NULL,
  `timespec_val` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `guid_val` varchar(32) DEFAULT NULL,
  `numeric_val_num` bigint(20) DEFAULT NULL,
  `numeric_val_denom` bigint(20) DEFAULT NULL,
  `gdate_val` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slots_guid_index` (`obj_guid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4255 ;

CREATE TABLE IF NOT EXISTS `splits` (
  `guid` varchar(32) NOT NULL,
  `tx_guid` varchar(32) NOT NULL,
  `account_guid` varchar(32) NOT NULL,
  `memo` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `action` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `reconcile_state` varchar(1) CHARACTER SET utf8 NOT NULL,
  `reconcile_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `value_num` bigint(20) NOT NULL,
  `value_denom` bigint(20) NOT NULL,
  `quantity_num` bigint(20) NOT NULL,
  `quantity_denom` bigint(20) NOT NULL,
  `lot_guid` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`guid`),
  KEY `splits_tx_guid_index` (`tx_guid`),
  KEY `splits_account_guid_index` (`account_guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `taxtables` (
  `guid` varchar(32) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `refcount` bigint(20) NOT NULL,
  `invisible` int(11) NOT NULL,
  `parent` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `taxtable_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `taxtable` varchar(32) NOT NULL,
  `account` varchar(32) NOT NULL,
  `amount_num` bigint(20) NOT NULL,
  `amount_denom` bigint(20) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `transactions` (
  `guid` varchar(32) NOT NULL,
  `currency_guid` varchar(32) NOT NULL,
  `num` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `post_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `enter_date` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `description` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`guid`),
  KEY `tx_post_date_index` (`post_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `validations` (
  `guid_tx` varchar(32) NOT NULL,
  `username` varchar(35) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`guid_tx`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vendors` (
  `guid` varchar(32) NOT NULL,
  `name` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `id` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `notes` varchar(2048) CHARACTER SET utf8 NOT NULL,
  `currency` varchar(32) NOT NULL,
  `active` int(11) NOT NULL,
  `tax_override` int(11) NOT NULL,
  `addr_name` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr1` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr2` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr3` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_addr4` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `addr_phone` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `addr_fax` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `addr_email` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `terms` varchar(32) DEFAULT NULL,
  `tax_inc` varchar(2048) CHARACTER SET utf8 DEFAULT NULL,
  `tax_table` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `versions` (
  `table_name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `table_version` int(11) NOT NULL,
  PRIMARY KEY (`table_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `commodities` (`guid`, `namespace`, `mnemonic`, `fullname`, `cusip`, `fraction`, `quote_flag`, `quote_source`, `quote_tz`) VALUES
('3f675b5d80c66b98a918a76f74878945', 'CURRENCY', 'EUR', 'Euro', '978', 100, 1, 'currency', '');

INSERT INTO `accounts` (`guid`, `name`, `account_type`, `commodity_guid`, `commodity_scu`, `non_std_scu`, `parent_guid`, `code`, `description`, `hidden`, `placeholder`) VALUES
('42b7589674ffda84291e7ef048275480', 'Root Account', 'ROOT', '3f675b5d80c66b98a918a76f74878945', 100, 0, NULL, '', '', 0, 0),
('26608e50d05429ad798e286b8b71201a', 'Finanzbuchhaltung', 'ROOT', '3f675b5d80c66b98a918a76f74878945', 100, 0, '42b7589674ffda84291e7ef048275480', 'F', '', 0, 1),
('3e88f28388aab619a9fd6d4f9264758b', 'Kreditoren', 'PAYABLE', '3f675b5d80c66b98a918a76f74878945', 100, 0, '42b7589674ffda84291e7ef048275480', 'K', '', 0, 0),
('6af64b6de64658e846da4e23fc06bf42', 'Eigenkapital', 'ROOT', '3f675b5d80c66b98a918a76f74878945', 100, 0, '42b7589674ffda84291e7ef048275480', 'E', '', 0, 0),
('76c53ef584c5fc41884db195e73cca7e', 'Kostenrechnung', 'ROOT', '3f675b5d80c66b98a918a76f74878945', 100, 0, '42b7589674ffda84291e7ef048275480', 'R', '', 0, 0),
('98d2d35a07fbf3038f3d57c96f4c7d22', 'Debitoren', 'RECEIVABLE', '3f675b5d80c66b98a918a76f74878945', 100, 0, '42b7589674ffda84291e7ef048275480', 'D', '', 0, 0),
('cd483f40d08093dd11d8148f7f407a1d', 'Template Root', 'ROOT', NULL, 0, 0, NULL, '', '', 0, 0);

INSERT INTO `books` (`guid`, `root_account_guid`, `root_template_guid`) VALUES
('c125308464c064c827137ae833c302d9', '42b7589674ffda84291e7ef048275480', 'cd483f40d08093dd11d8148f7f407a1d');

INSERT INTO `versions` (`table_name`, `table_version`) VALUES
('Gnucash', 2060200),
('Gnucash-Resave', 19920),
('accounts', 1),
('books', 1),
('budgets', 1),
('budget_amounts', 1),
('commodities', 1),
('lots', 2),
('prices', 2),
('schedxactions', 1),
('transactions', 3),
('splits', 4),
('billterms', 2),
('customers', 2),
('employees', 2),
('entries', 3),
('invoices', 3),
('jobs', 1),
('orders', 1),
('taxtables', 2),
('taxtable_entries', 3),
('vendors', 1),
('recurrences', 2),
('slots', 3);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
