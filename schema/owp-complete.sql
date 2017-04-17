/*
Navicat MySQL Data Transfer

Source Server         : MYSQL - VM .50 Web Dev
Source Server Version : 50717
Source Host           : 192.168.25.50:3306
Source Database       : owp_users

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2017-03-21 16:00:23
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `tbl_content`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_content`;
CREATE TABLE `tbl_content` (
  `content_name` varchar(100) NOT NULL,
  `content_value` longtext NOT NULL,
  `content_last_updated` date NOT NULL,
  `content_last_updated_by_userID` int(15) NOT NULL,
  `content_title` varchar(500) NOT NULL,
  PRIMARY KEY (`content_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_content
-- ----------------------------
INSERT INTO `tbl_content` VALUES ('NewUserRegistrationMessageBody', '\"Welcome to OWP {{first_name}} {{last_name}}! Click on the following link to validate your account to continue. {{validation_link}}\"', '2017-03-01', '0', 'New User Registration Body');

-- ----------------------------
-- Table structure for `tbl_settings`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_settings`;
CREATE TABLE `tbl_settings` (
  `setting_name` varchar(100) NOT NULL,
  `setting_value` longtext NOT NULL,
  `setting_last_updated` date NOT NULL,
  `setting_last_updated_by_userID` int(15) NOT NULL,
  PRIMARY KEY (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_settings
-- ----------------------------

-- ----------------------------
-- Table structure for `tbl_users`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE `tbl_users` (
  `userID` int(15) unsigned NOT NULL AUTO_INCREMENT,
  `passwd` varchar(200) NOT NULL,
  `user_created_datetime` datetime NOT NULL,
  `user_updated_datetime` datetime NOT NULL,
  `user_last_login_datetime` datetime NOT NULL,
  `login_count` int(10) NOT NULL DEFAULT '0',
  `statusID` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(200) NOT NULL,
  `first_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `uuid` varchar(35) DEFAULT NULL,
  `welcome_email_sent` tinyint(1) NOT NULL,
  `reset_pass_uuid` varchar(35) DEFAULT NULL,
  `user_ip` varchar(20) DEFAULT NULL,
  `rememberme_hash` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email` (`email`) USING BTREE,
  KEY `uuid` (`uuid`) USING BTREE,
  KEY `reset_pass_uuid` (`reset_pass_uuid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_users
-- ----------------------------
INSERT INTO `tbl_users` VALUES ('1', '$2a$08$AjMKPO9lS3VOcuFJPgrdUuLltnEZO1d8/QKJJxD4c44NQG782qA3C', '2017-01-01 01:00:00', '2017-01-01 01:00:00', '2017-01-01 01:00:00', '0', '10', 'system@system', 'System', 'User', null, '0', null, null, null);

-- ----------------------------
-- Table structure for `tbl_users_meta_data`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_users_meta_data`;
CREATE TABLE `tbl_users_meta_data` (
  `userID` int(15) unsigned NOT NULL,
  `key_name` varchar(50) NOT NULL,
  `key_value` varchar(500) DEFAULT NULL,
  `updated_ts` datetime NOT NULL,
  PRIMARY KEY (`userID`,`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_users_meta_data
-- ----------------------------

-- ----------------------------
-- Table structure for `tbl_users_rights`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_users_rights`;
CREATE TABLE `tbl_users_rights` (
  `userID` int(15) unsigned NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `hide_ads` tinyint(1) NOT NULL DEFAULT '0',
  `is_dev` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_users_rights
-- ----------------------------

-- ----------------------------
-- Table structure for `tbl_users_status`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_users_status`;
CREATE TABLE `tbl_users_status` (
  `statusID` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `status_label` varchar(100) NOT NULL,
  `canLogin` tinyint(1) NOT NULL DEFAULT '0',
  `triggerCustomMethod` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`statusID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_users_status
-- ----------------------------
INSERT INTO `tbl_users_status` VALUES ('1', 'is Pending Verification', '0', null);
INSERT INTO `tbl_users_status` VALUES ('2', 'has been Verified', '1', null);
INSERT INTO `tbl_users_status` VALUES ('4', 'is Password Lost status', '0', null);
INSERT INTO `tbl_users_status` VALUES ('9', 'has been Closed by the Administration.', '0', null);
INSERT INTO `tbl_users_status` VALUES ('3', 'is on hold because of a Bounced Verification Email', '0', null);
INSERT INTO `tbl_users_status` VALUES ('10', 'System User', '0', null);
