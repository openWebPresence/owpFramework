/*
Navicat MySQL Data Transfer

Source Server         : MYSQL - VM .50 Web Dev
Source Server Version : 50717
Source Host           : 192.168.25.50:3306
Source Database       : 144_com

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2017-03-02 15:13:05
*/

SET FOREIGN_KEY_CHECKS=0;

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
