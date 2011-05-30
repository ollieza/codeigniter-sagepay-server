SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Table structure for table `sagepay_payments`
--

DROP TABLE IF EXISTS `sagepay_payments`;
CREATE TABLE IF NOT EXISTS `sagepay_payments` (
  `VendorTxCode` varchar(50) NOT NULL,
  `TxType` varchar(32) NOT NULL default '',
  `Amount` decimal(10,2) NOT NULL,
  `Currency` varchar(3) NOT NULL default '',
  `BillingFirstnames` varchar(20) default NULL,
  `BillingSurname` varchar(20) default NULL,
  `BillingAddress1` varchar(100) default NULL,
  `BillingAddress2` varchar(100) default NULL,
  `BillingCity` varchar(40) default NULL,
  `BillingPostCode` varchar(10) default NULL,
  `BillingCountry` varchar(2) default NULL,
  `BillingState` varchar(2) default NULL,
  `BillingPhone` varchar(20) default NULL,
  `DeliveryFirstnames` varchar(20) default NULL,
  `DeliverySurname` varchar(20) default NULL,
  `DeliveryAddress1` varchar(100) default NULL,
  `DeliveryAddress2` varchar(100) default NULL,
  `DeliveryCity` varchar(40) default NULL,
  `DeliveryPostCode` varchar(10) default NULL,
  `DeliveryCountry` varchar(2) default NULL,
  `DeliveryState` varchar(2) default NULL,
  `DeliveryPhone` varchar(20) default NULL,
  `CustomerEMail` varchar(255) default NULL,
  `VPSTxId` varchar(64) default NULL,
  `SecurityKey` varchar(10) default NULL,
  `TxAuthNo` bigint(20) NOT NULL default '0',
  `AVSCV2` varchar(50) default NULL,
  `AddressResult` varchar(20) default NULL,
  `PostCodeResult` varchar(20) default NULL,
  `CV2Result` varchar(20) default NULL,
  `GiftAid` tinyint(4) default NULL,
  `ThreeDSecureStatus` varchar(50) default NULL,
  `CAVV` varchar(40) default NULL,
  `RelatedVendorTxCode` varchar(50) default NULL,
  `Status` varchar(255) default NULL,
  `AddressStatus` varchar(20) default NULL,
  `PayerStatus` varchar(20) default NULL,
  `CardType` varchar(15) default NULL,
  `Last4Digits` varchar(4) default NULL,
  `LastUpdated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`VendorTxCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
