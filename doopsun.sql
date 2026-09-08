-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.7.26 - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win64
-- HeidiSQL 版本:                  12.21.0.7344
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- 导出 doopsun 的数据库结构
CREATE DATABASE IF NOT EXISTS `doopsun` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `doopsun`;

-- 导出  表 doopsun.doopsun_ad 结构
CREATE TABLE IF NOT EXISTS `doopsun_ad` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告ID，主键',
  `title` varchar(255) NOT NULL COMMENT '广告标题',
  `description` varchar(1000) DEFAULT NULL COMMENT '广告描述',
  `sort_order` int(10) unsigned DEFAULT '0' COMMENT '排序值，数字越大越靠前',
  `ad_link` varchar(500) NOT NULL COMMENT '广告链接',
  `banner_url` varchar(255) NOT NULL COMMENT 'Banner图片路径',
  `ad_type` tinyint(3) unsigned DEFAULT '1' COMMENT '广告类型：1-新闻列表广告',
  `start_time` datetime DEFAULT NULL COMMENT '广告开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '广告结束时间',
  `status` tinyint(3) unsigned DEFAULT '1' COMMENT '状态：1-启用, 0-禁用',
  `click_count` int(10) unsigned DEFAULT '0' COMMENT '点击量',
  `view_count` int(10) unsigned DEFAULT '0' COMMENT '展示量',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_sort_order` (`sort_order`) USING BTREE,
  KEY `idx_time_range` (`start_time`,`end_time`) USING BTREE,
  KEY `idx_ad_type` (`ad_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='广告管理表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_agent 结构
CREATE TABLE IF NOT EXISTS `doopsun_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户号',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_api_notice 结构
CREATE TABLE IF NOT EXISTS `doopsun_api_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '商户用户id',
  `type` varchar(30) NOT NULL COMMENT '类型',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `done_time` int(10) DEFAULT NULL COMMENT '完成时间',
  `data` varchar(1000) DEFAULT NULL COMMENT '回调结果',
  `param` varchar(1000) DEFAULT NULL COMMENT '回调参数',
  `num` tinyint(3) DEFAULT '0' COMMENT '调用次数',
  `msg` varchar(255) DEFAULT '' COMMENT '失败原因',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `entityType` varchar(150) DEFAULT 'ENTERPRISE' COMMENT '客户主体类型',
  `businessType` varchar(150) DEFAULT 'B2B' COMMENT '业务类型',
  `contactPhone` varchar(50) DEFAULT NULL COMMENT '申请人联系电话',
  `contactEmail` varchar(150) DEFAULT NULL COMMENT '申请人联系邮箱',
  `contactIdType` varchar(50) DEFAULT NULL COMMENT '申请人证件类型',
  `contactIdNumber` varchar(150) DEFAULT NULL COMMENT '申请人证件号',
  `authorisedFillingAs` varchar(150) DEFAULT NULL COMMENT '申请人类型 1/2 1-法人代表/企业董事/实际控制人/股东（占股25%及以上）之一2-被授权人',
  `authType` varchar(50) DEFAULT NULL COMMENT '客户认证方式',
  `otherAuthResult` varchar(50) DEFAULT NULL COMMENT '其他认证结果',
  `applyFileId` varchar(255) DEFAULT NULL COMMENT '认证文件包ID',
  `powerOfAttorney` varchar(150) DEFAULT NULL COMMENT '企业授权书文件id',
  `name` varchar(255) DEFAULT NULL COMMENT '企业名称',
  `nameEn` varchar(255) DEFAULT NULL COMMENT '企业英文名称',
  `countryCode` varchar(50) DEFAULT NULL COMMENT '企业国家地区码',
  `companyType` varchar(50) DEFAULT NULL COMMENT '企业类型',
  `certificateNumber` varchar(50) DEFAULT NULL COMMENT '企业证件编号',
  `certificateType` varchar(50) DEFAULT NULL COMMENT '证件类型',
  `certificateFileId` varchar(150) DEFAULT NULL COMMENT '企业证件文件id',
  `establishedDate` varchar(50) DEFAULT NULL COMMENT '企业成立日期',
  `certificateExpireDate` varchar(50) DEFAULT NULL COMMENT '企业证书有效时间',
  `provinceCode` varchar(50) DEFAULT NULL COMMENT '省份编码',
  `cityCode` varchar(50) DEFAULT NULL COMMENT '城市编码',
  `registeredCapital` varchar(50) DEFAULT '0' COMMENT '注册资本',
  `actualBusinessAddress` varchar(255) DEFAULT NULL COMMENT '实际经营地址',
  `companyRegisterAddress` varchar(255) DEFAULT NULL COMMENT '公司注册地址',
  `businessCategory` varchar(255) DEFAULT NULL COMMENT '经营品类',
  `employeeNumber` varchar(50) DEFAULT NULL COMMENT '企业员工人数',
  `annualVolumePast` varchar(50) DEFAULT NULL COMMENT '历史年出口额(万美元)',
  `annualVolumeFuture` varchar(50) DEFAULT NULL COMMENT '预估年出口额(万美元)',
  `exportType` varchar(50) DEFAULT NULL COMMENT '出口类型',
  `exportCountry` varchar(50) DEFAULT NULL COMMENT '主要出口国家(多个国家用逗号分隔)',
  `persons` text COMMENT '企业关联个人信息列表（json多个个人）',
  `attachments` text COMMENT '企业关联附件信息列表（json多个文件）',
  `status` varchar(50) DEFAULT '1' COMMENT '账号状态：0正常，1待审核2禁用',
  `inTime` varchar(50) DEFAULT NULL COMMENT '加入时间',
  `signtime` varchar(50) DEFAULT NULL COMMENT '签约时间',
  `endtime` varchar(50) DEFAULT NULL COMMENT '到期时间',
  `updateTime` varchar(50) DEFAULT NULL COMMENT '最后修改信息时间',
  `abate_time` varchar(50) NOT NULL DEFAULT '0' COMMENT '失效时间',
  `thoroughfare` int(11) DEFAULT '1' COMMENT '通道',
  `adduserid` varchar(50) DEFAULT '0' COMMENT '添加人员id',
  `merchantId` int(11) DEFAULT NULL COMMENT '商户id',
  `merchantIdHf` int(11) DEFAULT NULL COMMENT '汇付商户id',
  `clientId` varchar(50) DEFAULT NULL COMMENT '客户号',
  `requestId` varchar(150) DEFAULT NULL,
  `auditRemark` varchar(255) DEFAULT NULL COMMENT '审核备注',
  `contactPhoneRegion` varchar(255) DEFAULT NULL COMMENT '联系电话区号',
  `isBizForever` varchar(255) DEFAULT NULL COMMENT '是否永久经营',
  `actualBusinessProvinceCode` varchar(255) DEFAULT NULL COMMENT '实际经营省份编码',
  `actualBusinessCityCode` varchar(255) DEFAULT NULL COMMENT '实际经营城市编码',
  `website` varchar(255) DEFAULT NULL COMMENT '公司网站',
  `importExportBusinessRight` varchar(255) DEFAULT NULL COMMENT '对外贸易进出口经营权',
  `businessEntityAttachments` varchar(255) DEFAULT NULL COMMENT '企业 - 其他材料补充',
  `createdTime` varchar(255) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_all 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_all` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `usertype` smallint(1) NOT NULL DEFAULT '1' COMMENT '1为直客 2为代理',
  `merchantId` int(11) DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  `requestId` varchar(100) DEFAULT NULL COMMENT 'requestId或者userNo',
  `channel` int(1) DEFAULT '0' COMMENT '通道：1汇付 2易宝 3宝付 ',
  `bizType` varchar(50) DEFAULT NULL COMMENT '业务类型 bizType',
  `productDesc` text COMMENT '产品描述 productDesc 去除不用了',
  `collectionAccount` varchar(50) DEFAULT NULL COMMENT '收款账户类型 collectionAccount',
  `collectionAccountCountry` varchar(10) DEFAULT NULL COMMENT '收款账户所在国家 collectionAccountCountry',
  `corpRegion` varchar(10) DEFAULT NULL COMMENT '注册地区 corpRegion (CN/HK/OTHER)',
  `corpBizRegion` varchar(10) DEFAULT NULL COMMENT '企业经营地区 corpBizRegion',
  `corpName` varchar(255) DEFAULT NULL COMMENT '企业名称 corpName',
  `corpNameEn` varchar(255) DEFAULT NULL COMMENT '企业英文名称 corpNameEn',
  `registeredCapital` varchar(100) DEFAULT NULL COMMENT '注册资本 registeredCapital',
  `corpType` varchar(50) DEFAULT NULL COMMENT '企业类型 corpType',
  `cnLicenseFile` varchar(255) DEFAULT NULL COMMENT '营业执照文件 cnLicenseFile (fileId/URL)',
  `cnLicenseNo` varchar(100) DEFAULT NULL COMMENT '营业执照号 cnLicenseNo',
  `cnLicenseStart` date DEFAULT NULL COMMENT '营业期限开始 cnLicenseStart',
  `cnLicenseEnd` date DEFAULT NULL COMMENT '营业期限结束 cnLicenseEnd',
  `hkCertFile` varchar(255) DEFAULT NULL COMMENT '公司注册证书 hkCertFile (fileId/URL)',
  `hkBrFile` varchar(255) DEFAULT NULL COMMENT '商业登记证 hkBrFile (fileId/URL)',
  `hkNnc1File` varchar(255) DEFAULT NULL COMMENT '法团成立表格 hkNnc1File (fileId/URL)',
  `hkArFile` varchar(255) DEFAULT NULL COMMENT '周年申报表 hkArFile (fileId/URL)',
  `hkBrNo` varchar(100) DEFAULT NULL COMMENT '商业登记号码 hkBrNo',
  `hkBrStart` date DEFAULT NULL COMMENT '商业登记证起止时间 hkBrStart',
  `hkBrEnd` date DEFAULT NULL COMMENT '商业登记证起止时间 hkBrEnd',
  `otherRegCertFile` varchar(255) DEFAULT NULL COMMENT '注册证书 otherRegCertFile (fileId/URL)',
  `otherRegCertNo` varchar(100) DEFAULT NULL COMMENT '注册证书编号 otherRegCertNo',
  `otherAccessories` varchar(255) DEFAULT NULL COMMENT '其他附件',
  `regProvince` varchar(100) DEFAULT NULL COMMENT '注册省 regProvince',
  `regCity` varchar(100) DEFAULT NULL COMMENT '注册市 regCity',
  `regAddressDetail` varchar(255) DEFAULT NULL COMMENT '注册详细地址 regAddressDetail',
  `regAddressDetailEn` varchar(255) DEFAULT NULL COMMENT '注册地址英文 regAddressDetailEn',
  `bizSame` char(1) DEFAULT 'N' COMMENT '营业地址同注册地址 bizSame (Y/N)',
  `bizProvince` varchar(100) DEFAULT NULL COMMENT '营业省 bizProvince',
  `bizCity` varchar(100) DEFAULT NULL COMMENT '营业市 bizCity',
  `bizAddressDetail` varchar(255) DEFAULT NULL COMMENT '营业详细地址 bizAddressDetail',
  `bizAddressDetailEn` varchar(255) DEFAULT NULL COMMENT '营业地址英文 bizAddressDetailEn',
  `contactName` varchar(100) DEFAULT NULL COMMENT '联系人 contactName',
  `contactAreaCode` varchar(10) DEFAULT NULL COMMENT '联系人区号 contactAreaCode',
  `contactPhone` varchar(50) DEFAULT NULL COMMENT '联系人电话 contactPhone',
  `employeeCount` varchar(50) DEFAULT NULL COMMENT '员工数量 employeeCount',
  `exportCountry` varchar(10) DEFAULT NULL COMMENT '出口国家 exportCountry',
  `website` varchar(255) DEFAULT NULL COMMENT '公司网站 website',
  `annualSalesLevel` varchar(50) DEFAULT NULL COMMENT '每年销售额等级 annualSalesLevel',
  `cnLegalIdType` varchar(20) DEFAULT NULL COMMENT '法人证件类型 cnLegalIdType',
  `cnLegalIdFileFront` varchar(255) DEFAULT NULL COMMENT '法人证件上传（正面） cnLegalIdFileFront (fileId/URL)',
  `cnLegalIdFileBack` varchar(255) DEFAULT NULL COMMENT '法人证件上传（反面） cnLegalIdFileBack (fileId/URL)',
  `cnLegalIdFileHandheld` varchar(255) DEFAULT NULL COMMENT '法人手持证件照上传 cnLegalIdFileHandheld (fileId/URL)',
  `cnLegalName` varchar(100) DEFAULT NULL COMMENT '法人姓名 cnLegalName',
  `cnLegalGender` varchar(10) DEFAULT NULL COMMENT '法人性别 cnLegalGender',
  `cnLegalNationality` varchar(10) DEFAULT NULL COMMENT '法人国籍 cnLegalNationality',
  `cnLegalIdNo` varchar(100) DEFAULT NULL COMMENT '法人证件号 cnLegalIdNo',
  `cnLegalDob` date DEFAULT NULL COMMENT '法人出生日期 cnLegalDob',
  `cnLegalIdStart` varchar(255) DEFAULT NULL COMMENT '法人证件有效开始日期',
  `cnLegalIdEnd` varchar(255) DEFAULT NULL COMMENT '法人证件有效结束日期',
  `cnLegalIdAddress` varchar(255) DEFAULT NULL,
  `cnLegalResProvince` varchar(100) DEFAULT NULL COMMENT '居住省 cnLegalResProvince',
  `cnLegalResCity` varchar(100) DEFAULT NULL COMMENT '居住市 cnLegalResCity',
  `cnLegalResAddress` varchar(255) DEFAULT NULL COMMENT '居住详细地址 cnLegalResAddress',
  `cnLegalIsBeneficiary` char(1) DEFAULT NULL COMMENT '是否为受益人 cnLegalIsBeneficiary (Y/N)',
  `cnLegalSharePercent` decimal(6,2) DEFAULT NULL COMMENT '占股比例(%) cnLegalSharePercent',
  `dirIdType` varchar(20) DEFAULT NULL COMMENT '董事证件类型 dirIdType',
  `dirIdFileFront` varchar(255) DEFAULT NULL COMMENT '董事证件上传（正面） dirIdFileFront (fileId/URL)',
  `dirIdFileBack` varchar(255) DEFAULT NULL COMMENT '董事证件上传（反面） dirIdFileBack (fileId/URL)',
  `dirIdFileHandheld` varchar(255) DEFAULT NULL COMMENT '董事手持证件照上传 dirIdFileHandheld (fileId/URL)',
  `dirName` varchar(100) DEFAULT NULL COMMENT '董事姓名 dirName',
  `dirGender` varchar(10) DEFAULT NULL COMMENT '董事性别 dirGender',
  `dirNationality` varchar(10) DEFAULT NULL COMMENT '董事国籍 dirNationality',
  `dirIdNo` varchar(100) DEFAULT NULL COMMENT '董事证件号 dirIdNo',
  `dirDob` date DEFAULT NULL COMMENT '董事出生日期 dirDob',
  `dirIdStart` date DEFAULT NULL COMMENT '董事证件有效开始日期',
  `dirIdEnd` date DEFAULT NULL COMMENT '董事证件有效结束日期',
  `dirIdAddress` varchar(258) DEFAULT NULL COMMENT '董事证件地址',
  `dirResProvince` varchar(100) DEFAULT NULL COMMENT '居住省 dirResProvince',
  `dirResCity` varchar(100) DEFAULT NULL COMMENT '居住市 dirResCity',
  `dirResAddress` varchar(255) DEFAULT NULL COMMENT '居住详细地址 dirResAddress',
  `dirIsBeneficiary` char(1) DEFAULT NULL COMMENT '是否为受益人 dirIsBeneficiary (Y/N)',
  `dirSharePercent` decimal(6,2) DEFAULT NULL COMMENT '占股比例(%) dirSharePercent',
  `otherLicenseStart` varchar(255) DEFAULT NULL COMMENT '其它国家营业期限开始时间',
  `otherLicenseEnd` varchar(255) DEFAULT NULL COMMENT '其它国家营业期限结束时间',
  `establishDate` date DEFAULT NULL COMMENT '成立日期\r\n',
  `industryCategory` varchar(255) DEFAULT NULL COMMENT '行业类别',
  `goodsServiceType` varchar(255) DEFAULT NULL COMMENT '货物/服务种类',
  `wealthSource` varchar(255) DEFAULT NULL COMMENT '财富来源',
  `expectedFundSource` varchar(255) DEFAULT NULL COMMENT '预计资金来源',
  `beneficiaries` text COMMENT '受益人信息列表 beneficiaries (JSON数组, 包含: beneficiaryIdType, beneficiaryIdFileFront, beneficiaryIdFileBack, beneficiaryName, beneficiaryGender, beneficiaryNationality, beneficiaryIdNo, beneficiaryDob, beneficiaryIdExpiry, beneficiaryResProvince, beneficiaryResCity, beneficiaryResDistrict, beneficiaryResAddress, beneficiaryShare)',
  `auditStatus` tinyint(1) DEFAULT '0' COMMENT '审核状态 0待审核 1预审通过 预审驳回 3需要补充材料 4审核通过 5审核不通过',
  `auditRemark` text COMMENT '审核备注 auditRemark',
  `channelCode` varchar(50) DEFAULT NULL COMMENT '通道选择 channelCode',
  `status` varchar(50) DEFAULT '0' COMMENT '业务状态 status',
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间 createdAt',
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间 updatedAt',
  `addressProve` text COMMENT '法人/董事地址文件、股东地址文件;多个文件逗号间隔',
  `otherResource` text COMMENT '易宝的补充资料数据',
  `exportType` varchar(150) DEFAULT NULL COMMENT '出口类型SELF_SUPPORT-自营出口|DELEGATION-委托出口|INTERNATIONAL_EXPRESS-国际快速|OTHER-其他;多个逗号间隔',
  `memberId` varchar(255) DEFAULT NULL COMMENT '通道返回的ID',
  `additionalMaterials` varchar(255) DEFAULT NULL COMMENT '补充材料：税务商业登记申请书或其他，多个以逗号分开',
  `bizDistrict` varchar(100) DEFAULT NULL COMMENT '营业区 bizDistrict',
  `cnLegalResDistrict` varchar(100) DEFAULT NULL COMMENT '居住区 cnLegalResDistrict',
  `dirIdExpiry` date DEFAULT NULL COMMENT '董事证件有效期 dirIdExpiry',
  `dirResDistrict` varchar(100) DEFAULT NULL COMMENT '居住区 dirResDistrict',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1客户自提 2管理提交',
  `companytype` varchar(255) DEFAULT NULL,
  `filename` varchar(254) DEFAULT NULL,
  `filepath` varchar(255) DEFAULT NULL,
  `filesize` varchar(255) DEFAULT NULL,
  `filetime` int(11) DEFAULT NULL,
  `filestatus` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 1提交成功 2提交失败',
  `callBackUrl` varchar(255) DEFAULT NULL,
  `rzcallBackUrl` varchar(255) DEFAULT NULL COMMENT '入账通知地址',
  `taxNo` varchar(64) DEFAULT NULL COMMENT '税号 ARG-CUIT / BRA-CNPJ / MEX-RFC',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_corpRegion` (`corpRegion`) USING BTREE,
  KEY `idx_corpName` (`corpName`) USING BTREE,
  KEY `idx_createdAt` (`createdAt`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='商户入网信息主表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_all_uploadfile 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_all_uploadfile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel` smallint(1) DEFAULT '0' COMMENT '通道',
  `merchantId` int(11) NOT NULL,
  `fileId` varchar(255) DEFAULT NULL COMMENT '宝付附件ID',
  `path` varchar(1000) DEFAULT NULL COMMENT '易宝path',
  `fileMd5encryption` varchar(100) DEFAULT NULL,
  `fileName` varchar(100) DEFAULT NULL,
  `userNo` varchar(100) DEFAULT NULL,
  `uploadUrl` varchar(100) DEFAULT NULL COMMENT '系统路径',
  `created_time` datetime DEFAULT NULL COMMENT '上传时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` smallint(1) DEFAULT NULL COMMENT '1成功 默认0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1308 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_balance 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) DEFAULT '0' COMMENT '账户id',
  `currency` varchar(150) DEFAULT NULL COMMENT '账户币种',
  `balance` varchar(150) DEFAULT NULL COMMENT '账户金额',
  `updateTime` varchar(150) DEFAULT NULL COMMENT '余额更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户账户余额表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_balancedetail 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_balancedetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) DEFAULT '0' COMMENT '账户id',
  `currency` varchar(150) DEFAULT NULL COMMENT '账户币种',
  `txnId` varchar(150) DEFAULT NULL COMMENT '交易id',
  `txnType` varchar(50) DEFAULT NULL COMMENT '交易类型',
  `txnTime` varchar(150) DEFAULT NULL COMMENT '交易时间',
  `amount` varchar(150) DEFAULT NULL COMMENT '变动金额',
  `cdtDbtInd` varchar(150) DEFAULT NULL COMMENT '变动金额方向：CREDIT – 出账DEBIT – 入账',
  `balance` varchar(150) DEFAULT NULL COMMENT '变动后账户余额',
  `updateTime` varchar(150) DEFAULT NULL COMMENT '余额更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户账户余额明细表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_beneficiary 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_beneficiary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) DEFAULT NULL COMMENT '客户号',
  `requestId` varchar(150) DEFAULT NULL COMMENT '请求id',
  `name` varchar(150) DEFAULT NULL COMMENT '收款方名称',
  `type` varchar(50) DEFAULT NULL COMMENT '收款方类型:(INDIVIDUAL - 个人,BUSINESS - 企业)',
  `country` varchar(50) DEFAULT NULL COMMENT '收款方国家',
  `idType` varchar(50) DEFAULT NULL COMMENT '收款方证件类型',
  `idNumber` varchar(150) DEFAULT NULL COMMENT '收款方证件号',
  `addressLine1` varchar(150) DEFAULT NULL COMMENT '收款方地址行1(海外必填)',
  `accountType` varchar(50) DEFAULT NULL COMMENT '账户类型(PERSONAL -个人账户,BUSINESS - 企业账户)',
  `accountName` varchar(150) DEFAULT NULL COMMENT '收款人银行账户名称',
  `accountCurrency` varchar(50) DEFAULT NULL COMMENT '账户币种',
  `accountNumber` varchar(150) DEFAULT NULL COMMENT '银行账号',
  `bankName` varchar(150) DEFAULT NULL COMMENT '银行名称(海外/大陆离岸银行请输入英文)',
  `bankCountry` varchar(150) DEFAULT NULL COMMENT '银行所在国家:2 位国家代码',
  `beneficiaryInfoType` varchar(150) DEFAULT NULL COMMENT '收款方信息类型:WD_CARD - 提现卡,SUPPLIER-供应商',
  `beneficiaryId` varchar(150) DEFAULT NULL COMMENT '收款人:仅创建成功并审核通过后返回,付款时可通过指定收款人 id 发起付款',
  `status` varchar(150) DEFAULT NULL COMMENT '状态:AUDITING – 审核中,APPROVED – 审核通过,REJECTED – 审核拒绝',
  `msg` varchar(255) DEFAULT NULL COMMENT '状态描述:审核拒绝时见描述',
  `bic` varchar(150) DEFAULT NULL COMMENT '银行识别号',
  `isPaymentForPobo` varchar(50) DEFAULT NULL COMMENT '已收款人名义付款Y/N',
  `bankAccountHolderType` varchar(50) DEFAULT NULL COMMENT '持卡人类型',
  `poboCertIdType` varchar(50) DEFAULT NULL COMMENT '以收款人名义付款证件类型',
  `poboCertFrontId` varchar(255) DEFAULT NULL COMMENT '以收款人名义付款-身份证人像页',
  `poboCertBackId` varchar(255) DEFAULT NULL COMMENT '以收款人名义付款-身份证国徽页',
  `poboValidIdentType` varchar(50) DEFAULT NULL COMMENT 'pobo其他材料类型',
  `poboValidIdentFile` varchar(255) DEFAULT NULL COMMENT 'pobo其他材料文件',
  `supplierType` varchar(150) DEFAULT NULL COMMENT '供应商类型',
  `otherServiceProviderName` varchar(255) DEFAULT NULL COMMENT '供应商其他支付服务商名称',
  `supplierProveMaterial` varchar(255) DEFAULT NULL COMMENT '供应商证明材料',
  `supplierEnName` varchar(255) DEFAULT NULL COMMENT '供应商英文名称',
  `clearingSysType` varchar(150) DEFAULT NULL COMMENT '付款附言',
  `bankBranch` varchar(255) DEFAULT NULL,
  `isOffshore` varchar(50) DEFAULT NULL,
  `addressLine` text,
  `bankAddressLine` text,
  `createdTime` varchar(255) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clientId` (`clientId`) USING BTREE,
  KEY `idx_beneficiaryId` (`beneficiaryId`) USING BTREE,
  KEY `idx_requestId` (`requestId`) USING BTREE,
  KEY `idx_clientId_status` (`clientId`,`status`) USING BTREE,
  KEY `idx_clientId_name` (`clientId`,`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户收款人表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_charge 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_charge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` varchar(255) DEFAULT NULL COMMENT '请求号',
  `clientId` varchar(100) NOT NULL DEFAULT '0' COMMENT '客户号',
  `amount` varchar(150) DEFAULT NULL COMMENT '归集金额',
  `reason` text COMMENT '归集原因',
  `currency` varchar(255) DEFAULT NULL COMMENT '币种',
  `transactionId` varchar(255) DEFAULT NULL COMMENT '交易id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户转账归集表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_checklog 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_checklog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `check_id` int(11) NOT NULL COMMENT '审核id',
  `requestId` varchar(100) DEFAULT NULL COMMENT '请求号',
  `action_name` varchar(100) DEFAULT NULL COMMENT '节点名称',
  `check_status` varchar(50) DEFAULT NULL COMMENT '状态',
  `channel_name` varchar(50) DEFAULT NULL COMMENT '通道',
  `reason` varchar(255) DEFAULT '' COMMENT '原因',
  `created_time` datetime DEFAULT NULL COMMENT '创建时间',
  `check_user` varchar(255) DEFAULT NULL COMMENT '审核人',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=546 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_curllog 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_curllog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text,
  `request` text NOT NULL COMMENT '请求参数',
  `content` text COMMENT '返回数据',
  `curltype` varchar(100) NOT NULL COMMENT '请求类型',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '接口描述',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=4265 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b请求接口的日志表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_declarant 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_declarant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) DEFAULT NULL COMMENT '客户号',
  `merchantIdHf` varchar(100) DEFAULT NULL COMMENT '客户编码',
  `postcode` varchar(150) DEFAULT NULL COMMENT '邮政编码',
  `industryCode` varchar(150) DEFAULT NULL COMMENT '行业属性代码',
  `econType` varchar(50) DEFAULT NULL COMMENT '经济类型',
  `speEconEnt` char(10) DEFAULT NULL COMMENT '是否为在特殊经济区注册的企业。（Y/N）',
  `speEconEntType` char(10) DEFAULT NULL COMMENT '特殊经济区内企业类型',
  `contactName` varchar(100) DEFAULT NULL COMMENT '联系人姓名',
  `contactNumber` varchar(100) DEFAULT NULL COMMENT '联系电话',
  `orgCode` varchar(255) DEFAULT NULL COMMENT '企业组织机构代码',
  `orgName` varchar(255) DEFAULT NULL COMMENT '企业名称',
  `status` varchar(255) DEFAULT NULL COMMENT 'AWAIT待备案,ACCEPTED已受理,FAILED备案失败,SUCCESS备案成功',
  `platformUserId` varchar(255) DEFAULT NULL COMMENT '平台用户号',
  `declarantId` varchar(255) DEFAULT NULL COMMENT '申报主体ID（结汇付款时若选择对公申报使用，仅当企业主体备案为备案成功时方可在付款时使用。）',
  `rank` varchar(255) DEFAULT NULL COMMENT '企业资质，ABC空',
  `declarantAdditionalInfo` text COMMENT 'json额外补充信息',
  `declarantCardInfo` text COMMENT 'json申报人卡信息',
  `msg` text,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户企业备案信息表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_declarant_entity 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_declarant_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) DEFAULT NULL COMMENT '客户号',
  `platformUserId` varchar(255) DEFAULT NULL COMMENT '平台用户号',
  `declarantType` varchar(150) DEFAULT NULL COMMENT '申报主体类型INDIVIDUAL – 个人申报主体,BUSINESS – 企业申报主体',
  `declarantName` varchar(150) DEFAULT NULL COMMENT '申报主体姓名',
  `country` varchar(50) DEFAULT NULL COMMENT '国家/地区',
  `idType` varchar(100) DEFAULT NULL COMMENT '证件类型',
  `idNumber` varchar(100) DEFAULT NULL COMMENT '证件号码',
  `idValidDtFr` varchar(100) DEFAULT NULL COMMENT '证件有效期起始日期',
  `idValidDtTo` varchar(100) DEFAULT NULL COMMENT '证件有效期截止日期',
  `idCopyFront` varchar(255) DEFAULT NULL COMMENT '证件影印件正面fileId',
  `phone` varchar(150) DEFAULT NULL COMMENT '企业联系电话',
  `address` varchar(255) DEFAULT NULL COMMENT '企业经营地址',
  `email` varchar(150) DEFAULT NULL COMMENT '联系邮箱',
  `acctType` varchar(150) DEFAULT NULL COMMENT '账户类型 BUSINESS',
  `acctName` varchar(150) DEFAULT NULL COMMENT '账户名称',
  `acctNumber` varchar(150) DEFAULT NULL COMMENT '账户号',
  `acctBankName` varchar(150) DEFAULT NULL COMMENT '账户银行名称',
  `platformName` varchar(150) DEFAULT NULL COMMENT '电商平台名称',
  `storeId` varchar(150) DEFAULT NULL COMMENT '平台店铺id、sellerId或者其他唯一标识',
  `storeUrl` varchar(255) DEFAULT NULL COMMENT '平台店铺URL',
  `status` varchar(255) DEFAULT NULL COMMENT '业务处理状态，同步返回仅有如下状态：PROCESSING – 处理中,FAILED – 开户失败',
  `recordStatus` varchar(255) DEFAULT NULL COMMENT '企业申报主体备案状态，仅成功开户的企业申报主体涉及备案状态,AWAIT- 待备案,ACCEPTED – 已受理,FAILED – 备案失败,SUCCESS – 备案成',
  `declarantId` varchar(255) DEFAULT NULL COMMENT '申报主体ID（结汇付款时若选择对公申报使用，仅当企业主体备案为备案成功时方可在付款时使用。）',
  `rank` varchar(255) DEFAULT NULL COMMENT '企业资质，ABC空',
  `requestId` varchar(150) DEFAULT NULL COMMENT '请求id',
  `declarantFillingInfo` varchar(255) DEFAULT NULL COMMENT '备案信息',
  `declarantAdditionalInfo` varchar(255) DEFAULT NULL COMMENT '额外补充信息',
  `declarantCardInfo` text COMMENT '申报人卡信息',
  `orgCode` varchar(255) DEFAULT NULL COMMENT '企业组织机构代码',
  `orgName` varchar(255) DEFAULT NULL COMMENT '企业名称',
  `msg` text COMMENT '返回信息',
  `type` varchar(150) DEFAULT NULL COMMENT '是否以自身作为申报主体',
  `declarantBasicInfo` text COMMENT '基本信息',
  `declarantIdDetails` text COMMENT '证件信息',
  `declarantContact` text COMMENT '联系信息',
  `legalPerson` text COMMENT '法人信息',
  `contactPerson` text COMMENT '联系人信息',
  `storeInfo` text COMMENT '店铺信息',
  `businessInfo` text COMMENT '企业信息',
  `fileId` varchar(150) DEFAULT NULL COMMENT '备案文件id',
  `extendInfo` text COMMENT '扩展字段',
  `createTime` varchar(255) DEFAULT NULL COMMENT '提交时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clientId` (`clientId`) USING BTREE,
  KEY `idx_requestId` (`requestId`) USING BTREE,
  KEY `idx_declarantId` (`declarantId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户申报主体登记信息表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_exchange 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_exchange` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) DEFAULT NULL COMMENT '客户号',
  `requestId` varchar(150) DEFAULT NULL COMMENT '请求id',
  `requestTime` varchar(150) DEFAULT NULL COMMENT '请求时间',
  `sourceCur` varchar(50) DEFAULT NULL COMMENT '源币种',
  `targetCur` varchar(50) DEFAULT NULL COMMENT '目标币种',
  `sourceAmt` varchar(150) DEFAULT NULL COMMENT '源币种金额(原样返回请求锁定的币种金额)',
  `targetAmt` varchar(150) DEFAULT NULL COMMENT '目标币种金额(根据锁定币种金额及汇率返回换算的币种金额)',
  `validateTo` varchar(150) DEFAULT NULL COMMENT '汇率失效时间',
  `curPair` varchar(150) DEFAULT NULL COMMENT '货币对',
  `clientRate` varchar(150) DEFAULT NULL COMMENT '报价客户汇率',
  `quoteId` varchar(150) DEFAULT NULL COMMENT '报价单id',
  `type` varchar(150) DEFAULT NULL COMMENT '换汇业务类型：1询价(inquiry)，2下单(apply)',
  `tenor` varchar(50) DEFAULT NULL COMMENT '交割方式',
  `conversionDate` varchar(150) DEFAULT NULL COMMENT '换汇交割日期',
  `contractId` varchar(150) DEFAULT NULL COMMENT '换汇合同号',
  `status` varchar(150) DEFAULT NULL COMMENT '订单状态:换汇订单状态SUBMITTED-已提交',
  `msg` varchar(255) DEFAULT NULL COMMENT '状态说明(只有换汇申请才有状态)',
  `validity` varchar(150) DEFAULT NULL COMMENT '锁汇时间：目前暂时只能传MIN_1',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clientId` (`clientId`) USING BTREE,
  KEY `idx_requestId` (`requestId`) USING BTREE,
  KEY `idx_type_clientId` (`type`,`clientId`) USING BTREE,
  KEY `idx_clientId_status` (`clientId`,`status`) USING BTREE,
  KEY `idx_clientId_currency` (`clientId`,`sourceCur`,`targetCur`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户换汇询价和申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_fee 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_fee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channelCode` varchar(150) DEFAULT NULL COMMENT 'hf/yb/bf...',
  `type` varchar(50) DEFAULT NULL COMMENT '费用类型：手续费1',
  `method` varchar(255) DEFAULT NULL COMMENT '费用计算方式：固定值1(+/-)、百分比2(*)',
  `fee` varchar(255) DEFAULT NULL COMMENT '费用系数',
  `status` varchar(50) DEFAULT NULL COMMENT '状态 0/1 禁用/启用',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_channelCode` (`channelCode`) USING BTREE,
  KEY `idx_type` (`type`) USING BTREE,
  KEY `idx_method` (`method`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b费用表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_files 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fileId` varchar(255) DEFAULT NULL COMMENT '文件ID',
  `channelId` int(11) DEFAULT NULL COMMENT 'pingpong,汇付',
  `merchantId` int(11) DEFAULT NULL COMMENT '商户用户id',
  `type` varchar(255) DEFAULT NULL COMMENT '使用场景',
  `fileName` varchar(255) DEFAULT NULL COMMENT '本地文件名',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_ga 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_ga` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) NOT NULL DEFAULT '0' COMMENT '客户号',
  `currency` varchar(50) DEFAULT NULL COMMENT '币种',
  `country` varchar(50) DEFAULT NULL COMMENT '收款国家/地区',
  `paymentMethod` varchar(50) DEFAULT NULL COMMENT '收款方式SWIFT或LOCAL',
  `accountNumber` varchar(255) DEFAULT NULL COMMENT '收款账号',
  `bic` varchar(255) DEFAULT NULL COMMENT 'bic/swift_code',
  `iban` varchar(255) DEFAULT NULL COMMENT 'iban 欧元账户仅返回该字段，不返回accountNumber，以此为收款账号',
  `clearingSysType` varchar(255) DEFAULT NULL COMMENT '清算系统类型',
  `clearingSysNumber` varchar(255) DEFAULT NULL COMMENT '清算系统号',
  `status` varchar(50) DEFAULT NULL COMMENT '状态 APPLYING-申请中 ACTIVE-已开通（同步返回ACTIVE，无需等待异步）',
  `userDeviceInfo` text COMMENT '用户设备信息',
  `requestId` varchar(255) DEFAULT NULL COMMENT '请求id',
  `financialInstName` varchar(255) DEFAULT NULL COMMENT '银行名称',
  `financialInstAddress` varchar(255) DEFAULT NULL COMMENT '银行地址',
  `createdTime` varchar(255) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户表收款账户表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_orders 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` varchar(255) DEFAULT NULL COMMENT '请求号',
  `clientId` varchar(100) NOT NULL DEFAULT '0' COMMENT '客户号',
  `contractNo` varchar(100) DEFAULT NULL COMMENT '合同编号',
  `orderTime` varchar(150) DEFAULT NULL COMMENT '订单时间',
  `orderAmount` varchar(150) DEFAULT '0' COMMENT '订单金额',
  `orderCurrency` varchar(100) DEFAULT '0' COMMENT '订单币别',
  `payerName` varchar(150) DEFAULT NULL COMMENT '买家英文名称',
  `tradeCountry` varchar(50) DEFAULT NULL COMMENT '贸易国家/地区',
  `goodsCategory` varchar(150) DEFAULT NULL COMMENT '待解付金额',
  `goodsName` varchar(150) DEFAULT NULL COMMENT '商品英文名称',
  `goodsCnName` varchar(150) DEFAULT NULL COMMENT '商品中文名称',
  `goodsCount` varchar(50) DEFAULT NULL COMMENT '商品数量',
  `goodsUnit` varchar(50) DEFAULT NULL COMMENT '商品单位 商品单位 枚举值(PCS; SETS; G; KG; MT/TON;YARDS; ROLLS; PAIRS)',
  `websiteUrl` varchar(255) DEFAULT NULL COMMENT '商品/店铺网址信息',
  `isShipped` tinyint(2) DEFAULT '0' COMMENT '是否发货：已发货-1; 未发货-0',
  `payerType` varchar(50) DEFAULT NULL COMMENT '新/老买家：新买家-NEW; 老买家-OLD)，未发货时必填',
  `shipper` varchar(50) DEFAULT NULL COMMENT '发货模式：自主发货; 货代发货)，已发货必填',
  `expectShippingDate` varchar(50) DEFAULT NULL COMMENT '预计发货时间：未发货必填',
  `actualShippingDate` varchar(50) DEFAULT NULL COMMENT '实际发货时间：已发货必填',
  `shippingCompany` varchar(255) DEFAULT NULL COMMENT '物流公司名称：已发货必填',
  `shippingType` varchar(255) DEFAULT NULL COMMENT '实际物流方式：已发货必填',
  `shippingNo` varchar(255) DEFAULT NULL COMMENT '出境物流单号：已发货必填',
  `dealType` varchar(255) DEFAULT NULL COMMENT '成交方式：枚举值 (CFR; CIF; CIP; CPT; DAF;DDP; DDU; DEQ; DES; EXW; FAS;FCA; FOB; 其他',
  `isExchangeSettle` tinyint(2) DEFAULT '0' COMMENT '是否结汇:结汇-1; 不结汇-0',
  `ciPiContracts` varchar(255) DEFAULT NULL COMMENT 'CI/PI/合同文件 json',
  `debtorAccount` varchar(100) DEFAULT NULL COMMENT '付款账号',
  `debtorName` varchar(255) DEFAULT NULL COMMENT '付款账户名',
  `payMode` varchar(255) DEFAULT NULL COMMENT '付款模式',
  `debtorFinInstCountry` varchar(255) DEFAULT NULL COMMENT '付款行所在国家',
  `contactIdType` varchar(255) DEFAULT NULL COMMENT '是否对公申报',
  `orderSeqId` varchar(150) DEFAULT NULL COMMENT '订单ID',
  `inquiryRecords` varchar(255) DEFAULT NULL COMMENT '询盘下单沟通记录 json',
  `freightRecords` varchar(255) DEFAULT NULL COMMENT '货运安排沟通记录 json',
  `lastOrderDocs` varchar(255) DEFAULT NULL COMMENT '与该买家最近一笔交易的报关单或出境物流凭证 json',
  `shippingDocs` varchar(255) DEFAULT NULL COMMENT '发货凭证 json',
  `isCorporateDeclare` varchar(100) DEFAULT NULL COMMENT '是否对公申报',
  `customsCode` varchar(100) DEFAULT NULL COMMENT '海关监管方式',
  `decNumber` varchar(100) DEFAULT NULL COMMENT '报关单号',
  `fileType` varchar(100) DEFAULT NULL COMMENT '报关文件类型',
  `customsDecDocs` varchar(255) DEFAULT NULL COMMENT '报关文件 json',
  `status` varchar(255) DEFAULT NULL COMMENT '状态',
  `pendingAmount` varchar(255) DEFAULT NULL COMMENT '剩余可收款金额',
  `relatedAmount` varchar(255) DEFAULT NULL COMMENT '已入账金额',
  `declaredAmount` varchar(255) DEFAULT NULL COMMENT '已申报金额',
  `declareBalance` varchar(255) DEFAULT NULL COMMENT '可申报金额',
  `modifiedTime` varchar(255) DEFAULT NULL COMMENT '最近更新时间',
  `createdTime` varchar(255) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clientId` (`clientId`) USING BTREE,
  KEY `idx_orderSeqId` (`orderSeqId`) USING BTREE,
  KEY `idx_requestId` (`requestId`) USING BTREE,
  KEY `idx_clientId_requestId` (`clientId`,`requestId`) USING BTREE,
  KEY `idx_clientId_pendingAmount` (`clientId`,`pendingAmount`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户订单创建申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_payment 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) DEFAULT NULL COMMENT '客户号',
  `requestId` varchar(150) DEFAULT NULL COMMENT '请求id',
  `mode` varchar(150) DEFAULT NULL COMMENT '付款模式：境内下发必填,STANDARD – 标准入境下发,T0 – 额度即时下发',
  `orderSeqIds` varchar(150) DEFAULT NULL COMMENT '申报主体ID',
  `declarantId` varchar(150) DEFAULT NULL COMMENT '申报主体ID',
  `beneficiaryId` varchar(150) DEFAULT NULL COMMENT '收款人id',
  `currency` varchar(50) DEFAULT NULL COMMENT '币种',
  `amount` varchar(150) DEFAULT NULL COMMENT '金额',
  `purpose` varchar(150) DEFAULT NULL COMMENT '目的:(提现：TRANSFER_TO_OWN_ACCOUNT,供应商：PURCHASE_OF_GOODS)',
  `transactionId` varchar(150) DEFAULT NULL COMMENT '交易号:恒新系统交易id',
  `handleResult` varchar(150) DEFAULT NULL COMMENT '处理/审核结果:APPROVED - 通过（即结束挂起，继续流程）REJECTED - 拒绝（即确认有风险，中止交易）',
  `handleRemark` varchar(150) DEFAULT NULL COMMENT '错误信息:失败时返回失败原因',
  `status` varchar(100) DEFAULT NULL COMMENT '付款交易状态',
  `errMsg` varchar(255) DEFAULT NULL COMMENT '错误信息',
  `paymentDetails` varchar(255) DEFAULT NULL COMMENT '付款信息',
  `orderDeclareClass` varchar(150) DEFAULT NULL COMMENT '订单申报类别',
  `declaranttype` varchar(50) DEFAULT NULL COMMENT '申报类型',
  `method` varchar(255) DEFAULT NULL COMMENT '付款方式',
  `feeOption` varchar(255) DEFAULT NULL COMMENT '费用承担方式',
  `remittanceInfo` varchar(255) DEFAULT NULL COMMENT '付款附言',
  `createdTime` varchar(255) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clientId` (`clientId`) USING BTREE,
  KEY `idx_beneficiaryId` (`beneficiaryId`) USING BTREE,
  KEY `idx_transactionId` (`transactionId`) USING BTREE,
  KEY `idx_requestId` (`requestId`) USING BTREE,
  KEY `idx_clientId_currency` (`clientId`,`currency`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户付款表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_refund 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) NOT NULL DEFAULT '0' COMMENT '客户号',
  `transactionId` varchar(100) DEFAULT NULL COMMENT '汇入汇款交易ID',
  `status` varchar(50) DEFAULT NULL COMMENT 'REFUND –待退款',
  `amount` varchar(255) DEFAULT '0' COMMENT '金额',
  `currency` varchar(150) DEFAULT NULL COMMENT '币种',
  `pendingAmt` varchar(255) DEFAULT NULL COMMENT '待解付金额',
  `transactionTime` varchar(150) DEFAULT NULL COMMENT '汇入汇款创建时间',
  `additionalInfo` text COMMENT '附加信息 json',
  `feeAmount` varchar(100) DEFAULT NULL COMMENT '退款手续费',
  `refundAmount` varchar(100) DEFAULT NULL COMMENT '退款金额',
  `accountNumber` varchar(100) DEFAULT NULL COMMENT '收款账户',
  `bic` varchar(100) DEFAULT NULL COMMENT '收款账户BIC',
  `iban` varchar(100) DEFAULT NULL COMMENT '收款方银行bic',
  `refundProof` varchar(200) DEFAULT NULL COMMENT '退款凭证',
  `paccountNumber` varchar(100) DEFAULT NULL COMMENT '付款方账户号',
  `pbic` varchar(100) DEFAULT NULL COMMENT '	\r\n付款方银行bic',
  `pname` varchar(100) DEFAULT NULL COMMENT '付款方名称',
  `remittanceInfo` varchar(255) DEFAULT NULL COMMENT '汇款附言',
  `refundcurrency` varchar(100) DEFAULT NULL COMMENT '退款币种',
  `collectionCcy` varchar(100) DEFAULT NULL COMMENT '收款账户币种',
  `clearingSysNumber` varchar(100) DEFAULT NULL COMMENT '收款账户清算系统号',
  `clearingSysType` varchar(100) DEFAULT NULL COMMENT '收款账户清算系统类型',
  `requestId` varchar(100) DEFAULT NULL COMMENT '请求id',
  `respMsg` varchar(255) DEFAULT NULL COMMENT '失败原因',
  `refundTime` varchar(255) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clientId` (`clientId`) USING BTREE,
  KEY `idx_transactionId` (`transactionId`) USING BTREE,
  KEY `idx_clientId_transactionId` (`clientId`,`transactionId`) USING BTREE,
  KEY `idx_clientId_status` (`clientId`,`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户汇入汇款退款表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_remittance 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_remittance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) NOT NULL DEFAULT '0' COMMENT '客户号',
  `amount` varchar(255) DEFAULT '0' COMMENT '金额',
  `currency` varchar(150) DEFAULT NULL COMMENT '币种',
  `pendingAmt` varchar(255) DEFAULT NULL COMMENT '待解付金额',
  `transactionId` varchar(100) DEFAULT NULL COMMENT '汇入汇款交易ID',
  `transactionTime` varchar(150) DEFAULT NULL COMMENT '汇入汇款创建时间',
  `additionalInfo` text COMMENT '附加信息 json',
  `accountNumber` varchar(255) DEFAULT NULL COMMENT '收款账户',
  `abic` varchar(100) DEFAULT NULL COMMENT '收款账户BIC',
  `clearingSysNumber` varchar(100) DEFAULT NULL COMMENT '收款账户清算系统号',
  `clearingSysType` varchar(100) DEFAULT NULL COMMENT '收款账户清算系统类型',
  `acurrency` varchar(150) DEFAULT NULL COMMENT '收款账户币种',
  `iban` varchar(100) DEFAULT NULL COMMENT '收款方银行bic',
  `paccountNumber` varchar(100) DEFAULT NULL COMMENT '付款方账户号',
  `pbic` varchar(100) DEFAULT NULL COMMENT '	\r\n付款方银行bic',
  `pname` varchar(100) DEFAULT NULL COMMENT '付款方名称',
  `remittanceInfo` varchar(255) DEFAULT NULL COMMENT '汇款附言',
  `status` varchar(50) DEFAULT NULL COMMENT 'REFUND –待退款、REFUNDED-已退款成功、PROCESSING-审核中、SUCCESS-解付审核通过',
  `respMsg` varchar(255) DEFAULT NULL COMMENT '失败原因',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户汇入汇款表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_requestlog 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_requestlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchantid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '商户ID',
  `requestContent` text COLLATE utf8mb4_unicode_ci COMMENT '请求内容(JSON格式)',
  `backContent` text COLLATE utf8mb4_unicode_ci COMMENT '返回内容',
  `callbackContent` text COLLATE utf8mb4_unicode_ci COMMENT '回调内容',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `createdTime` int(11) DEFAULT '0' COMMENT '创建时间(时间戳)',
  `updateTime` int(11) DEFAULT '0' COMMENT '更新时间(时间戳)',
  `request_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '请求IP',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantid` (`merchantid`) USING BTREE,
  KEY `idx_createdTime` (`createdTime`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='B2B请求日志表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_sendlog 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_sendlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchantid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '商户ID',
  `requestContent` text COLLATE utf8mb4_unicode_ci COMMENT '发送内容(JSON格式)',
  `backContent` text COLLATE utf8mb4_unicode_ci COMMENT '返回内容',
  `callbacurl` varchar(3000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '推送地址',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `createdTime` int(11) DEFAULT '0' COMMENT '创建时间(时间戳)',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantid` (`merchantid`) USING BTREE,
  KEY `idx_createdTime` (`createdTime`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='B2B请求日志表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_store 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(255) DEFAULT NULL,
  `requestId` varchar(255) DEFAULT NULL COMMENT '请求号',
  `storeId` varchar(255) DEFAULT NULL COMMENT '店铺ID',
  `storeName` varchar(255) DEFAULT NULL COMMENT '店铺名',
  `platformCode` varchar(50) DEFAULT NULL COMMENT '平台编码（根据接口文档枚举）',
  `productCategories` varchar(50) DEFAULT NULL COMMENT '销售类目',
  `expectedSale` varchar(50) DEFAULT NULL COMMENT '预计年销售量',
  `currency` char(10) DEFAULT NULL COMMENT '店铺收款币种',
  `region` char(10) DEFAULT NULL COMMENT '店铺收款国家地区',
  `shopWebsite` varchar(255) DEFAULT NULL COMMENT '店铺网址',
  `attachments` text COMMENT 'json,附件，支持多个',
  `status` varchar(50) DEFAULT NULL COMMENT '状态',
  `respMsg` text COMMENT '返回信息',
  `createdTime` varchar(150) DEFAULT NULL COMMENT '创建时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `accountNumber` varchar(255) DEFAULT NULL COMMENT '收款账号',
  `paymentMethod` varchar(150) DEFAULT NULL COMMENT '收款方式',
  `bic` varchar(150) DEFAULT NULL COMMENT '银行国际代码',
  `iban` varchar(150) DEFAULT NULL COMMENT '国际银行账户号码',
  `financialInstName` varchar(150) DEFAULT NULL COMMENT '银行名称',
  `financialInstAddress` text COMMENT '银行地址',
  `clearingSysNumber` varchar(150) DEFAULT NULL COMMENT '汇款路由号',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_clientId` (`clientId`) USING BTREE,
  KEY `idx_storeId` (`storeId`) USING BTREE,
  KEY `idx_storeName` (`storeName`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_accountNumber` (`accountNumber`) USING BTREE,
  KEY `idx_created_time` (`createdTime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2c店铺';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_b2b_tradesettle 结构
CREATE TABLE IF NOT EXISTS `doopsun_b2b_tradesettle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` varchar(255) DEFAULT NULL COMMENT '请求号',
  `clientId` varchar(100) NOT NULL DEFAULT '0' COMMENT '客户号',
  `transactionId` varchar(100) DEFAULT NULL COMMENT '汇入汇款编号',
  `orderItems` text COMMENT '关联订单 json',
  `respMsg` text COMMENT '收返回信息',
  `status` varchar(50) DEFAULT NULL COMMENT '状态SUCCESS 解付审核通过,FAILED 解付审核未通过,PROCESSING 审核中',
  `createTime` varchar(150) DEFAULT NULL COMMENT '解付时间',
  `settleAmt` varchar(150) DEFAULT '0' COMMENT '订单金额',
  `settleCur` varchar(150) DEFAULT '0' COMMENT '解付币别',
  `amount` varchar(255) DEFAULT NULL COMMENT '退款金额',
  `currency` varchar(255) DEFAULT NULL COMMENT '退款币种',
  `feeAmount` varchar(255) DEFAULT NULL COMMENT '退款手续费',
  `refundAmount` varchar(255) DEFAULT NULL COMMENT '退款金额',
  `refundProof` varchar(255) DEFAULT NULL COMMENT '退款凭证',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_transactionId` (`transactionId`) USING BTREE,
  KEY `idx_requestId` (`requestId`) USING BTREE,
  KEY `idx_clientId_status` (`clientId`,`status`) USING BTREE,
  KEY `idx_transactionId_status` (`transactionId`,`status`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户解付申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_bankcard 结构
CREATE TABLE IF NOT EXISTS `doopsun_bankcard` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '银行表id',
  `merchantId` int(11) NOT NULL COMMENT '商户号',
  `cardNum` char(19) NOT NULL COMMENT '银行卡号(暂用19位)',
  `bankName` varchar(55) NOT NULL COMMENT '开户行',
  `bankBranch` varchar(55) NOT NULL COMMENT '支行',
  `certificateType` varchar(55) NOT NULL COMMENT '证件类型',
  `certificateNum` varchar(55) NOT NULL COMMENT '证件号码',
  `holderName` varchar(55) NOT NULL COMMENT '持卡人姓名',
  `phoneNum` varchar(25) NOT NULL COMMENT '预留电话',
  `isDefault` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `editTime` int(11) DEFAULT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_bankvaaccount 结构
CREATE TABLE IF NOT EXISTS `doopsun_bankvaaccount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '商户id',
  `accountname` varchar(100) NOT NULL COMMENT '账户名称',
  `receiveaccount` varchar(100) NOT NULL COMMENT '收款账户',
  `banknum` varchar(200) DEFAULT '' COMMENT '银行号',
  `branchcode` int(11) NOT NULL COMMENT '分行代码',
  `swiftcode` varchar(50) NOT NULL COMMENT 'SWIFT代码',
  `bankname` varchar(255) NOT NULL COMMENT '银行名称',
  `accoundaddress` varchar(255) NOT NULL COMMENT '账户地点',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认0:不，1默认',
  `addtime` int(11) NOT NULL COMMENT '创建时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='VA账户表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_banner 结构
CREATE TABLE IF NOT EXISTS `doopsun_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '名称',
  `banner_img` varchar(100) DEFAULT NULL COMMENT 'banner图',
  `web_type` tinyint(1) DEFAULT NULL COMMENT '平台类型',
  `lan_type` tinyint(1) DEFAULT NULL COMMENT '语言类型',
  `url` varchar(100) DEFAULT NULL COMMENT '跳转链接',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `status` tinyint(1) DEFAULT NULL COMMENT '1正常',
  `created_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_blacklists 结构
CREATE TABLE IF NOT EXISTS `doopsun_blacklists` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('ip','email','region','card_no','website') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类型',
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '黑名单值',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原因',
  `risk_level` enum('low','mid','high') COLLATE utf8mb4_unicode_ci DEFAULT 'high' COMMENT '风险等级',
  `expire_type` enum('permanent','7d','30d','90d') COLLATE utf8mb4_unicode_ci DEFAULT 'permanent' COMMENT '到期类型',
  `expire_at` datetime DEFAULT NULL COMMENT '到期时间',
  `status` enum('active','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT '状态',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='黑名单';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_channel 结构
CREATE TABLE IF NOT EXISTS `doopsun_channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_name` varchar(200) NOT NULL COMMENT '通道名称',
  `channel_type` varchar(100) NOT NULL COMMENT '类型',
  `category` varchar(50) NOT NULL COMMENT '品类',
  `status` tinyint(1) NOT NULL COMMENT '状态，1启用，2停用',
  `description` text COMMENT '通道介绍',
  `advantages` text COMMENT '主要优势',
  `feejson` text COMMENT '费用信息',
  `cashback` text COMMENT '返现费用',
  `created_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_coin_rate 结构
CREATE TABLE IF NOT EXISTS `doopsun_coin_rate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(8) NOT NULL DEFAULT 'live' COMMENT '1  实时,2 固定',
  `add_rate` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '附加汇率',
  `fixed_rate` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '固定汇率',
  `add_user` int(10) NOT NULL,
  `create_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_coin_refund 结构
CREATE TABLE IF NOT EXISTS `doopsun_coin_refund` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '退款申请ID',
  `merchant_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户号',
  `coin_amount` decimal(10,2) NOT NULL COMMENT '德普币扣除金额',
  `amount` decimal(10,2) NOT NULL COMMENT '退款金额',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '退款状态（1 待审核 2退款成功 3拒绝）',
  `remark` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '备注',
  `audit_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提款时间',
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `exchange_rate` decimal(10,5) DEFAULT '0.00000' COMMENT '汇率',
  `amount_cny` decimal(10,2) DEFAULT NULL COMMENT '提款人民币',
  `amount_usd` decimal(10,2) DEFAULT NULL COMMENT '提款美元',
  `currency` varchar(255) NOT NULL DEFAULT 'USD' COMMENT '币种',
  `account` varchar(255) NOT NULL DEFAULT '' COMMENT '退款账号',
  `account_name` varchar(255) NOT NULL DEFAULT '' COMMENT '账号名称',
  `audit_remark` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '审核备注',
  `bank` varchar(255) NOT NULL DEFAULT '' COMMENT '银行名称',
  `swift_code` varchar(255) NOT NULL DEFAULT '' COMMENT 'swift code',
  `bank_address` varchar(255) NOT NULL DEFAULT '' COMMENT '银行地址',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='提款申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_config 结构
CREATE TABLE IF NOT EXISTS `doopsun_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL COMMENT '业务类型：va va账户管理，payment 付款业务',
  `ruleJson` text COMMENT '规则json:va账户管理applyDate:申请总数 (每个业务填自己的规则)',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT NULL COMMENT '激活1，未激活0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_type` (`type`) USING BTREE,
  KEY `idx_remark` (`remark`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='系统规则配置表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_control_measures 结构
CREATE TABLE IF NOT EXISTS `doopsun_control_measures` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '措施名称',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '说明',
  `risk_level` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT '高风险' COMMENT '适用风险等级',
  `status` enum('enabled','disabled') COLLATE utf8mb4_unicode_ci DEFAULT 'enabled' COMMENT '状态',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='管控措施';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_country_codes 结构
CREATE TABLE IF NOT EXISTS `doopsun_country_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键（替代序号功能）',
  `cn_name` varchar(50) NOT NULL COMMENT '中文名称',
  `en_name` varchar(100) NOT NULL COMMENT '英文名称',
  `two_code` char(2) NOT NULL COMMENT '二字码（ISO 3166-1 alpha-2）',
  `three_code` char(3) NOT NULL COMMENT '三字码（ISO 3166-1 alpha-3）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_two_code` (`two_code`) USING BTREE,
  UNIQUE KEY `idx_three_code` (`three_code`) USING BTREE,
  KEY `idx_cn_name` (`cn_name`) USING BTREE,
  KEY `idx_en_name` (`en_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='国家编码对照表（ISO 3166-1标准）';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_creditcardchannel 结构
CREATE TABLE IF NOT EXISTS `doopsun_creditcardchannel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL COMMENT '渠道id',
  `account_name` varchar(100) DEFAULT NULL COMMENT '渠道名称',
  `abbreviation` varchar(100) DEFAULT NULL COMMENT '简称',
  `channel_code` varchar(100) DEFAULT '' COMMENT '编号',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，1正常，2禁用',
  `created_time` int(11) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `merchantId` int(11) DEFAULT NULL COMMENT '关联商户ID',
  `userNo` varchar(50) NOT NULL COMMENT '用户编号(GEP提供的唯一编号)',
  `userType` tinyint(2) DEFAULT NULL COMMENT '用户类型:1-境内个人 2-境内企业 3-香港企业 5-其他地区企业',
  `callBackUrl` varchar(500) DEFAULT NULL COMMENT '回调URL',
  `name` varchar(255) DEFAULT NULL COMMENT '姓名/企业名称',
  `enName` varchar(255) DEFAULT NULL COMMENT '英文名称',
  `province` varchar(100) DEFAULT NULL COMMENT '省份',
  `city` varchar(100) DEFAULT NULL COMMENT '城市',
  `area` varchar(100) DEFAULT NULL COMMENT '区域',
  `address` varchar(500) DEFAULT NULL COMMENT '详细地址',
  `postCode` varchar(20) DEFAULT NULL COMMENT '邮编',
  `license` varchar(20) DEFAULT NULL COMMENT '营业执照文件ID',
  `licenseNo` varchar(100) DEFAULT NULL COMMENT '营业执照号',
  `companyType` varchar(100) DEFAULT NULL COMMENT '公司类型',
  `registerProvince` varchar(100) DEFAULT NULL COMMENT '注册省份',
  `registerCity` varchar(100) DEFAULT NULL COMMENT '注册城市',
  `registerArea` varchar(100) DEFAULT NULL COMMENT '注册区域',
  `registerAddress` varchar(500) DEFAULT NULL COMMENT '注册地址',
  `registerDate` date DEFAULT NULL COMMENT '注册日期',
  `registerAmt` decimal(15,2) DEFAULT NULL COMMENT '注册资本',
  `registerCcy` varchar(10) DEFAULT NULL COMMENT '注册币种',
  `endDate` date DEFAULT NULL COMMENT '营业执照有效期',
  `managementScope` text COMMENT '经营范围',
  `industryId` varchar(50) DEFAULT NULL COMMENT '行业ID',
  `economyId` varchar(50) DEFAULT NULL COMMENT '经济类型ID',
  `legalRepresentative` varchar(100) DEFAULT NULL COMMENT '法人代表',
  `legalIdType` tinyint(2) DEFAULT NULL COMMENT '法人证件类型',
  `legalIdFront` bigint(20) DEFAULT NULL COMMENT '法人证件正面文件ID',
  `legalIdReverse` bigint(20) DEFAULT NULL COMMENT '法人证件反面文件ID',
  `legalIdHold` bigint(20) DEFAULT NULL COMMENT '法人手持证件文件ID',
  `legalIdNo` varchar(50) DEFAULT NULL COMMENT '法人证件号',
  `legalName` varchar(100) DEFAULT NULL COMMENT '法人姓名',
  `legalFirstName` varchar(255) DEFAULT NULL,
  `legalLastName` varchar(255) DEFAULT NULL,
  `legalBirthDate` date DEFAULT NULL COMMENT '法人出生日期',
  `legalCertExpiryDate` date DEFAULT NULL COMMENT '法人证件有效期',
  `legalProvince` varchar(100) DEFAULT NULL COMMENT '法人省份',
  `legalCity` varchar(100) DEFAULT NULL COMMENT '法人城市',
  `legalArea` varchar(100) DEFAULT NULL COMMENT '法人区域',
  `legalAddress` varchar(500) DEFAULT NULL COMMENT '法人地址',
  `legalCountryCode` varchar(10) DEFAULT NULL COMMENT '法人国家代码',
  `legalGender` tinyint(1) DEFAULT NULL COMMENT '法人性别:1-男 2-女',
  `businessRegister` varchar(255) DEFAULT NULL COMMENT '商业登记证文件ID',
  `declarationForm` varchar(255) DEFAULT NULL COMMENT '申报表文件ID',
  `incorporationForm` varchar(255) DEFAULT NULL COMMENT '公司注册证书文件ID',
  `orgRegister` varchar(255) DEFAULT NULL COMMENT '组织注册文件ID',
  `stockCertificate` varchar(255) DEFAULT NULL COMMENT '股票证书文件ID',
  `orgRegisterNo` varchar(100) DEFAULT NULL COMMENT '组织注册号',
  `businessRegisterNo` varchar(100) DEFAULT NULL COMMENT '商业登记证号',
  `businessRegisterExpiryDate` date DEFAULT NULL COMMENT '商业登记证有效期',
  `directorList` text COMMENT '董事信息列表(JSON数组)，包含: directorIdType, directorIdFront, directorIdReverse, directorIdHold, directorIdNo, directorName, directorFirstName, directorLastName, directorGender, directorBirthDate, directorCertExpiryDate, directorProvince, directorCity, directorArea, directorAddress, directorCountry/directorCountryCode',
  `otherAccessories` varchar(255) DEFAULT NULL COMMENT '其他附件文件ID',
  `registerCountry` varchar(10) DEFAULT NULL COMMENT '注册国家代码',
  `beneficiaryList` text COMMENT '受益人信息列表(JSON数组)，包含: beneficiaryIdType, beneficiaryIdFront, beneficiaryIdReverse, beneficiaryIdNo, beneficiaryName, beneficiaryFirstName, beneficiaryLastName, beneficiaryBirthDate, beneficiaryCertExpiryDate, beneficiaryGender, beneficiaryProvince, beneficiaryCity, beneficiaryArea, beneficiaryAddress, beneficiaryCountry, beneficiaryShareholderRatio',
  `certificationStatus` tinyint(2) DEFAULT '0' COMMENT '0-未认证；1-认证中 2-已认证 3-认证失败',
  `certificationResult` text COMMENT '认证结果(JSON格式)',
  `certificationMsg` varchar(500) DEFAULT NULL COMMENT '认证结果消息',
  `certificationTime` datetime DEFAULT NULL COMMENT '认证完成时间',
  `createdTime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updatedTime` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `remark` text COMMENT '备注',
  `qualifiedAuditType` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1-首次认证 2-证件到期\r\n',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_userNo` (`userNo`) USING BTREE,
  KEY `idx_userType` (`userType`) USING BTREE,
  KEY `idx_certificationStatus` (`certificationStatus`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_createdTime` (`createdTime`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='Payful实名认证主表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_applyacc 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_applyacc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '系统商户id',
  `userNo` varchar(200) NOT NULL COMMENT 'GEP分配的用户号',
  `accountPayeeType` int(11) NOT NULL COMMENT '账户收款类型：1-全球收款，2-本地收款\r\n不传默认为1',
  `country` varchar(50) NOT NULL COMMENT 'accountPayeeType 为1时 账户区域为HKG\r\naccountPayeeType 为2时 账户区域参考账户区域与账户开户行关系列表\r\n不传默认HKG',
  `bankCode` int(11) NOT NULL COMMENT '必填\r\n默认为1\r\n账户区域与账户开户行关系',
  `bankAccName` varchar(100) DEFAULT NULL COMMENT '当accountPayeeType 为2时 并且country为KOR时 为必填 字符最大长度为10个字符',
  `webUrl` varchar(200) NOT NULL COMMENT '用户使用平台网址',
  `goodsType` varchar(50) NOT NULL COMMENT '贸易商品大类',
  `goodsArea` varchar(50) NOT NULL COMMENT '贸易国家或地区',
  `goodsTradeType` varchar(50) NOT NULL COMMENT '贸易类型',
  `goodsFileNo` varchar(255) DEFAULT NULL COMMENT '商品大类资质文件id 上传文件接口返回的id',
  `callBackUrl` varchar(255) DEFAULT NULL COMMENT '处理结果回调地址',
  `exceptYearSalesAmount` int(11) NOT NULL COMMENT '1:月交易量10万美元以下\r\n2:月交易量10万（含）-30万美元\r\n3:月交易量30万（含）-50万美元\r\n4:月交易量50万（含）美元以上',
  `platformType` varchar(255) DEFAULT NULL COMMENT '经营平台列表',
  `subAccountCcy` varchar(255) DEFAULT NULL COMMENT '当bankCode = 41时上送以下币种之一（USD/CNH/HKD/EUR），默认USD',
  `applyId` varchar(255) DEFAULT NULL COMMENT 'GEP生成的唯一编号',
  `applyAt` varchar(100) DEFAULT NULL COMMENT '格式：yyyy-MM-dd HH:mm:ss',
  `auditRemarks` varchar(255) DEFAULT NULL COMMENT '审核信息',
  `auditStatus` int(11) DEFAULT NULL COMMENT '-1-审核暂停，1-待审核，2-审核成功，3-审核失败 0-系统审核成功',
  `status` varchar(50) DEFAULT NULL COMMENT '成功，失败，处理中, 申请中',
  `statusCode` varchar(50) DEFAULT NULL COMMENT '	开户状态code，对接系统请使用该字段判断是否成功以及失败，取值如下：\r\n3：成功\r\n4：失败\r\n-1：账户已注销\r\n1:处理中\r\n其他状态位都归为处理中',
  `bankAccountName` varchar(255) DEFAULT NULL COMMENT '银行账户名称',
  `bankNo` varchar(255) DEFAULT NULL COMMENT '银行账号',
  `accountOfBankName` varchar(255) DEFAULT NULL COMMENT '账户开户行',
  `bankName` varchar(255) DEFAULT NULL COMMENT '银行名称',
  `bankAddress` varchar(500) DEFAULT NULL COMMENT '银行地址',
  `bankCountry` varchar(100) DEFAULT NULL COMMENT '银行区域',
  `bankSubCode` varchar(100) DEFAULT NULL COMMENT '分行代码',
  `swiftCode` varchar(100) DEFAULT NULL COMMENT 'swift_code',
  `iban` varchar(100) DEFAULT NULL COMMENT 'IBAN',
  `payeeAddress` varchar(255) DEFAULT NULL COMMENT '收款人地址',
  `routingCode` varchar(100) DEFAULT NULL COMMENT '路由编号',
  `currency` varchar(50) DEFAULT NULL COMMENT '账号币种',
  `coontentJson` text COMMENT '回调数据',
  `created_time` int(11) DEFAULT NULL COMMENT '数据新增时间',
  `userReqNo` varchar(100) DEFAULT '' COMMENT '用户调用此API请求时生成的唯一编号',
  `account_type` tinyint(2) DEFAULT '1' COMMENT '账户类型，1货贸，2服贸',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_applypayment 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_applypayment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `orderId` varchar(255) DEFAULT NULL COMMENT 'GEP订单号(orderId)',
  `userNo` varchar(32) DEFAULT NULL COMMENT '商户号',
  `merchantId` varchar(32) DEFAULT NULL COMMENT '商户号',
  `userReqNo` varchar(32) DEFAULT NULL COMMENT '汇款申请单号	：商户请求GEP系统的申请订单号，由商户保证此单号唯一',
  `paymentCcy` char(3) DEFAULT NULL COMMENT '付款币种：商户开通的出款币种',
  `paymentAmount` varchar(50) DEFAULT NULL COMMENT '付款金额：实际付款的资金，当付款币种和收款币种不一致时，并且固定模式为1时，此字段必须大于0，固定模式为2时此字段填写0',
  `payeeCcy` char(3) DEFAULT NULL COMMENT '收款到账币种：实际境外收款的币种',
  `payeeAmount` varchar(50) DEFAULT NULL COMMENT '收款到账金额：实际境外收款的资金，当付款币种和收款币种不一致时，并且固定模式为1时，此字段必须为0，固定模式为2时此字段必须大于0',
  `tradeRate` varchar(50) DEFAULT NULL COMMENT '交易汇率：交易汇率，交易和到账币种不一致时需先汇兑',
  `fixedModel` tinyint(1) DEFAULT NULL COMMENT '固定模式：固定模式：1-固定付款金额，2-固定收款金额',
  `paymentPurpose` char(2) DEFAULT NULL COMMENT '付款用途：12-供货商 13-物流服务 14-分销推广 15-广告宣传 16-技术服务 17-留学 18-其他',
  `paymentReference` varchar(128) DEFAULT NULL COMMENT '汇款附言，只能是英文',
  `costBorne` char(3) DEFAULT 'OUR' COMMENT '费用承担方式: 费用承担方式：SHA-非全额到账、OUR-全额到账、BEN-收款人承担',
  `paymentMaterial` varchar(50) DEFAULT NULL COMMENT '付款材料文件ID:通过文件上传接口上传成功之后返回的文件编号',
  `cardNo` varchar(128) DEFAULT NULL COMMENT '账户名称：已经在GEP平台绑定的帐号',
  `accountName` varchar(255) DEFAULT NULL COMMENT '账户名称：已经在GEP平台绑定的账户名称，其中银行帐号 + 账户名 + 收款到账币种确认一个收款人帐号信息',
  `paymentFeeCcy` char(3) DEFAULT NULL COMMENT '手续费币种:只能是商户开通的账户币种，出款的手续费币种',
  `paymentFeeAmount` varchar(50) DEFAULT NULL COMMENT '付款的手续费金额',
  `paymentFeeRate` varchar(50) DEFAULT NULL COMMENT '交易手续费金额汇率:手续费的汇率，因为GEP平台手续费都是按照USD来计算，出款的手续费不是USD时，需要折算成对应手续费',
  `paymentSuccessDate` varchar(50) DEFAULT NULL COMMENT '付款成功时间',
  `applyDate` varchar(50) DEFAULT NULL COMMENT '付款申请时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态：1-交易待处理，2-交易处理中，3-交易成功，4-交易失败，5-交易异常 7-已取消 8-待补充材料',
  `bankName` varchar(255) DEFAULT NULL COMMENT '银行名称',
  `remark` text COMMENT '备注',
  `businessNo` varchar(50) DEFAULT NULL COMMENT '收款方主体编号:在添加收款方时返回的编号',
  `paymentMode` varchar(50) DEFAULT NULL COMMENT '付款模式：SWIFT、LOCAL、BILLPAY、BPAY',
  `supplementMaterialList` text COMMENT '补充材料：json',
  `auditStatus` tinyint(1) DEFAULT NULL,
  `auditStatusDesc` text,
  `type` tinyint(1) DEFAULT NULL COMMENT '类型：0货贸-付款，1服贸-付款，2服贸-提现',
  `poboPayment` tinyint(1) DEFAULT NULL COMMENT '是否以用户名义出款	：1-是 0-否',
  `poboRecordId` varchar(50) DEFAULT NULL COMMENT '是否以用户名义出款	：1-是 0-否',
  `industryType` char(2) DEFAULT NULL COMMENT '01-货物贸易 06-物流 08-广告收入',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `userReqNo` (`userReqNo`) USING BTREE,
  KEY `idx_userReqNo` (`userReqNo`) USING BTREE COMMENT '汇款申请单号',
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_paymentCcy` (`paymentCcy`) USING BTREE,
  KEY `idx_payeeCcy` (`payeeCcy`) USING BTREE,
  KEY `idx_merchant_id` (`merchantId`) USING BTREE COMMENT '商户ID索引',
  KEY `idx_cardNo` (`cardNo`) USING BTREE,
  KEY `idx_accountName` (`accountName`) USING BTREE,
  KEY `idx_order_id` (`orderId`) USING BTREE COMMENT 'GEP订单号索引',
  KEY `idx_status` (`status`) USING BTREE COMMENT '状态索引',
  KEY `idx_applyDate` (`applyDate`) USING BTREE COMMENT '创建时间索引',
  KEY `idx_paymentSuccessDate` (`paymentSuccessDate`) USING BTREE COMMENT '付款成功时间索引'
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='宝付付款表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_applypayment_refund 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_applypayment_refund` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `orderNo` varchar(255) DEFAULT NULL COMMENT '交易原订单号',
  `refundOrderNo` varchar(255) DEFAULT NULL COMMENT '退款订单号',
  `userNo` varchar(32) DEFAULT NULL COMMENT 'GEP分配的用户号',
  `merchantId` varchar(32) DEFAULT NULL COMMENT '商户号',
  `refundStatus` tinyint(1) DEFAULT NULL COMMENT '退款状态：1-退款中、2-退款成功',
  `refundAmt` varchar(255) DEFAULT NULL COMMENT '退款金额',
  `refundCcy` char(3) DEFAULT NULL COMMENT '退款金额',
  `refundFeeCcy` char(3) DEFAULT NULL COMMENT '退款手续费币种',
  `refundFee` varchar(255) DEFAULT NULL COMMENT '退款手续费',
  `refundReason` text COMMENT '退款原因',
  `createAt` varchar(50) DEFAULT NULL COMMENT '退款申请时间',
  `refundFinalAt` varchar(50) DEFAULT NULL COMMENT '退款终止时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_refundCcy` (`refundCcy`) USING BTREE,
  KEY `idx_orderNo` (`orderNo`) USING BTREE,
  KEY `idx_refundStatus` (`refundStatus`) USING BTREE,
  KEY `idx_createAt` (`createAt`) USING BTREE,
  KEY `idx_refundFinalAt` (`refundFinalAt`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='宝付退款表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_bak 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_bak` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `requestId` varchar(255) DEFAULT NULL COMMENT '请求号',
  `merchantId` varchar(50) DEFAULT NULL COMMENT '商户编码',
  `merchantIdY` varchar(150) DEFAULT NULL COMMENT '商户编码',
  `userNo` varchar(255) DEFAULT NULL,
  `userType` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `regCountry` varchar(255) DEFAULT NULL,
  `chineseName` varchar(255) DEFAULT NULL,
  `englishName` varchar(255) DEFAULT NULL,
  `incorporationCertNo` varchar(255) DEFAULT NULL,
  `createDate` varchar(255) DEFAULT NULL,
  `effectiveDate` varchar(255) DEFAULT NULL,
  `expiryDate` varchar(255) DEFAULT NULL,
  `regAddress` varchar(255) DEFAULT NULL,
  `businessCountry` varchar(255) DEFAULT NULL,
  `businessAddress` varchar(255) DEFAULT NULL,
  `companyType` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `industryCategory` varchar(255) DEFAULT NULL,
  `serviceCategory` varchar(255) DEFAULT NULL,
  `businessRegion` varchar(255) DEFAULT NULL,
  `employeeRank` varchar(255) DEFAULT NULL,
  `salesVolumeRank` varchar(255) DEFAULT NULL,
  `wealthSource` text COMMENT 'string:财富来源',
  `fundsSource` text COMMENT 'json:预计资金来源',
  `merchantNetinPersons` text COMMENT 'json:董事/股东/法人/主要负责人信息',
  `kycFiles` text COMMENT 'json:资质文件列表',
  `notifyUrl` varchar(255) DEFAULT NULL COMMENT '回调地址',
  `mobileAreaCode` varchar(50) DEFAULT '86' COMMENT '电话区号',
  `enRegAddress` varchar(255) DEFAULT NULL,
  `businessRegCertNo` varchar(255) DEFAULT NULL,
  `businessEffectiveDate` varchar(255) DEFAULT NULL,
  `businessExpiryDate` varchar(255) DEFAULT NULL,
  `enBusinessAddress` varchar(255) DEFAULT NULL,
  `nameUsed` varchar(255) DEFAULT NULL,
  `otherWealthSource` varchar(255) DEFAULT NULL,
  `otherFundsSource` varchar(255) DEFAULT NULL,
  `appStoreImageFilePath` varchar(150) DEFAULT NULL COMMENT 'APP开发者后台管理截图',
  `appDownloadUrl` varchar(150) DEFAULT NULL COMMENT 'APP在应用商店的下载链接',
  `middleShareholders` text COMMENT 'json:中间层股东',
  `franchiseLicenseName` varchar(150) DEFAULT NULL COMMENT '特许经营许可证名称',
  `franchiseLicenseNum` varchar(50) DEFAULT NULL COMMENT '特许经营许可证编号',
  `franchiseBiz` varchar(50) DEFAULT NULL COMMENT '特许经营内容/业务',
  `regulatorName` varchar(50) DEFAULT NULL COMMENT '监管机构名称',
  `franchiseLicenseEffectiveDate` varchar(50) DEFAULT NULL COMMENT '获得许可时间',
  `franchiseLicenseExpiryDate` varchar(50) DEFAULT NULL COMMENT '许可有效期',
  `companyIsListed` varchar(50) DEFAULT NULL COMMENT '是否为上市公司',
  `exchangeName` varchar(50) DEFAULT NULL COMMENT '交易所名称',
  `parentCompanyExist` varchar(50) DEFAULT NULL COMMENT '是否存在母公司',
  `parentCompanyIsListed` varchar(50) DEFAULT NULL COMMENT '母公司是否为上市公司',
  `parentCompanyExchangeName` varchar(255) DEFAULT NULL,
  `controlledByFinalParentCompany` varchar(255) DEFAULT NULL,
  `parentCompanyRegulatorRegion` varchar(255) DEFAULT NULL,
  `parentCompanyRegulatorName` varchar(255) DEFAULT NULL,
  `byGptSalesRank` varchar(50) DEFAULT NULL COMMENT '预计通过gpt月平均交易(HKD)',
  `supplyServiceType` varchar(50) DEFAULT NULL COMMENT '提供的服务类型',
  `supplyServiceTypeOther` varchar(50) DEFAULT NULL COMMENT '其他服务类型',
  `feeBear` varchar(155) DEFAULT NULL,
  `subMerchantId` varchar(50) DEFAULT NULL COMMENT '子商户商编',
  `status` varchar(50) DEFAULT NULL COMMENT '状态',
  `msg` text COMMENT '返回信息',
  `createdTime` varchar(255) DEFAULT NULL COMMENT '入网提交时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchantId` (`merchantId`) USING BTREE,
  KEY `subMerchantId` (`subMerchantId`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `email` (`email`) USING BTREE,
  KEY `requestId` (`requestId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='B2B-YEEPAY商户';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_curllog 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_curllog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text,
  `request` text NOT NULL COMMENT '请求参数',
  `content` text COMMENT '返回数据',
  `curltype` varchar(100) NOT NULL COMMENT '请求类型',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '接口描述',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=4521 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b宝付请求接口的日志表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_exchange_record 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_exchange_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1货贸 2服贸',
  `userNo` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payful的商户编号',
  `userReqNo` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '商户订单号',
  `buyCcy` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '买入币种',
  `buyAmount` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '买入金额',
  `sellCcy` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '卖出币种',
  `sellAmount` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '卖出金额',
  `closingDate` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交割日',
  `closingType` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交割类型：TOD:立即交割，账户余额一定要有资金才行 TOM:T+1日交割 SPOT:T+2日交割 默认：TOD',
  `direction` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交易方向：1-买入，2-卖出，根据此字段确认以买入或卖出金额为准',
  `tradeModel` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交割模式',
  `deliveryType` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT ' 交割方式：该字段只适用于预约兑换\r\n1、AUTO 自动交割\r\n2、MANUAL 手动交割\r\n不传默认 AUTO',
  `callBackUrl` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '回调地址',
  `exchangeId` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'GEP平台内部汇兑单号',
  `tradeRate` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交易汇率',
  `closingStatus` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交割状态：0-待交割，1-交割处理中，2-交割完成，3-交割失败，4-已违约，5-部分交割成功，6-已取消',
  `closingSuccessDate` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交割成功时间',
  `orderState` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '订单状态：0-待确认，1-已确认，2-已取消，3-已过期',
  `remarks` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
  `createTime` datetime DEFAULT NULL COMMENT '创建时间',
  `completeDt` datetime DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户换汇询价和申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_payeeusers 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_payeeusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(1) NOT NULL DEFAULT '1',
  `merchantId` int(11) NOT NULL COMMENT '商户号',
  `userNo` varchar(100) DEFAULT NULL,
  `paymentChannelType` varchar(50) NOT NULL DEFAULT '' COMMENT '付款方式',
  `accType` varchar(50) DEFAULT NULL COMMENT '收款人类型1-企业，2-个人',
  `countryCode` varchar(50) DEFAULT NULL COMMENT '银行国家/地区',
  `accountCcy` varchar(50) DEFAULT NULL COMMENT '账户币种',
  `accountName` varchar(255) DEFAULT NULL COMMENT '账户名称',
  `cardNo` varchar(255) DEFAULT NULL COMMENT '银行账号/IBAN',
  `swiftCode` varchar(100) DEFAULT NULL COMMENT 'SWIFTCODE',
  `middleSwiftCode` varchar(100) DEFAULT NULL COMMENT '中间行SWIFTCODE',
  `city` varchar(100) DEFAULT NULL COMMENT '城市',
  `payeeAddress` varchar(255) DEFAULT NULL COMMENT '收款人地址',
  `bankName` varchar(255) DEFAULT NULL COMMENT '银行名称',
  `clearingCode` varchar(50) DEFAULT NULL COMMENT 'BSB-收款银行代码',
  `branchCode` varchar(50) DEFAULT NULL COMMENT '银行分行代码',
  `province` varchar(100) DEFAULT NULL COMMENT '省/州',
  `zipCode` varchar(50) DEFAULT NULL COMMENT '邮编',
  `firstName` varchar(100) DEFAULT NULL COMMENT '名',
  `lastName` varchar(100) DEFAULT NULL COMMENT '姓',
  `cardType` varchar(50) DEFAULT NULL COMMENT '账户类型	10-DEPOSIT,11-SAVINGS,12-CHECKING',
  `phoneNum` varchar(50) DEFAULT NULL COMMENT '手机号',
  `bankBranchName` varchar(255) DEFAULT NULL COMMENT '支行名称',
  `orgName` varchar(255) DEFAULT NULL COMMENT '企业名称',
  `legalName` varchar(255) DEFAULT NULL COMMENT '法人名称',
  `legalNo` varchar(255) DEFAULT NULL COMMENT '法人身份证号',
  `legalCountryCode` varchar(255) DEFAULT NULL COMMENT '法人国籍',
  `isTaxFree` varchar(50) DEFAULT NULL COMMENT '是否特殊经济区客户',
  `taxFreeCode` varchar(255) DEFAULT NULL COMMENT 'taxFreeCode',
  `idNo` varchar(50) DEFAULT NULL COMMENT '身份证号',
  `payeeProvince` varchar(50) DEFAULT NULL COMMENT '居住省',
  `payeeCity` varchar(50) DEFAULT NULL COMMENT '居住市',
  `payeeArea` varchar(50) DEFAULT NULL COMMENT '居住区',
  `recordNo` varchar(255) DEFAULT NULL COMMENT '记录编号',
  `businessNo` varchar(255) DEFAULT NULL COMMENT '收款方主体编号',
  `state` varchar(50) DEFAULT NULL COMMENT '1-待审核2-可用3-不可用4-需补充材料',
  `orgExpand` text COMMENT '	企业备案信息',
  `createAt` varchar(100) DEFAULT NULL COMMENT 'bf创建时间',
  `remarks` varchar(255) DEFAULT NULL COMMENT 'bf备注',
  `auditRemark` varchar(255) DEFAULT NULL COMMENT '审核备注',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_pobopayeeusers 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_pobopayeeusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) NOT NULL DEFAULT '1' COMMENT '1货贸 2服贸',
  `merchantId` int(11) NOT NULL COMMENT '系统商户号',
  `userNo` varchar(100) DEFAULT NULL COMMENT '商户号',
  `payerType` int(11) DEFAULT NULL COMMENT '付款人类型1-个人，2-境内企业，3-非境内企业',
  `payerName` varchar(200) DEFAULT NULL COMMENT '付款人姓名',
  `payerEnName` varchar(200) DEFAULT NULL COMMENT '付款人英文名',
  `payerCountry` varchar(50) DEFAULT NULL COMMENT '付款人国家,三位国家编码 默认CHN',
  `payerCountryName` varchar(100) DEFAULT NULL COMMENT '国家中文名称',
  `payerIdType` varchar(50) DEFAULT NULL COMMENT '付款人证件类型,1-身份证，4-营业执照，5-企业注册号',
  `payerIdNo` varchar(200) DEFAULT NULL COMMENT '付款人证件号码',
  `idExpirationBegin` varchar(100) DEFAULT NULL COMMENT '证件签发日期',
  `idExpiration` varchar(100) DEFAULT NULL COMMENT '	证件有效期',
  `payerAddress` varchar(255) DEFAULT NULL COMMENT '付款人地址',
  `province` varchar(100) DEFAULT NULL COMMENT '付款人省/洲',
  `city` varchar(100) DEFAULT NULL COMMENT '付款人城市',
  `phone` varchar(100) DEFAULT NULL COMMENT '手机号',
  `zoneCode` varchar(100) DEFAULT NULL COMMENT '手机号区号',
  `firstName` varchar(100) DEFAULT NULL COMMENT '付款人英文名',
  `lastName` varchar(100) DEFAULT NULL COMMENT '付款人英文姓',
  `payeeName` varchar(200) DEFAULT NULL COMMENT '收款人名称',
  `payeeAddress` varchar(255) DEFAULT NULL COMMENT '收款人地址',
  `legalIdNo` varchar(200) DEFAULT NULL COMMENT '法人证件号',
  `legalName` varchar(200) DEFAULT NULL COMMENT '法人证件名称',
  `legalIdExpiration` varchar(100) DEFAULT NULL COMMENT '法人证件有效期',
  `managementIdNo` varchar(200) DEFAULT NULL COMMENT '董事证件号',
  `managementName` varchar(200) DEFAULT NULL COMMENT '董事名称',
  `managementIdExpiration` varchar(100) DEFAULT NULL COMMENT '	董事证件有效期',
  `establishDate` varchar(100) DEFAULT NULL COMMENT '成立日期',
  `receiptList` text COMMENT '受益人',
  `materialList` text COMMENT '	附件材料',
  `userReqNo` varchar(100) DEFAULT NULL COMMENT '请求单号',
  `orderId` varchar(100) DEFAULT NULL COMMENT '系统订单号',
  `callBackUrl` varchar(100) DEFAULT NULL COMMENT '回调地址',
  `poboRecordId` varchar(100) DEFAULT NULL COMMENT 'pobo记录编号',
  `status` int(11) DEFAULT NULL COMMENT '状态：-1：已删除，1-处理中，2-正常，3-失败',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `createAt` varchar(255) DEFAULT NULL COMMENT '创建时间',
  `created_time` int(11) DEFAULT NULL COMMENT '系统创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_rate 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` varchar(50) DEFAULT NULL COMMENT '商户号',
  `userNo` varchar(150) DEFAULT NULL COMMENT '收款商户id',
  `applyDate` varchar(50) DEFAULT NULL COMMENT '日期',
  `bizType` varchar(5) DEFAULT NULL COMMENT '业务类型：6：B2B结汇付款汇率,7：B2B国际分发汇率,8：B2B兑换汇率',
  `rateJson` text,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_applyDate` (`applyDate`) USING BTREE,
  KEY `idx_bizType` (`bizType`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='汇率表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_receivable 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_receivable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_type` tinyint(2) DEFAULT '1' COMMENT '入账类型，1货贸，2服贸',
  `userNo` varchar(64) NOT NULL COMMENT '签约Payful的唯一用户编号',
  `bankAccountNo` varchar(64) NOT NULL COMMENT '银行帐号',
  `amount` decimal(18,2) NOT NULL COMMENT '交易入账金额，已扣除手续费',
  `ccy` char(3) NOT NULL COMMENT '交易入账币种',
  `fee` decimal(18,2) NOT NULL COMMENT '交易入账手续费',
  `tradeTime` bigint(20) DEFAULT NULL COMMENT '入账成功时间，格式yyyyMMddHHmmss，仅入账成功时有值',
  `tradeNo` varchar(64) NOT NULL COMMENT '交易单号，唯一标识此笔入账',
  `tradeTotalAmount` decimal(18,2) NOT NULL COMMENT 'GEP实际收到的入账总金额 = amount + fee',
  `remitAccNo` varchar(64) NOT NULL COMMENT '汇款银行帐号',
  `remitAccName` varchar(255) NOT NULL COMMENT '汇款银行账户名',
  `status` tinyint(4) NOT NULL COMMENT '入账状态：1-未入账 2-已入账 3-入账中 4-入账失败 7-待补充材料 8-入账复核中 10-退款中 11-退款待确认 12-退款成功',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注信息，入账失败时显示原因',
  `auditorStatus` tinyint(4) NOT NULL COMMENT '-1审核暂停 1待审核 2审核成功 3审核失败',
  `userRemitAmount` decimal(18,2) NOT NULL COMMENT '到账金额',
  `userRemitCcy` varchar(16) NOT NULL COMMENT '到账币种',
  `remitReference` varchar(255) DEFAULT NULL COMMENT '汇款附言',
  `remitCountry` varchar(32) DEFAULT NULL COMMENT '付款方国家',
  `fileRiskStatus` tinyint(4) DEFAULT NULL COMMENT '快速入账关联材料审核状态，仅快速入账返回：10-无需审核 11-待审核 12-审核驳回 13-审核通过 14-审核拒绝',
  `orderEntryType` tinyint(4) NOT NULL COMMENT '入账类型：0-普通入账 1-快速入账',
  `orderRelatedStatus` tinyint(4) NOT NULL COMMENT '是否已关联订单：0-否 1-是',
  `orderList` varchar(8888) DEFAULT NULL COMMENT '关联订单信息：orderId  relationOrderAmt',
  `voucherFileId` int(11) DEFAULT NULL,
  `createTime` datetime DEFAULT NULL COMMENT '创建时间',
  `upTime` datetime DEFAULT NULL,
  `orderFileList` text COMMENT '补充材料：json',
  `bankName` varchar(100) DEFAULT '' COMMENT '银行名称',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_userNo_tradeTime` (`userNo`,`tradeTime`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='Payful入账通知表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_settleapply 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_settleapply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '商户号',
  `userNo` varchar(100) DEFAULT NULL COMMENT '用户编号',
  `userReqNo` varchar(150) DEFAULT NULL COMMENT '请求单号',
  `orderId` varchar(150) DEFAULT NULL COMMENT '付款订单号',
  `paymentCcy` char(3) DEFAULT NULL COMMENT '付款币种',
  `payeeCcy` char(3) DEFAULT NULL COMMENT '收款币种',
  `fixedModel` tinyint(2) DEFAULT NULL COMMENT '固定模式:1-固定付款金额，2-固定收款金额',
  `tradeAmount` varchar(255) DEFAULT NULL COMMENT '交易金额：交易金额不能小于等于0',
  `autoPayment` tinyint(2) DEFAULT NULL COMMENT '是否出款：默认：1-自动出款',
  `payeeAccountId` varchar(255) DEFAULT NULL COMMENT '收款人银行账户编号',
  `transferRemarks` varchar(255) DEFAULT NULL COMMENT '交易附言',
  `callBackUrl` varchar(255) DEFAULT NULL COMMENT '回调地址',
  `fastPayment` varchar(50) DEFAULT NULL COMMENT '极速付款标识:1：普通付款2：极速出款,默认不传是都是普通付款',
  `supplementMaterialList` text COMMENT '证明材料：json materialId：materialName',
  `applyDate` varchar(255) DEFAULT NULL COMMENT '申请时间',
  `accountName` varchar(255) DEFAULT NULL COMMENT '收款方名称',
  `cardNo` varchar(255) DEFAULT NULL COMMENT '收款方银行账号',
  `paymentAmountAndCcy` varchar(255) DEFAULT NULL COMMENT '付款金额和币种',
  `payeeAmountAndCcy` varchar(255) DEFAULT NULL COMMENT '收款到账金额及币种',
  `tradeRate` varchar(150) DEFAULT NULL COMMENT '交易汇率',
  `statusKey` tinyint(2) DEFAULT NULL COMMENT '交易状态:1-交易待处理, 2-交易处理中, 3-交易成功, 4-交易失败,6-下发失败，可重新发起出款,7-已取消 8-待补充材料',
  `status` varchar(150) DEFAULT NULL COMMENT '状态描述:1-交易待处理, 2-交易处理中, 3-交易成功, 4-交易失败,6-下发失败，可重新发起出款,7-已取消 8-待补充材料',
  `paymentSuccessDate` varchar(150) DEFAULT NULL COMMENT '付款完成时间',
  `auditStatus` tinyint(2) DEFAULT NULL COMMENT '审核状态：1-待审核，2-审核成功，3-审核失败 4-审核暂停',
  `businessNo` varchar(255) DEFAULT NULL COMMENT '收款人编号',
  `paymentAmount` varchar(255) DEFAULT NULL COMMENT '付款金额',
  `paymentFeeCcy` char(3) DEFAULT NULL COMMENT '付款手续费币种',
  `paymentFeeAmount` varchar(255) DEFAULT NULL COMMENT '付款手续费金额',
  `payeeAmount` varchar(255) DEFAULT NULL COMMENT '收款到账金额',
  `bankName` varchar(255) DEFAULT NULL COMMENT '银行名称',
  `paymentPurpose` tinyint(2) DEFAULT NULL COMMENT '付款用途：12-供货商 13-物流服务 14-分销推广 15-广告宣传 16-技术服务 17-留学 18-其他',
  `costBorne` char(3) DEFAULT NULL COMMENT '费用承担方式：SHA-非全额到账、OUR-全额到账、BEN-收款人承担',
  `errorFileBatchNo` varchar(255) DEFAULT NULL COMMENT '错误文件编号	：如果由于订单明细文件处理失败的情况，该字段有值，可以通过该文件编号通过下载接口查看具体的错误原因',
  `auditStatusDesc` varchar(255) DEFAULT NULL COMMENT '审核状态说明：系统自动审核，待审核，审核成功，审核失败 审核暂停',
  `remarks` text COMMENT '备注',
  `payeeInfo` text COMMENT '收款人信息用于代发',
  `type` tinyint(1) DEFAULT NULL COMMENT '结汇类型：0货贸/1服贸',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_applyDate` (`applyDate`) USING BTREE,
  KEY `idx_paymentSuccessDate` (`paymentSuccessDate`) USING BTREE,
  KEY `idx_userReqNo` (`userReqNo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='宝付结汇付款表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_settleapply_account 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_settleapply_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` varchar(32) DEFAULT NULL COMMENT '商户号',
  `userNo` varchar(32) DEFAULT NULL COMMENT 'GEP分配的用户号',
  `accType` varchar(20) DEFAULT NULL COMMENT '账户类型：1-对公，2-对私',
  `accountCcy` char(3) DEFAULT NULL COMMENT '账户币种',
  `accountName` varchar(150) DEFAULT NULL COMMENT '收款银行账户名称',
  `cardNo` varchar(150) DEFAULT NULL COMMENT '收款银行账户号',
  `bankName` varchar(150) DEFAULT NULL COMMENT '收款银行名称',
  `bankBranchName` varchar(150) DEFAULT NULL COMMENT '收款银行支行名称',
  `countryCode` char(3) DEFAULT NULL COMMENT '收款银行国家编码',
  `swiftCode` varchar(20) DEFAULT NULL COMMENT '收款银行swift Code',
  `payeeAddress` varchar(255) DEFAULT NULL COMMENT '收款人地址',
  `bankAddress` varchar(150) DEFAULT NULL COMMENT '开户行地址/收款银行地址',
  `recordNo` varchar(20) DEFAULT NULL COMMENT '记录编号',
  `clearingCode` varchar(255) DEFAULT NULL COMMENT '收款银行代码',
  `middleSwiftCode` varchar(20) DEFAULT NULL COMMENT '中转行SwiftCode',
  `middleBankName` varchar(150) DEFAULT NULL COMMENT '中转行银行名称',
  `middleBankCountry` char(3) DEFAULT NULL COMMENT '中转行国家',
  `createAt` varchar(50) DEFAULT NULL COMMENT '申请时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_accType` (`accType`) USING BTREE,
  KEY `idx_accountCcy` (`accountCcy`) USING BTREE,
  KEY `idx_accountName` (`accountName`) USING BTREE,
  KEY `idx_cardNo` (`cardNo`) USING BTREE,
  KEY `idx_bankName` (`bankName`) USING BTREE,
  KEY `idx_bankBranchName` (`bankBranchName`) USING BTREE,
  KEY `idx_countryCode` (`countryCode`) USING BTREE,
  KEY `idx_swiftCode` (`swiftCode`) USING BTREE,
  KEY `idx_taxfreeCode` (`payeeAddress`) USING BTREE,
  KEY `idx_bankAddress` (`bankAddress`) USING BTREE,
  KEY `idx_recordNo` (`recordNo`) USING BTREE,
  KEY `idx_clearingCode` (`clearingCode`) USING BTREE,
  KEY `idx_middleSwiftCode` (`middleSwiftCode`) USING BTREE,
  KEY `idx_middleBankName` (`middleBankName`) USING BTREE,
  KEY `idx_middleBankCountry` (`middleBankCountry`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='宝付结汇提现账户表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_settleapply_apply 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_settleapply_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` varchar(32) DEFAULT NULL COMMENT '商户号',
  `userNo` varchar(32) DEFAULT NULL COMMENT 'GEP分配的用户号',
  `file` varchar(255) DEFAULT NULL COMMENT '文件',
  `status` varchar(50) DEFAULT NULL COMMENT '0待审核，1通过，2不予通过',
  `createTime` varchar(50) DEFAULT NULL COMMENT '创建时间',
  `successTime` varchar(50) DEFAULT NULL COMMENT '审核时间',
  `name` varchar(255) DEFAULT NULL COMMENT '文件名',
  `requestId` varchar(50) DEFAULT NULL,
  `remark` text,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_file` (`file`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_createTime` (`createTime`) USING BTREE,
  KEY `idx_successTime` (`successTime`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='宝付结汇申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_settleapply_cust 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_settleapply_cust` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` varchar(32) DEFAULT NULL COMMENT '商户号',
  `userNo` varchar(32) DEFAULT NULL COMMENT 'GEP分配的用户号',
  `areaCode` varchar(20) DEFAULT NULL COMMENT '住所/经营场所代码',
  `areaCodeDesc` varchar(50) DEFAULT NULL COMMENT '住所/经营场所代码描述',
  `economyId` varchar(20) DEFAULT NULL COMMENT '经济类型代码',
  `economyIdDesc` varchar(50) DEFAULT NULL COMMENT '经济类型代码描述',
  `industryId` varchar(20) DEFAULT NULL COMMENT '行业属性代码',
  `industryIdDesc` varchar(255) DEFAULT NULL COMMENT '行业属性代码描述',
  `isTaxfree` tinyint(1) DEFAULT NULL COMMENT '是否特殊经济区内企业:0-是，1-否',
  `isTaxfreeDesc` varchar(255) DEFAULT NULL COMMENT '是否特殊经济区内企业描述',
  `taxfreeCode` char(2) DEFAULT NULL COMMENT '特殊经济区编码',
  `taxfreeCodeDesc` varchar(255) DEFAULT NULL COMMENT '特殊经济区代码描述',
  `linkman` varchar(20) DEFAULT NULL COMMENT '法人，联系人	',
  `linkmanTel` varchar(20) DEFAULT NULL COMMENT '联系人电话',
  `orgAddress` varchar(255) DEFAULT NULL COMMENT '企业地址',
  `orgCode` varchar(20) DEFAULT NULL COMMENT '组织机构编码:9位编码,即统一社会信用代码的9-17位',
  `orgName` varchar(255) DEFAULT NULL COMMENT '组织机构名称:企业注册名称',
  `postCode` varchar(20) DEFAULT NULL COMMENT '邮编:企业注册地邮编',
  `license` varchar(50) DEFAULT NULL COMMENT '营业执照文件(DFS编号):特殊结汇渠道对公备案必填,先调用文件上传接口获取fileId，然后赋值该字段',
  `legalIdFront` varchar(50) DEFAULT NULL COMMENT '法人身份证照片正面(DFS编号):特殊结汇渠道对公备案必填,先调用文件上传接口获取fileId，然后赋值该字段',
  `legalIdReverse` varchar(50) DEFAULT NULL COMMENT '法人身份证照片反面(DFS编号):特殊结汇渠道对公备案必填,先调用文件上传接口获取fileId，然后赋值该字段',
  `createAtStr` varchar(50) DEFAULT NULL COMMENT '创建时间',
  `recordId` varchar(50) DEFAULT NULL COMMENT '记录ID',
  `status` varchar(50) DEFAULT NULL COMMENT '状态:0-待备案，1-成功，2-失败，3-备案中',
  `statusDesc` varchar(50) DEFAULT NULL COMMENT '状态描述:0-待备案，1-成功，2-失败，3-备案中',
  `remarks` text COMMENT '备注:备注描述，如果备案失败时会把原因显示在该字段中',
  `industryType` varchar(50) DEFAULT NULL COMMENT '行业类型',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_areaCode` (`areaCode`) USING BTREE,
  KEY `idx_areaCodeDesc` (`areaCodeDesc`) USING BTREE,
  KEY `idx_economyId` (`economyId`) USING BTREE,
  KEY `idx_economyIdDesc` (`economyIdDesc`) USING BTREE,
  KEY `idx_industryId` (`industryId`) USING BTREE,
  KEY `idx_industryIdDesc` (`industryIdDesc`) USING BTREE,
  KEY `idx_isTaxfree` (`isTaxfree`) USING BTREE,
  KEY `idx_isTaxfreeDesc` (`isTaxfreeDesc`) USING BTREE,
  KEY `idx_taxfreeCode` (`taxfreeCode`) USING BTREE,
  KEY `idx_taxfreeCodeDesc` (`taxfreeCodeDesc`) USING BTREE,
  KEY `idx_linkman` (`linkman`) USING BTREE,
  KEY `idx_linkmanTel` (`linkmanTel`) USING BTREE,
  KEY `idx_orgAddress` (`orgAddress`) USING BTREE,
  KEY `idx_orgCode` (`orgCode`) USING BTREE,
  KEY `idx_orgName` (`orgName`) USING BTREE,
  KEY `idx_postCode` (`postCode`) USING BTREE,
  KEY `idx_license` (`license`) USING BTREE,
  KEY `idx_legalIdFront` (`legalIdFront`) USING BTREE,
  KEY `idx_legalIdReverse` (`legalIdReverse`) USING BTREE,
  KEY `idx_createAtStr` (`createAtStr`) USING BTREE,
  KEY `idx_recordId` (`recordId`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_statusDesc` (`statusDesc`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='宝付结汇对公企业信息备案表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_settleapply_payee 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_settleapply_payee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '商户号',
  `userNo` varchar(100) DEFAULT NULL COMMENT '用户编号',
  `userReqNo` varchar(150) DEFAULT NULL COMMENT '请求单号',
  `partnerType` tinyint(2) DEFAULT NULL COMMENT '收款人类型:合作收款人类型 2-本企业 5-第三方个人 6-第三方企业',
  `nameType` tinyint(2) DEFAULT NULL COMMENT '干系人类型:当收款人类型为本企业时，必填，选择的干系人类型 1-本企业 2-本企业的法人 3-本企业的受益人',
  `name` varchar(255) DEFAULT NULL COMMENT '收款人名称:结汇出款到银行账户或卡号的名称',
  `orgCode` varchar(255) DEFAULT NULL COMMENT '组织机构号:当收款人类型为第三方企业时，必填',
  `industryId` char(4) DEFAULT NULL COMMENT '行业属性:当收款人类型为第三方企业时，必填',
  `economyId` char(3) DEFAULT NULL COMMENT '经济属性:当收款人类型为第三方企业时，必填',
  `taxfreeCode` char(3) DEFAULT NULL COMMENT '经济特区:当收款人类型为第三方企业时，是特殊经济区必填',
  `linkMan` varchar(255) DEFAULT NULL COMMENT '联系人:当收款人类型为第三方企业时，必填 企业联系人',
  `linkMobile` char(15) DEFAULT NULL COMMENT '联系人电话:当收款人类型为第三方企业时，必填',
  `postCode` char(6) DEFAULT NULL COMMENT '邮编:当收款人类型为第三方企业时，必填 企业所在地',
  `legalName` varchar(255) DEFAULT NULL COMMENT '法人名称:当收款人类型为第三方企业时，必填 企业的法人',
  `legalIdNo` varchar(255) DEFAULT NULL COMMENT '证件号码:当收款人类型为第三方个人、第三方企业时，如果KYC董事/法人存在中国大陆个人时，身份证可以选填;其他 第三方个人时为收款人的身份证证件号码;其他 第三方企业时为法人的身份证证件号码',
  `province` varchar(50) DEFAULT NULL COMMENT '省份代码:当收款人类型为第三方企业时，必填 企业经营场所所在省份',
  `city` varchar(50) DEFAULT NULL COMMENT '城市代码:当收款人类型为第三方企业时，必填 企业经营场所所在城市',
  `area` varchar(50) DEFAULT NULL COMMENT '区县代码:当收款人类型为第三方企业时，必填 企业经营场所所在区县',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址:当收款人类型为第三方企业时，必填 企业经营场所详细地址',
  `bankName` varchar(255) DEFAULT NULL COMMENT '银行名称:结汇出款到账的银行名称',
  `cardNo` varchar(255) DEFAULT NULL COMMENT '账号或卡号:结汇出款到账的境内银行账号（对公）或卡号（对私）',
  `bankBranchName` varchar(255) DEFAULT NULL COMMENT '支行名称:收款人开户行的支行名称',
  `bankProvince` varchar(50) DEFAULT NULL COMMENT '省份代码:收款人开户行的省份，格式必须是code_name',
  `bankCity` varchar(50) DEFAULT NULL COMMENT '城市代码:收款人 开户行的城市，格式必须是code_name',
  `bankAddress` varchar(255) DEFAULT NULL COMMENT '开户行地址:收款人开户行的详细地址',
  `bankNameOfLegal` varchar(255) DEFAULT NULL COMMENT '银行名称:当收款人类型为第三方企业时，此参数是添加法人到账银行名称 可为空',
  `cardNoOfLegal` varchar(255) DEFAULT NULL COMMENT '银行卡号:当收款人类型为第三方企业时，此参数是添加法人境内到账银行卡号 可为空',
  `bankLegalProvince` varchar(50) DEFAULT NULL COMMENT '开户行的省份代码:当收款人类型为第三方企业时，如果法人到账银行名称不为空 此参数必填,开户行的省份，格式必须是code_name',
  `bankLegalCity` varchar(50) DEFAULT NULL COMMENT '开户行的城市代码:当收款人类型为第三方企业时，如果法人到账银行名称不为空 此参数必填，开户行的城市，格式必须是code_name',
  `accountDocument` text COMMENT '证明文件id:当收款人类型为第三方企业、第三方个人时，必填,上传文件接口返回的id（上传文件接口的fileType：B2B_OPEN_ACC_FILE）',
  `callBackUrl` varchar(255) DEFAULT NULL COMMENT '回调地址',
  `recordNo` varchar(255) DEFAULT NULL COMMENT '银行账户编号：收款人银行账户编号',
  `partnerNo` varchar(255) DEFAULT NULL COMMENT '收款人编号：唯一编号',
  `accountCcy` varchar(255) DEFAULT NULL COMMENT '账户币种：默认：CNY',
  `accountName` varchar(255) DEFAULT NULL COMMENT '账户名称：持卡人姓名或企业账户名称',
  `createAt` char(50) DEFAULT NULL COMMENT '创建时间：格式：yyyy-MM-dd HH:mm:ss',
  `idNo` varchar(150) DEFAULT NULL COMMENT '证件号：身份证号或企业组织机构号',
  `state` varchar(150) DEFAULT NULL COMMENT '状态：-1-删除;1-待审核;2-审核通过;3-审核未通过',
  `remark` varchar(150) DEFAULT NULL COMMENT '备注：审核未通过的备注',
  `enableStatus` tinyint(2) DEFAULT NULL COMMENT '启用禁用状态:1-启用 0-禁用',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_userReqNo` (`userReqNo`) USING BTREE,
  KEY `idx_partnerType` (`partnerType`) USING BTREE,
  KEY `idx_nameType` (`nameType`) USING BTREE,
  KEY `idx_state` (`state`) USING BTREE,
  KEY `idx_createAt` (`createAt`) USING BTREE,
  KEY `idx_enableStatus` (`enableStatus`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='宝付结汇付款人表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_settleapply_quota 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_settleapply_quota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '商户号',
  `userNo` varchar(100) DEFAULT NULL COMMENT '用户编号',
  `surplusQuota` varchar(150) DEFAULT NULL COMMENT '垫资额度：剩余额度',
  `status` varchar(150) DEFAULT NULL COMMENT '开通状态：1-待开通，2-正在审批中，3-已开通',
  `createAt` char(50) DEFAULT NULL COMMENT '开通时间',
  `remarks` tinyint(2) DEFAULT NULL COMMENT '备注信息',
  `callBackUrl` varchar(255) DEFAULT NULL COMMENT '回调地址',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_createAt` (`createAt`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='结汇付款垫资额度表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_settleapply_transfer 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_settleapply_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '商户号',
  `userNo` varchar(100) DEFAULT NULL COMMENT '用户编号',
  `userReqNo` varchar(150) DEFAULT NULL COMMENT '请求单号',
  `payeeName` varchar(150) DEFAULT NULL COMMENT '收款银行账户名称',
  `payeeAccNo` varchar(150) DEFAULT NULL COMMENT '收款银行账户号',
  `payeeType` varchar(150) DEFAULT NULL COMMENT '收款人类型：1-对公 2-对私',
  `payeeIdNo` varchar(150) DEFAULT NULL COMMENT '收款人证件号:对私时填写收款人的身份证号码,对公时填写收款人的组织机构代码',
  `bankProvince` varchar(150) DEFAULT NULL COMMENT '对公时开户行省份必填',
  `bankCity` varchar(150) DEFAULT NULL COMMENT '对公时开户行城市必填',
  `bankName` varchar(255) DEFAULT NULL COMMENT '银行名称',
  `bankBranchName` varchar(255) DEFAULT NULL COMMENT '对公时开户行支行名称必填',
  `transferAmt` varchar(255) DEFAULT NULL COMMENT '转账的金额，单位：元',
  `transferRemarks` varchar(255) DEFAULT NULL COMMENT '转账备注',
  `deductionFeeCcy` char(3) DEFAULT NULL COMMENT '手续费出款币种',
  `applyDate` varchar(50) DEFAULT NULL COMMENT '申请时间',
  `remarks` text COMMENT '备注信息:如果交易失败的情况，该字段会显示失败原因',
  `transferState` tinyint(1) DEFAULT NULL COMMENT '转账状态: 1-交易待处理，2-交易处理中，3-交易成功，4-交易失败，5-交易异常',
  `callBackUrl` varchar(255) DEFAULT NULL COMMENT '回调地址',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_userNo` (`userNo`) USING BTREE,
  KEY `idx_transferState` (`transferState`) USING BTREE,
  KEY `idx_applyDate` (`applyDate`) USING BTREE,
  KEY `idx_userReqNo` (`userReqNo`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='宝付结汇代发表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_tradeorder 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_tradeorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '系统',
  `userNo` varchar(255) NOT NULL COMMENT '用户号',
  `voucherFileId` varchar(255) NOT NULL COMMENT '证明文件ID',
  `buyName` varchar(200) NOT NULL COMMENT '买家姓名长度不能超过128位',
  `orderAmt` decimal(19,2) NOT NULL COMMENT '订单总金额',
  `orderCcy` varchar(50) NOT NULL COMMENT '订单币种',
  `contractNo` varchar(100) NOT NULL COMMENT '商户合同号',
  `userTransDate` varchar(100) NOT NULL COMMENT '合同时间',
  `countryCode` varchar(50) NOT NULL COMMENT '贸易国家地区',
  `storeUrl` varchar(200) NOT NULL COMMENT '网址',
  `commodityList` text NOT NULL COMMENT '商品信息',
  `deliveryStatus` int(11) NOT NULL COMMENT '发货状态： 1-已发货 2-未发货 -1 无 ；无需结汇可选择无 ；其它时只能选择已发货/未发货',
  `expectDeliveryDate` varchar(50) DEFAULT NULL COMMENT '发货状态为未发货必填,格式：yyyy-MM-dd,无需结汇非必填',
  `deliveryDate` varchar(50) DEFAULT NULL COMMENT '发货状态为已发货必填,格式：yyyy-MM-dd；无需结汇非必填',
  `logisticsCompanyNumber` varchar(255) DEFAULT NULL COMMENT '发货状态为已发货必填 ；无需结汇非必填',
  `logisticsNumber` varchar(255) DEFAULT NULL COMMENT '发货状态为已发货必填 ；物流单号长度不能超过64位 ；无需结汇非必填',
  `correlationOrderAmt` varchar(255) DEFAULT '0' COMMENT '可关联订单金额',
  `settleFlag` varchar(50) DEFAULT NULL COMMENT '结汇类型：0-无需结汇 1-普通结汇',
  `callBackUrl` varchar(255) DEFAULT NULL COMMENT '异步回调通知地址',
  `orderId` varchar(100) DEFAULT NULL COMMENT '平台订单号',
  `merchantFileList` text COMMENT '文件名称数组',
  `userRemarks` varchar(255) DEFAULT NULL COMMENT '对客备注',
  `state` varchar(50) DEFAULT '1' COMMENT '订单状态：-1:已作废 1未关联 2：部分关联 3:已关联 9:不可用',
  `expDeliveryDateUpStatus` varchar(255) DEFAULT NULL COMMENT '预计发货时间只能修改一次',
  `logisticsStatus` int(11) DEFAULT NULL COMMENT '物流状态：-1 初始(无)， 10-待补传材料 11-已补充（已补传待审核 ）12-重新补传材料 13-审核通过',
  `createAt` varchar(50) DEFAULT NULL COMMENT '创建时间',
  `fileList` text COMMENT '附件材料信息',
  `created_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '作废备注',
  `remarks` varchar(255) DEFAULT NULL COMMENT '审核失败原因',
  `status` varchar(255) DEFAULT NULL COMMENT '未发货物流补传:10-待补传材料 12-补传材料驳回 13-审核通过\r\n\r\n材料校验结果:0-校验通过 3-校验失败',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_crossborderpayful_uploadfile 结构
CREATE TABLE IF NOT EXISTS `doopsun_crossborderpayful_uploadfile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL,
  `fileId` varchar(50) NOT NULL COMMENT '附件ID',
  `fileMd5encryption` varchar(100) DEFAULT NULL,
  `fileName` varchar(100) DEFAULT NULL,
  `userNo` varchar(100) DEFAULT NULL,
  `uploadUrl` varchar(100) DEFAULT NULL COMMENT '系统路径',
  `created_time` datetime DEFAULT NULL COMMENT '上传时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_dishonor 结构
CREATE TABLE IF NOT EXISTS `doopsun_dishonor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dishonor_order` varchar(40) NOT NULL COMMENT '商户订单号',
  `dishonor_doopsun_orderid` varchar(155) NOT NULL DEFAULT '0' COMMENT '系统流水号',
  `dishonor_cash` double(9,2) NOT NULL COMMENT '拒付金额',
  `dishonor_time` int(10) NOT NULL COMMENT '拒付时间',
  `dishonor_carnum` varchar(30) NOT NULL COMMENT '拒付卡号',
  `dishonor_money` double(9,2) NOT NULL COMMENT '收单金额',
  `dishonor_type` varchar(100) NOT NULL COMMENT '拒付类型',
  `dishonor_reason` varchar(200) NOT NULL COMMENT '拒付原因',
  `dishonor_url` varchar(100) NOT NULL COMMENT '交易网址',
  `dishonor_findnum` int(30) NOT NULL DEFAULT '0' COMMENT '查询单号',
  `dishonor_merchantid` int(11) NOT NULL COMMENT '商户号',
  `dishonor_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '返还状态1为已返还0为未返还',
  `dishonor_style` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1为真实拒付，2为假拒付',
  `dishonor_punish` double(9,2) DEFAULT NULL COMMENT '拒付处罚',
  `dishonor_email` varchar(255) DEFAULT NULL COMMENT '拒付邮箱',
  `dishonor_accessurl_id` int(11) DEFAULT NULL COMMENT '拒付网址id',
  `order_time` int(11) DEFAULT NULL COMMENT '下单时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `dishonor_merchantid` (`dishonor_merchantid`) USING BTREE,
  KEY `dishonor_order` (`dishonor_order`) USING BTREE,
  KEY `dishonor_doopsun_orderid` (`dishonor_doopsun_orderid`) USING BTREE,
  KEY `dishonor_merchantid_2` (`dishonor_merchantid`) USING BTREE,
  KEY `dishonor_accessurl_id` (`dishonor_accessurl_id`) USING BTREE,
  KEY `dishonor_style` (`dishonor_style`) USING BTREE,
  KEY `dishonor_status` (`dishonor_status`) USING BTREE,
  KEY `dishonor_type` (`dishonor_type`) USING BTREE,
  KEY `dishonor_findnum` (`dishonor_findnum`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='拒付订单';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_exchangerole 结构
CREATE TABLE IF NOT EXISTS `doopsun_exchangerole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '内容',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `role_name` varchar(255) NOT NULL COMMENT '规则名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_fee 结构
CREATE TABLE IF NOT EXISTS `doopsun_fee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channelid` int(11) DEFAULT NULL COMMENT '通道iid',
  `channelname` varchar(255) DEFAULT NULL COMMENT '通道名称',
  `feejson` text COMMENT '费用信息',
  `cashback` text COMMENT '返现信息',
  `createdtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `createduser` varchar(255) DEFAULT NULL COMMENT '创建用户',
  `updatetime` int(11) DEFAULT NULL COMMENT '修改时间',
  `updateuser` varchar(255) DEFAULT NULL COMMENT '修改用户',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1正常',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_kkpayfuldoopsun 结构
CREATE TABLE IF NOT EXISTS `doopsun_kkpayfuldoopsun` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(20) NOT NULL DEFAULT 'MERCH' COMMENT '业务类型',
  `version` varchar(10) NOT NULL DEFAULT 'V2' COMMENT '版本号(V1、V2...)',
  `request_id` varchar(32) NOT NULL DEFAULT '' COMMENT '请求号，请求方系统内唯一',
  `merch_id` varchar(20) NOT NULL DEFAULT '' COMMENT '商户id(通知返回)',
  `status` varchar(20) NOT NULL DEFAULT 'INIT' COMMENT '认证状态',
  `msg` varchar(255) NOT NULL DEFAULT '' COMMENT '结果说明',
  `risk_level` varchar(20) NOT NULL DEFAULT '' COMMENT '风险等级',
  `phone_prefix` varchar(8) NOT NULL DEFAULT '' COMMENT '联系人区号',
  `phone` varchar(64) NOT NULL DEFAULT '' COMMENT '联系人电话',
  `email` varchar(64) NOT NULL DEFAULT '' COMMENT '联系人邮箱',
  `region` varchar(2) NOT NULL DEFAULT '' COMMENT '企业注册地区代码',
  `ent_name` varchar(128) NOT NULL DEFAULT '' COMMENT '企业名称',
  `ent_name_en` varchar(128) NOT NULL DEFAULT '' COMMENT '企业英文名称',
  `found_date` date DEFAULT NULL COMMENT '成立日期',
  `registered_currency` varchar(3) NOT NULL DEFAULT '' COMMENT '注册资本币种',
  `registered_capital` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '注册资本',
  `legal_rep_name` varchar(128) NOT NULL DEFAULT '' COMMENT '法人姓名',
  `uni_social_credit` varchar(32) NOT NULL DEFAULT '' COMMENT '统一社会信用代码',
  `main_industry` varchar(64) NOT NULL DEFAULT '' COMMENT '主营行业code',
  `sub_industry` text COMMENT '子行业JSON',
  `industry` int(11) NOT NULL DEFAULT '0' COMMENT '行业字典值',
  `employee_number` varchar(30) NOT NULL DEFAULT '' COMMENT '员工人数等级',
  `export_country` text COMMENT '主要出口国家JSON',
  `export_type` text COMMENT '出口类型JSON',
  `trade_volume` varchar(30) NOT NULL DEFAULT '' COMMENT '月贸易额等级',
  `website` varchar(200) NOT NULL DEFAULT '' COMMENT '公司网站',
  `business_models` varchar(100) NOT NULL DEFAULT '' COMMENT '主要业务类型',
  `stakeholder_list` text COMMENT '企业关联人列表JSON',
  `data` text COMMENT '通知data节点JSON',
  `authorize_link` varchar(256) NOT NULL DEFAULT '' COMMENT '授权链接(申请返回)',
  `created_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间戳',
  `updated_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间戳',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uniq_request_id` (`request_id`) USING BTREE,
  KEY `idx_merch_id` (`merch_id`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_created_time` (`created_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_kkpayfuldoopsun_curllog 结构
CREATE TABLE IF NOT EXISTS `doopsun_kkpayfuldoopsun_curllog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text,
  `request` text NOT NULL COMMENT '请求参数',
  `content` text COMMENT '返回数据',
  `curltype` varchar(100) NOT NULL COMMENT '请求类型',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '接口描述',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=27760 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b请求接口的日志表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_logistics 结构
CREATE TABLE IF NOT EXISTS `doopsun_logistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logisticsname` varchar(20) DEFAULT NULL COMMENT '物流名称',
  `logisticsnumber` varchar(50) DEFAULT NULL COMMENT '物流单号',
  `logisticspic` varchar(50) DEFAULT NULL COMMENT '物流凭证，上传图片',
  `logisticstime` int(11) NOT NULL DEFAULT '0' COMMENT '上传时间',
  `orderid` varchar(50) DEFAULT NULL COMMENT '订单号',
  `merchantid` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `oid` int(11) NOT NULL DEFAULT '0',
  `clean` smallint(1) DEFAULT '0' COMMENT '结算',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `oid` (`oid`) USING BTREE,
  KEY `merchantid` (`merchantid`) USING BTREE,
  KEY `clean` (`clean`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='物流信息';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_merchant_cert 结构
CREATE TABLE IF NOT EXISTS `doopsun_merchant_cert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '回调链接',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否认证',
  `add_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_merchant_power 结构
CREATE TABLE IF NOT EXISTS `doopsun_merchant_power` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rulename` varchar(255) DEFAULT NULL COMMENT '权限名称',
  `power` text COMMENT '权限',
  `addtime` int(11) DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_merchants 结构
CREATE TABLE IF NOT EXISTS `doopsun_merchants` (
  `merchantId` int(11) NOT NULL AUTO_INCREMENT COMMENT '商户id',
  `loginName` char(16) NOT NULL COMMENT '登录名',
  `password` varchar(150) NOT NULL COMMENT '密码',
  `withdraw_password` varchar(150) DEFAULT '' COMMENT '支付密码',
  `email` varchar(150) DEFAULT NULL COMMENT '用户邮箱',
  `role` tinyint(2) NOT NULL DEFAULT '1' COMMENT '用户角色。1个人2企业',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '账号状态：0正常，1待审核2禁用',
  `inTime` int(11) DEFAULT NULL COMMENT '加入时间',
  `signtime` varchar(15) DEFAULT NULL COMMENT '签约时间',
  `endtime` varchar(15) DEFAULT NULL COMMENT '到期时间',
  `atmpassword` char(8) DEFAULT NULL COMMENT '提款密码',
  `updateTime` int(11) DEFAULT NULL COMMENT '最后修改信息时间',
  `transaction_url` varchar(100) DEFAULT NULL,
  `sid` smallint(5) DEFAULT '0',
  `filter_rate` int(11) NOT NULL DEFAULT '5' COMMENT '过滤比例',
  `is_abate` int(11) DEFAULT '1' COMMENT '是否失效0是1否',
  `abate_time` int(11) NOT NULL DEFAULT '0' COMMENT '失效时间',
  `exchange_id` int(11) NOT NULL DEFAULT '1' COMMENT '汇率规则ID',
  `promise_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商户承诺金额',
  `is_filtration` int(1) NOT NULL DEFAULT '1' COMMENT '是否过滤',
  `name` varchar(200) DEFAULT '' COMMENT '姓名',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `mobile` varchar(50) DEFAULT NULL COMMENT '联系方式',
  `commission_rate` decimal(5,5) NOT NULL DEFAULT '0.00000' COMMENT '提成比例',
  `passwordnew` varchar(150) DEFAULT NULL COMMENT '新系统密码',
  `sid2` smallint(5) NOT NULL DEFAULT '0' COMMENT '销售人员2',
  `commission_rate2` decimal(5,5) NOT NULL DEFAULT '0.00000' COMMENT '提成比例2',
  `is_logistics` int(1) DEFAULT '1' COMMENT '1上传，2不上传',
  `is_checkurl` int(1) DEFAULT '1' COMMENT '是否审核网址1审核，2不审核',
  `settlement_model` int(1) DEFAULT '1' COMMENT '1:T(排除节假日),2:D(包含节假日)',
  `power_id` int(10) DEFAULT '1' COMMENT '权限ID',
  `amountlimit` int(11) NOT NULL DEFAULT '0',
  `isDeduction` int(1) DEFAULT '1' COMMENT '拒付退款是否计入结算，1是2否',
  `vsuccessrate` int(1) NOT NULL DEFAULT '2' COMMENT 'v卡成功率比例',
  `vfilterrate` int(1) DEFAULT '2' COMMENT 'v卡失败过滤比例',
  `successrate` decimal(3,2) NOT NULL DEFAULT '1.00' COMMENT '商户总成功率调整',
  `vwayedid` int(11) DEFAULT '0' COMMENT 'v卡通道',
  `mwayedid` int(11) DEFAULT '0' COMMENT 'M卡通道',
  `merclass` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1已授权2未授权3无品牌',
  `mtype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '收款工具 1 b2c 2 b2b 3结汇收款 4平台收款',
  `idcardtype` tinyint(4) NOT NULL DEFAULT '0' COMMENT '证件类型',
  `trade` tinyint(1) DEFAULT NULL COMMENT '交易类型',
  `webtype` smallint(6) DEFAULT NULL COMMENT '建站工具',
  `protype` smallint(6) DEFAULT NULL COMMENT '推广方式',
  `idcardimg` varchar(255) DEFAULT NULL COMMENT '证件图',
  `source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '来源：直接注册0，销售分享链接：1，代理分享链接：2',
  `registe_code` varchar(100) DEFAULT '' COMMENT '商户注册时的销售和代理编码',
  `doopsun_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '商户类型：1:信用卡商户,2:虚拟卡商户',
  `is_view` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可以查看费用，0不可以，1：可以',
  `is_warning` tinyint(1) DEFAULT NULL COMMENT '是否预警，0：不预警，1：预警',
  `thoroughfare` int(11) DEFAULT '1' COMMENT '通道',
  `is_online` tinyint(11) DEFAULT '0' COMMENT '是否正式，0测试，1正式',
  `dl_commission_rate` decimal(5,5) DEFAULT '0.00000' COMMENT '代理提成比例底线',
  `adduserid` int(11) DEFAULT '0' COMMENT '添加人员id',
  `logistics_days` varchar(50) DEFAULT '0' COMMENT '上传物流失效天数',
  `is_new` tinyint(1) DEFAULT '1' COMMENT '1新用户 0老用户',
  `withdraw_exchange_id` int(11) DEFAULT NULL,
  `register_ip` varchar(50) DEFAULT '' COMMENT '注册IP',
  `channel_id` int(11) DEFAULT '0' COMMENT '通道id',
  PRIMARY KEY (`merchantId`) USING BTREE,
  KEY `sid2` (`sid2`) USING BTREE,
  KEY `sid` (`sid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1095833922 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_migrations 结构
CREATE TABLE IF NOT EXISTS `doopsun_migrations` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_new 结构
CREATE TABLE IF NOT EXISTS `doopsun_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '标题',
  `attr` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:普通-2：置顶-3：推荐-4：最火',
  `content` text NOT NULL COMMENT '内容',
  `time` int(10) NOT NULL COMMENT '时间',
  `merchantId` int(11) DEFAULT '0' COMMENT '商户id',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `attr` (`attr`) USING BTREE,
  KEY `title` (`title`) USING BTREE,
  KEY `time` (`time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='新闻';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_newread 结构
CREATE TABLE IF NOT EXISTS `doopsun_newread` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `merchantid` int(8) NOT NULL COMMENT '商户号',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否查看',
  `new_id` int(10) NOT NULL COMMENT '新闻id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_news 结构
CREATE TABLE IF NOT EXISTS `doopsun_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) DEFAULT '',
  `text` longtext,
  `cid` int(11) DEFAULT '0',
  `createtime` datetime DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `kwd` varchar(255) DEFAULT NULL,
  `des` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `uid` int(11) DEFAULT '0',
  `hot` tinyint(1) DEFAULT '2',
  `sort` smallint(6) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_order 结构
CREATE TABLE IF NOT EXISTS `doopsun_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchantid` int(11) NOT NULL COMMENT '商户号',
  `orderid` varchar(255) NOT NULL DEFAULT '' COMMENT '商户订单号',
  `doopsun_orderid` varchar(55) DEFAULT '' COMMENT '系统流水号',
  `productname` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '商品名称',
  `currency` varchar(25) DEFAULT NULL COMMENT '币种',
  `orderamount` decimal(10,2) DEFAULT NULL COMMENT '标价金额',
  `status` tinyint(2) DEFAULT '2' COMMENT '交易状态，1成功，2失败',
  `orderdate` datetime DEFAULT NULL COMMENT '下单时间',
  `doopsun_orderdate` int(50) DEFAULT NULL COMMENT '交易时间',
  `result` text COMMENT '风控结果',
  `fail_content` text NOT NULL,
  `request_content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `cardnum` varchar(20) NOT NULL COMMENT '交易卡号',
  `cardtype` int(11) NOT NULL DEFAULT '0',
  `accessurl` varchar(255) NOT NULL COMMENT '交易网址',
  `accessurl_id` int(11) NOT NULL DEFAULT '0' COMMENT '网址id',
  `email` varchar(50) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `del` tinyint(1) NOT NULL DEFAULT '1',
  `deleted` int(11) NOT NULL DEFAULT '0',
  `wayed` varchar(50) NOT NULL DEFAULT '0',
  `isfiltered` int(11) NOT NULL DEFAULT '0',
  `deposit_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '保证金返还状态1为已返还0为未返还',
  `ischongfu` int(11) NOT NULL DEFAULT '0',
  `order_type` int(11) DEFAULT '1' COMMENT '订单类型，1信用卡订单',
  `is_settlement` tinyint(11) DEFAULT '0' COMMENT '是否结算，0未结算，1已结算',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchantid` (`merchantid`) USING BTREE,
  KEY `doopsun_orderdate` (`doopsun_orderdate`) USING BTREE,
  KEY `currency` (`currency`) USING BTREE,
  KEY `isfiltered` (`isfiltered`) USING BTREE,
  KEY `merchantid_2` (`merchantid`) USING BTREE,
  KEY `orderid` (`orderid`) USING BTREE,
  KEY `wayed` (`wayed`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `ischongfu` (`ischongfu`) USING BTREE,
  KEY `deposit_status` (`deposit_status`) USING BTREE,
  KEY `cardtype` (`cardtype`) USING BTREE,
  KEY `accessurl_id` (`accessurl_id`) USING BTREE,
  KEY `doopsun_orderid` (`doopsun_orderid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=80048287 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_order_exchange 结构
CREATE TABLE IF NOT EXISTS `doopsun_order_exchange` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单id',
  `exchange_id` int(11) NOT NULL DEFAULT '0' COMMENT '汇率id',
  `exchange_content` text NOT NULL COMMENT '汇率内容json',
  `order_price` decimal(10,2) NOT NULL COMMENT '订单金额（人民币）',
  `order_price_us` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '交易金额(美元)',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `exchange_id` (`exchange_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4907731 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='订单汇率关联表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_order_links 结构
CREATE TABLE IF NOT EXISTS `doopsun_order_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL COMMENT '订单号',
  `merchant_id` int(11) NOT NULL COMMENT '商户id',
  `productname` varchar(255) NOT NULL COMMENT '商品名称',
  `orderdate` varchar(100) NOT NULL COMMENT '订单日期',
  `currency` varchar(100) NOT NULL COMMENT '订单币种',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0待支付，1已支付，2已失效',
  `orderamount` decimal(10,2) NOT NULL COMMENT '订单金额，0.00',
  `isShipping` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1实物，2虚拟',
  `order_link` text NOT NULL COMMENT '订单支付链接',
  `callbackurl` varchar(255) NOT NULL COMMENT '回调地址',
  `browserbackurl` varchar(255) NOT NULL COMMENT '页面返回地址',
  `accessurl` varchar(255) NOT NULL COMMENT '支付链接',
  `remark` varchar(255) DEFAULT NULL COMMENT 'IP地址',
  `signature` varchar(255) DEFAULT NULL COMMENT '签名',
  `created_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_order_settle 结构
CREATE TABLE IF NOT EXISTS `doopsun_order_settle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `merchantid` int(11) NOT NULL COMMENT '商户id',
  `commissioncharge` decimal(3,3) DEFAULT NULL COMMENT '费率',
  `deposits` decimal(3,3) DEFAULT NULL COMMENT '保证金费率',
  `cardtype` int(11) DEFAULT NULL COMMENT '卡类型',
  `order_price_us` decimal(10,2) DEFAULT NULL COMMENT '订单金额',
  `order_settlement_amount` decimal(10,2) DEFAULT NULL COMMENT '结算金额',
  `created_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `deposit_type` int(11) DEFAULT '1' COMMENT '保证金类型，1：循环保证金，2固定保证金',
  `dishonor_money` decimal(6,2) DEFAULT '0.00' COMMENT '单笔拒付罚金',
  `order_procesfee` decimal(10,2) DEFAULT '0.00' COMMENT '订单处理费',
  `protest_fee` decimal(10,2) DEFAULT '0.00' COMMENT 'RDR预警费',
  `ethocaprotest_fee` decimal(10,2) DEFAULT '0.00' COMMENT 'Ethoca预警费',
  `treeds` decimal(10,2) DEFAULT '0.00' COMMENT '3DS',
  `refund_handlfee` decimal(10,2) DEFAULT '0.00' COMMENT '退款手续费',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=32382 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=FIXED COMMENT='订单结算数据表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_order_statement 结构
CREATE TABLE IF NOT EXISTS `doopsun_order_statement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `statecode` varchar(100) NOT NULL COMMENT '结算编号',
  `merchantid` int(11) NOT NULL COMMENT '商户id',
  `billing_currency` varchar(50) DEFAULT NULL COMMENT '币种',
  `billing_cycle` varchar(50) DEFAULT NULL COMMENT '结算周期',
  `order_time` int(11) DEFAULT NULL COMMENT '订单时间',
  `settlement_time` int(11) DEFAULT NULL COMMENT '结算时间',
  `is_logistics` tinyint(1) DEFAULT NULL COMMENT '1上传2不上传',
  `settlement_amount` decimal(10,2) DEFAULT NULL COMMENT '结算金额',
  `deducted_amount` decimal(10,2) DEFAULT NULL COMMENT '扣除金额',
  `actual_amount` decimal(10,2) DEFAULT NULL COMMENT '实际金额',
  `remark` text,
  `refund_fee` decimal(10,2) DEFAULT NULL COMMENT '退款金额',
  `dishonor_fee` decimal(10,2) DEFAULT NULL COMMENT '拒付退款金额',
  `dishonor_fine` decimal(10,2) DEFAULT NULL COMMENT '拒付罚金',
  `yujing_fee` decimal(10,2) DEFAULT NULL COMMENT '预警费',
  `refund_hand_fee` varchar(255) DEFAULT NULL COMMENT '退款手续费',
  `hand_fee` decimal(10,2) DEFAULT NULL COMMENT '手续费（卡费+3ds费+订单处理费）',
  `deposits_fee` decimal(10,2) DEFAULT NULL COMMENT '保证金',
  `failorder_count` int(11) DEFAULT NULL COMMENT '失败订单个数',
  `created_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `three_fee` decimal(10,2) DEFAULT NULL COMMENT '3ds费',
  `order_fee` decimal(10,2) DEFAULT NULL COMMENT '订单处理费',
  `order_start_time` int(11) DEFAULT '0' COMMENT '订单开始时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_order_statement_detail 结构
CREATE TABLE IF NOT EXISTS `doopsun_order_statement_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantid` int(11) DEFAULT NULL COMMENT '商户id',
  `state_id` int(11) NOT NULL COMMENT '结算id',
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `status` int(11) DEFAULT NULL COMMENT '订单状态',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3371 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_order_statement_pend 结构
CREATE TABLE IF NOT EXISTS `doopsun_order_statement_pend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `merchantid` int(11) DEFAULT NULL COMMENT '商户id',
  `order_amount` decimal(10,2) DEFAULT NULL COMMENT '订单金额',
  `settlement_amount` decimal(10,2) NOT NULL COMMENT '结算金额',
  `settlement_time` int(11) NOT NULL COMMENT '结算时间',
  `is_settlement` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否结算',
  `cardfee` decimal(10,2) DEFAULT NULL COMMENT '卡费',
  `deposits_fee` decimal(10,2) DEFAULT NULL COMMENT '保证金',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_punish 结构
CREATE TABLE IF NOT EXISTS `doopsun_punish` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` int(11) NOT NULL COMMENT '订单号ID',
  `orderid` varchar(200) NOT NULL DEFAULT '0' COMMENT '订单号',
  `doopsun_orderid` varchar(55) NOT NULL DEFAULT '0' COMMENT '系统流水号',
  `merchantid` int(11) NOT NULL COMMENT '商户号',
  `money` decimal(10,2) NOT NULL COMMENT '处罚金额',
  `remark` varchar(200) NOT NULL COMMENT '处罚原因',
  `time` int(11) NOT NULL COMMENT '处罚时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `orderid` (`orderid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='拒付处罚';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_rate 结构
CREATE TABLE IF NOT EXISTS `doopsun_rate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `isuser` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否正在使用0未使用1正在使用',
  `merchant_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `commissioncharge` decimal(3,3) NOT NULL DEFAULT '0.000' COMMENT '手续费率精确到0.001',
  `commissioncharge2` decimal(3,3) NOT NULL DEFAULT '0.060',
  `commissioncharge3` decimal(3,3) NOT NULL DEFAULT '0.050',
  `deposits` decimal(3,3) NOT NULL DEFAULT '0.000' COMMENT '结算比例精确到0.001',
  `roundTime` int(5) NOT NULL DEFAULT '0' COMMENT '结算周期',
  `atmaround` int(5) NOT NULL DEFAULT '0' COMMENT '提款周期',
  `num` int(11) NOT NULL DEFAULT '0',
  `drawing` int(5) DEFAULT '10000' COMMENT '最小提款金额',
  `dishonor_money` decimal(6,2) NOT NULL DEFAULT '0.00' COMMENT '单笔拒付罚金',
  `extDateMonth` varchar(10) NOT NULL DEFAULT '201612' COMMENT '汇率有效期，格式：201701',
  `withdraw_fee` int(5) DEFAULT '50' COMMENT '提款手续费',
  `settlement_rate` decimal(3,3) DEFAULT '0.000' COMMENT '结算比例',
  `order_procesfee` decimal(10,2) DEFAULT '0.30' COMMENT '订单处理费',
  `protest_fee` decimal(10,2) DEFAULT '30.00' COMMENT 'RDR预警费',
  `ethocaprotest_fee` decimal(10,2) DEFAULT '30.00' COMMENT 'Ethoca预警费',
  `treeds` decimal(10,2) DEFAULT '0.30' COMMENT '3DS',
  `refund_handlfee` decimal(10,2) DEFAULT '1.00' COMMENT '退款手续费',
  `account_fee` decimal(10,2) DEFAULT '700.00' COMMENT '开户费',
  `withdraw_fee_percent` decimal(10,5) DEFAULT '0.00000' COMMENT '提款手续费百分比',
  `deposit_type` int(11) DEFAULT '1' COMMENT '保证金类型，1：循环保证金，2固定保证金',
  `fixed_deposit` decimal(10,5) DEFAULT '0.00000' COMMENT '固定保证金费率',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=815 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_rate_log 结构
CREATE TABLE IF NOT EXISTS `doopsun_rate_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL,
  `rateInfo_old` text COMMENT '旧的商户数据',
  `rateInfo_new` text COMMENT '新的商户数据',
  `created_time` datetime DEFAULT NULL,
  `created_user` varchar(255) DEFAULT NULL COMMENT '编辑人员',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_refund 结构
CREATE TABLE IF NOT EXISTS `doopsun_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `orderid` int(30) DEFAULT '0' COMMENT '退款订单号，非ID',
  `addtime` int(50) NOT NULL DEFAULT '0' COMMENT '申请退款时间',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0:处理中，1:退款成功，2:退款失败',
  `refund_success_time` int(50) NOT NULL DEFAULT '0' COMMENT '处理时间',
  `refund_content` varchar(200) DEFAULT NULL COMMENT '申请退款原因',
  `fail_content` varchar(200) DEFAULT NULL COMMENT '失败原因',
  `rmb` decimal(10,2) NOT NULL DEFAULT '0.00',
  `merchantid` int(11) NOT NULL DEFAULT '0' COMMENT '商户账号',
  `refund_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '返还状态0为未返还，1为已返还',
  `refund_doopsun_orderid` varchar(255) DEFAULT NULL COMMENT '订单流水号',
  `accessurl_id` int(11) NOT NULL DEFAULT '0' COMMENT '网址id',
  `refund_type` int(11) DEFAULT '0' COMMENT '1为全额退款2为部分退款',
  `order_time` int(11) NOT NULL DEFAULT '0' COMMENT '订单时间',
  `refund_fee` decimal(10,3) DEFAULT NULL COMMENT '退款手续费',
  `refund_currency` varchar(50) DEFAULT '' COMMENT '币种',
  `refund_amount` decimal(10,2) DEFAULT '0.00' COMMENT '退款的原币种金额',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `refund_doopsun_orderid` (`refund_doopsun_orderid`) USING BTREE,
  KEY `orderid` (`orderid`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `merchantid` (`merchantid`) USING BTREE,
  KEY `accessurl_id` (`accessurl_id`) USING BTREE,
  KEY `refund_status` (`refund_status`) USING BTREE,
  KEY `refund_type` (`refund_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_review_logs 结构
CREATE TABLE IF NOT EXISTS `doopsun_review_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `warning_id` int(11) NOT NULL COMMENT '预警ID',
  `merchant_order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商户订单号',
  `result` enum('pass','reject','observe') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '审核结果',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `reviewer` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '审核人',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `warning_id` (`warning_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='审核记录';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_risk 结构
CREATE TABLE IF NOT EXISTS `doopsun_risk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(50) NOT NULL COMMENT '内容',
  `remark` varchar(100) DEFAULT NULL COMMENT '备注',
  `type` tinyint(1) NOT NULL COMMENT '1:国家-2:ip-3:邮箱-4:卡号',
  `time` int(10) NOT NULL COMMENT '时间',
  `merchantid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchantid` (`merchantid`) USING BTREE,
  KEY `time` (`time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='风险控制';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_rules 结构
CREATE TABLE IF NOT EXISTS `doopsun_rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则分组',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则名称',
  `rule_expr` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则条件(JSON)',
  `action` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '拦截交易' COMMENT '触发措施',
  `is_enabled` int(1) DEFAULT '1' COMMENT '是否启用',
  `sort_order` int(11) DEFAULT '0' COMMENT '排序',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `group_name` (`group_name`) USING BTREE,
  KEY `is_enabled` (`is_enabled`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='风控规则';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_sendsms 结构
CREATE TABLE IF NOT EXISTS `doopsun_sendsms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(50) DEFAULT NULL COMMENT '手机号',
  `code` varchar(50) DEFAULT NULL COMMENT '验证码',
  `result` text COMMENT '结果',
  `message` varchar(200) DEFAULT NULL,
  `created_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `remark` varchar(255) DEFAULT NULL,
  `user_ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_sendtoken 结构
CREATE TABLE IF NOT EXISTS `doopsun_sendtoken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` char(20) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `sort` varchar(100) DEFAULT NULL,
  `expiration_time` int(11) DEFAULT NULL,
  `created_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `requestId` varchar(255) DEFAULT NULL COMMENT '请求号',
  `userType` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `regCountry` varchar(255) DEFAULT NULL,
  `chineseName` varchar(255) DEFAULT NULL,
  `englishName` varchar(255) DEFAULT NULL,
  `incorporationCertNo` varchar(255) DEFAULT NULL,
  `createDate` varchar(255) DEFAULT NULL,
  `effectiveDate` varchar(255) DEFAULT NULL,
  `expiryDate` varchar(255) DEFAULT NULL,
  `regAddress` varchar(255) DEFAULT NULL,
  `businessCountry` varchar(255) DEFAULT NULL,
  `businessAddress` varchar(255) DEFAULT NULL,
  `companyType` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `industryCategory` varchar(255) DEFAULT NULL,
  `serviceCategory` varchar(255) DEFAULT NULL,
  `businessRegion` varchar(255) DEFAULT NULL,
  `employeeRank` varchar(255) DEFAULT NULL,
  `salesVolumeRank` varchar(255) DEFAULT NULL,
  `wealthSource` text COMMENT 'string:财富来源',
  `fundsSource` text COMMENT 'json:预计资金来源',
  `merchantNetinPersons` text COMMENT 'json:董事/股东/法人/主要负责人信息',
  `kycFiles` text COMMENT 'json:资质文件列表',
  `notifyUrl` varchar(255) DEFAULT NULL COMMENT '回调地址',
  `mobileAreaCode` varchar(50) DEFAULT '86' COMMENT '电话区号',
  `enRegAddress` varchar(255) DEFAULT NULL,
  `businessRegCertNo` varchar(255) DEFAULT NULL,
  `businessEffectiveDate` varchar(255) DEFAULT NULL,
  `businessExpiryDate` varchar(255) DEFAULT NULL,
  `enBusinessAddress` varchar(255) DEFAULT NULL,
  `nameUsed` varchar(255) DEFAULT NULL,
  `otherWealthSource` varchar(255) DEFAULT NULL,
  `otherFundsSource` varchar(255) DEFAULT NULL,
  `appStoreImageFilePath` varchar(150) DEFAULT NULL COMMENT 'APP开发者后台管理截图',
  `appDownloadUrl` varchar(150) DEFAULT NULL COMMENT 'APP在应用商店的下载链接',
  `middleShareholders` text COMMENT 'json:中间层股东',
  `franchiseLicenseName` varchar(150) DEFAULT NULL COMMENT '特许经营许可证名称',
  `franchiseLicenseNum` varchar(50) DEFAULT NULL COMMENT '特许经营许可证编号',
  `franchiseBiz` varchar(50) DEFAULT NULL COMMENT '特许经营内容/业务',
  `regulatorName` varchar(50) DEFAULT NULL COMMENT '监管机构名称',
  `franchiseLicenseEffectiveDate` varchar(50) DEFAULT NULL COMMENT '获得许可时间',
  `franchiseLicenseExpiryDate` varchar(50) DEFAULT NULL COMMENT '许可有效期',
  `companyIsListed` varchar(50) DEFAULT NULL COMMENT '是否为上市公司',
  `exchangeName` varchar(50) DEFAULT NULL COMMENT '交易所名称',
  `parentCompanyExist` varchar(50) DEFAULT NULL COMMENT '是否存在母公司',
  `parentCompanyIsListed` varchar(50) DEFAULT NULL COMMENT '母公司是否为上市公司',
  `parentCompanyExchangeName` varchar(255) DEFAULT NULL,
  `controlledByFinalParentCompany` varchar(255) DEFAULT NULL,
  `parentCompanyRegulatorRegion` varchar(255) DEFAULT NULL,
  `parentCompanyRegulatorName` varchar(255) DEFAULT NULL,
  `byGptSalesRank` varchar(50) DEFAULT NULL COMMENT '预计通过gpt月平均交易(HKD)',
  `supplyServiceType` varchar(50) DEFAULT NULL COMMENT '提供的服务类型',
  `supplyServiceTypeOther` varchar(50) DEFAULT NULL COMMENT '其他服务类型',
  `feeBear` varchar(155) DEFAULT NULL,
  `merchantId` varchar(50) DEFAULT NULL COMMENT '商户编码',
  `subMerchantId` varchar(50) DEFAULT NULL COMMENT '子商户商编',
  `status` varchar(50) DEFAULT NULL COMMENT '状态',
  `msg` text COMMENT '返回信息',
  `merchantIdY` varchar(150) DEFAULT NULL COMMENT 'yeepay商户编码',
  `createdTime` varchar(255) DEFAULT NULL COMMENT '入网提交时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchantId` (`merchantId`) USING BTREE,
  KEY `subMerchantId` (`subMerchantId`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `email` (`email`) USING BTREE,
  KEY `requestId` (`requestId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='B2B-YEEPAY商户';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_curllog 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_curllog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text,
  `request` text NOT NULL COMMENT '请求参数',
  `content` text COMMENT '返回数据',
  `curltype` varchar(100) NOT NULL COMMENT '请求类型',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '接口描述',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=28309 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b请求接口的日志表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_cust 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_cust` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `requestId` varchar(50) DEFAULT NULL COMMENT '请求号',
  `merchantId` varchar(50) DEFAULT NULL COMMENT '商户编号',
  `status` varchar(50) DEFAULT NULL COMMENT '状态：SUCCESS: 成功、FAILED: 失败',
  `type` varchar(50) DEFAULT NULL COMMENT '固定值:RESULT_REPORT',
  `rank` varchar(50) DEFAULT NULL COMMENT '名录分类结果:A:A类/B:B类/C:C类/N:不在名录内',
  `memo` varchar(255) DEFAULT NULL COMMENT '扩展字段',
  `custCode` varchar(50) DEFAULT NULL COMMENT '组织机构代码',
  `custName` varchar(150) DEFAULT NULL COMMENT '组织机构名称',
  `areaCode` varchar(50) DEFAULT NULL COMMENT '住所／营业场所代码',
  `industryCode` varchar(50) DEFAULT NULL COMMENT '行业属性代码',
  `attrCode` varchar(50) DEFAULT NULL COMMENT '经济类型代码',
  `countryCode` varchar(50) DEFAULT NULL COMMENT '常驻国家代码 字母代码',
  `isTaxFree` varchar(50) DEFAULT NULL COMMENT '是否特殊经济区内企业',
  `taxFreeCode` varchar(50) DEFAULT NULL COMMENT '特殊经济区内企业类型',
  `custAddr` varchar(255) DEFAULT NULL COMMENT '单位地址',
  `contact` varchar(255) DEFAULT NULL COMMENT '单位联系人',
  `tel` varchar(50) DEFAULT NULL COMMENT '单位联系电话',
  `postCode` varchar(50) DEFAULT NULL COMMENT '邮政编码',
  `businessLicense` varchar(255) DEFAULT NULL COMMENT '营业执照电子版 调用文件上传接口后返回的文件地址',
  `notifyUrl` varchar(150) DEFAULT NULL COMMENT ' 通知地址',
  `purchaseFilingFormPath` varchar(255) DEFAULT NULL COMMENT ' 市场采购备案登记表路径',
  `legalName` varchar(50) DEFAULT NULL COMMENT ' 法人名称',
  `socialCreditCode` varchar(50) DEFAULT NULL COMMENT ' 统一社会信用代码',
  `msg` text COMMENT 'json:errCode、errMsg',
  `createdTime` varchar(255) DEFAULT NULL,
  `subMerchantId` varchar(255) DEFAULT NULL,
  `merchantIdY` varchar(150) DEFAULT NULL COMMENT 'yeepay商户编码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户企业表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_exchange 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_exchange` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '客户号',
  `requestId` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '请求id',
  `requestTime` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '请求时间',
  `sourceCur` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '源币种',
  `targetCur` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '目标币种',
  `sourceAmt` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '源币种金额(原样返回请求锁定的币种金额)',
  `targetAmt` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '目标币种金额(根据锁定币种金额及汇率返回换算的币种金额)',
  `validateTo` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '汇率失效时间',
  `curPair` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '货币对',
  `clientRate` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '报价客户汇率',
  `quoteId` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '报价单id',
  `type` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '换汇业务类型：1询价(inquiry)，2下单(apply)',
  `tenor` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交割方式',
  `conversionDate` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '换汇交割日期',
  `contractId` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '换汇合同号',
  `status` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '订单状态:换汇订单状态SUBMITTED-已提交',
  `msg` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '状态说明(只有换汇申请才有状态)',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户换汇询价和申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_exchange_record 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_exchange_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subMerchantId` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '客户号',
  `requestId` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '请求id',
  `requestTime` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '请求时间',
  `sourceCur` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '源币种',
  `targetCur` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '目标币种',
  `sourceAmt` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '源币种金额(原样返回请求锁定的币种金额)',
  `targetAmt` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '目标币种金额(根据锁定币种金额及汇率返回换算的币种金额)',
  `fxRateExpireDt` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '汇率失效时间',
  `curPair` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '货币对',
  `fxRate` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '报价客户汇率',
  `serialNum` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '报价单流水号',
  `type` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '换汇业务类型：1询价(inquiry)，2下单(apply)',
  `tenor` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '交割方式',
  `conversionDate` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '换汇交割日期',
  `contractId` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '换汇合同号',
  `status` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '订单状态:换汇订单状态SUBMITTED-已提交',
  `completeDt` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '完成日期',
  `msg` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '状态说明(只有换汇申请才有状态)',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户换汇询价和申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_ga 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_ga` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `requestId` varchar(50) DEFAULT NULL COMMENT '请求号',
  `merchantId` varchar(50) DEFAULT NULL COMMENT '申请va的商编',
  `vaRegCountry` varchar(50) DEFAULT NULL COMMENT 'VA开立所在国家/地区',
  `applyUse` varchar(50) DEFAULT NULL COMMENT '账户用途',
  `currency` varchar(50) DEFAULT NULL COMMENT '币种',
  `shopType` varchar(50) DEFAULT NULL COMMENT '站点/平台',
  `holderId` varchar(50) DEFAULT NULL COMMENT '持有者ID',
  `payType` varchar(50) DEFAULT NULL COMMENT '支付方式',
  `materialId` varchar(50) DEFAULT NULL COMMENT 'materialId',
  `subMerchantId` varchar(50) DEFAULT NULL COMMENT '子商户编号',
  `notifyUrl` varchar(50) DEFAULT NULL COMMENT '回调地址',
  `vaApplyId` varchar(50) DEFAULT NULL COMMENT '收款账户申请ID',
  `vaNumber` varchar(150) DEFAULT NULL COMMENT 'va账户号',
  `vaName` varchar(150) DEFAULT NULL COMMENT 'va账户名称',
  `vaArea` varchar(50) DEFAULT NULL COMMENT 'va账户开立地区',
  `openBankCode` varchar(150) DEFAULT NULL COMMENT '开户行编码',
  `openBankName` varchar(150) DEFAULT NULL COMMENT '开户银行',
  `openBankAddress` varchar(150) DEFAULT NULL COMMENT '开户行详细地址',
  `openBankSwiftCode` varchar(150) DEFAULT NULL COMMENT '开户行swift Code',
  `openBankBranchCode` varchar(150) DEFAULT NULL COMMENT '开户行支行编码',
  `localBankRouteNo` varchar(150) DEFAULT NULL COMMENT 'ACH路由号码',
  `status` varchar(50) DEFAULT NULL COMMENT '状态',
  `materialCode` varchar(50) DEFAULT NULL COMMENT '材料code',
  `msg` text COMMENT 'json:返回信息errorType错误类型、errorMessage错误原因',
  `xYopSign` varchar(255) DEFAULT NULL COMMENT '响应体签名',
  `materialInfoJson` text COMMENT '补充材料信息 : json',
  `requestType` varchar(255) DEFAULT NULL COMMENT '请求类型（ADD-新增、UPDATE-更新、DELETE-注销）',
  `createDateTime` varchar(255) DEFAULT NULL COMMENT '发起时间',
  `completeDateTime` varchar(255) DEFAULT NULL COMMENT '完成时间',
  `errorMessage` varchar(1888) DEFAULT NULL,
  `errorType` varchar(888) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchantId` (`merchantId`) USING BTREE,
  KEY `subMerchantId` (`subMerchantId`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `requestId` (`requestId`) USING BTREE,
  KEY `vaApplyId` (`vaApplyId`) USING BTREE,
  KEY `payType` (`payType`) USING BTREE,
  KEY `holderId` (`holderId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='B2B-YEEPAY商户收款账户';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_ga_supplement 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_ga_supplement` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `requestId` varchar(50) DEFAULT NULL COMMENT '请求号',
  `supplementMaterialInfo` text COMMENT 'json:补充材料详情',
  `vaApplyId` varchar(50) DEFAULT NULL COMMENT '收款账户申请ID',
  `subMerchantId` varchar(50) DEFAULT NULL COMMENT '子商户商编',
  `notifyUrl` varchar(150) DEFAULT NULL COMMENT '材料审核成功或不通过时回调地址',
  `materialId` varchar(150) DEFAULT NULL COMMENT '材料申请id',
  `createdTime` varchar(150) DEFAULT NULL COMMENT '创建时间',
  `updateTime` varchar(150) DEFAULT NULL COMMENT '修改时间',
  `status` varchar(150) DEFAULT NULL COMMENT '补充状态',
  `msg` text COMMENT 'json:审核意见、对应字段auditOpinion',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `requestId` (`requestId`) USING BTREE,
  KEY `vaApplyId` (`vaApplyId`) USING BTREE,
  KEY `materialId` (`materialId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='B2B-YEEPAY商户收款账户补充材料';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_inspection 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_inspection` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `requestId` varchar(255) DEFAULT NULL COMMENT '请求号',
  `inspectionNoticeId` varchar(255) DEFAULT NULL COMMENT '巡检通知id',
  `inspectionNoticeType` varchar(255) DEFAULT NULL COMMENT '巡检通知类型：BEFORE_EXPIRE_REMIND: 过期前提醒通知、AFTER_EXPIRE_REMIND: 过期后提醒通知、AFTER_EXPIRE_FROZEN: 冻结通知，只能线下处理',
  `inspectionNoticeDate` varchar(50) DEFAULT NULL COMMENT '巡检通知日期',
  `merchantId` varchar(50) DEFAULT NULL COMMENT '父商户编码',
  `subMerchantId` varchar(50) DEFAULT NULL COMMENT '子商户商编',
  `certificateSubjectType` varchar(255) DEFAULT NULL COMMENT '主体类型：COMPANY: 企业、ADMIN: 管理员、SHAREHOLDER:股东、DIRECTOR:董事、LEGAL_PERSON:法人',
  `certNo` varchar(255) DEFAULT NULL COMMENT '主体证件号',
  `fileType` varchar(255) DEFAULT NULL COMMENT '证件类型',
  `fileSideList` varchar(255) DEFAULT NULL COMMENT 'json:证件正反面 ["FRONT", "BACK"]',
  `expireDate` varchar(255) DEFAULT NULL COMMENT '过期日期',
  `frozenDate` varchar(255) DEFAULT NULL COMMENT '冻结日期',
  `status` varchar(50) DEFAULT NULL COMMENT '状态：PROCESS: 处理中、SUCCESS: 开户成功、FAILED: 开户失败',
  `msg` text COMMENT 'json:errCode、errMsg',
  `merchantIdY` varchar(150) DEFAULT NULL COMMENT 'yeepay商户编码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户巡检资质表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_payment 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subMerchantId` varchar(100) DEFAULT NULL COMMENT '子商户商编',
  `requestId` varchar(150) DEFAULT NULL COMMENT '请求id',
  `payoutType` varchar(150) DEFAULT NULL COMMENT '付款类型\n',
  `useCode` varchar(100) DEFAULT NULL COMMENT '用途编码\n',
  `detailPath` varchar(255) DEFAULT NULL COMMENT '付款文件上传获取的path',
  `remitType` varchar(100) DEFAULT NULL COMMENT '分发类型',
  `payoutCurrency` varchar(50) DEFAULT NULL COMMENT '付款币种',
  `receiveCurrency` varchar(50) DEFAULT NULL COMMENT '收款币种',
  `amount` varchar(150) DEFAULT NULL COMMENT '金额',
  `beneficiaryAccountName` varchar(150) DEFAULT NULL COMMENT '银行账户收款人名称\n',
  `beneficiaryAccountNumber` varchar(150) DEFAULT NULL COMMENT '收款账号\n',
  `feeBear` varchar(150) DEFAULT NULL COMMENT '费用承担方\n',
  `clearingType` varchar(150) DEFAULT NULL COMMENT '清分类型\n',
  `status` varchar(50) DEFAULT NULL COMMENT '付款交易状态 0已受理/处理中 1付款成功 2付款失败 3已退款 4明细认证成功',
  `tradeSide` varchar(50) DEFAULT NULL COMMENT '交易方向',
  `swiftCode` varchar(50) DEFAULT NULL COMMENT 'swiftCode\n',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `pobo` varchar(10) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL COMMENT '汇款附言\n',
  `remitPurpose` varchar(100) DEFAULT NULL COMMENT '付款目的',
  `token` varchar(300) DEFAULT NULL COMMENT '锁汇token',
  `originalRequestId` varchar(255) DEFAULT NULL COMMENT '贸易结汇场景 原订单请求号\n',
  `mxrequestId` varchar(255) DEFAULT NULL COMMENT '明细上传请求id',
  `serialNum` varchar(100) DEFAULT NULL COMMENT '流水号',
  `failmsg` varchar(200) DEFAULT NULL COMMENT '失败原因',
  `fxRate` decimal(10,2) DEFAULT NULL COMMENT '汇率',
  `feeAmount` decimal(10,2) DEFAULT NULL COMMENT '手续费',
  `notifydata` text COMMENT '付款返回明细',
  `notifymxdata` text COMMENT '明细上传返回明细',
  `payNotify` text COMMENT '境内分发重查询返回明细',
  `supplierid` int(11) DEFAULT NULL COMMENT '收款人的id',
  `payexcel` varchar(2555) DEFAULT NULL COMMENT '付款明细模板',
  `detailexcel` varchar(2555) DEFAULT NULL COMMENT '明细模板',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `subMerchantId` (`subMerchantId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户付款表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_receivable 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_receivable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` varchar(100) NOT NULL COMMENT '收款账户商编',
  `orderId` varchar(100) DEFAULT NULL COMMENT '收款记录ID',
  `requestId` mediumtext COMMENT '请求号',
  `eventCode` varchar(100) DEFAULT NULL COMMENT '事件类型:10：需要补充资料15：需要关联交易单17：审核驳回20：入账成功30：入账失败35：退款处理中40：退款已汇出',
  `bankSerialNum` varchar(100) DEFAULT NULL COMMENT '银行来账流水号',
  `orderType` varchar(100) DEFAULT NULL COMMENT '订单类型:RECHARGE-充值TRADE_COLLECTION-贸易收款PLATFORM_COLLECTION-平台收款',
  `amount` varchar(100) DEFAULT NULL COMMENT '入账金额',
  `actualAmount` varchar(100) DEFAULT NULL COMMENT '实际入账金额',
  `currency` varchar(100) DEFAULT NULL COMMENT '入账币种',
  `payAmount` varchar(100) DEFAULT NULL COMMENT '付款金额',
  `payCurrency` varchar(100) DEFAULT NULL COMMENT '付款币种',
  `inboundRate` varchar(100) DEFAULT NULL COMMENT '汇率',
  `feeAmount` varchar(100) DEFAULT NULL COMMENT '手续费金额',
  `feeCurrency` varchar(100) DEFAULT NULL COMMENT '手续费币种',
  `payerAccountName` varchar(255) DEFAULT NULL COMMENT '付款方名称',
  `payerAccountNum` varchar(255) DEFAULT NULL COMMENT '付款方账号',
  `payerAccountSwiftCode` varchar(100) DEFAULT NULL COMMENT '付款方银行SWIFT Code',
  `payerAccountBankName` varchar(255) DEFAULT NULL COMMENT '付款方银行名称',
  `createDateTime` varchar(100) DEFAULT NULL COMMENT '来账时间',
  `vaNo` varchar(100) DEFAULT NULL COMMENT '收款账户账号',
  `remark` text COMMENT '汇款附言',
  `matchRequestId` varchar(255) DEFAULT NULL COMMENT '关联请求号',
  `tradeOrderList` text COMMENT '关联交易订单',
  `refundInfo` text COMMENT '退款信息',
  `createdtime` int(11) DEFAULT NULL COMMENT '入库时间',
  `targetFeeAmount` varchar(100) DEFAULT NULL,
  `targetFeeFxRate` varchar(100) DEFAULT NULL,
  `targetFeeCurrency` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT 'WAITING' COMMENT '状态',
  `supplementMaterias` text COMMENT '补充资料',
  `msg` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='来账商户通知表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_receivable_nopayer 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_receivable_nopayer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` varchar(100) DEFAULT NULL COMMENT '账户商编',
  `orderId` varchar(100) DEFAULT NULL COMMENT '记录ID',
  `requestId` varchar(100) DEFAULT NULL COMMENT '请求号',
  `type` varchar(100) DEFAULT NULL COMMENT '通知类型',
  `bankSerialNum` varchar(100) DEFAULT NULL COMMENT '银行来账流水号',
  `orderType` varchar(100) DEFAULT NULL COMMENT '订单类型:RECHARGE-充值TRADE_COLLECTION-贸易收款PLATFORM_COLLECTION-平台收款',
  `amount` varchar(100) DEFAULT NULL COMMENT '来账金额',
  `currency` varchar(100) DEFAULT NULL COMMENT '来账币种',
  `actualAmount` varchar(100) DEFAULT NULL COMMENT '实际入账金额',
  `payerAccountName` varchar(255) DEFAULT NULL COMMENT '付款方名称',
  `payerAccountNum` varchar(255) DEFAULT NULL COMMENT '付款方账号',
  `payerAccountSwiftCode` varchar(100) DEFAULT NULL COMMENT '付款方银行SWIFT Code',
  `payerAccountBankName` varchar(255) DEFAULT NULL COMMENT '付款方银行名称	',
  `createDateTime` varchar(100) DEFAULT NULL COMMENT '来账时间',
  `vaNumber` varchar(100) DEFAULT NULL COMMENT '收款账户账号',
  `remark` text COMMENT '汇款附言',
  `paymentProofList` text COMMENT '付款证明',
  `createdtime` int(11) DEFAULT NULL COMMENT '入库时间',
  `subMerchantId` varchar(255) DEFAULT NULL COMMENT '子商户号',
  `
swiftCode` varchar(100) DEFAULT NULL COMMENT 'swiftCode',
  `payeeAccountName` varchar(255) DEFAULT NULL,
  `payeeAccountNum` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='无付款方来账通知表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_supplier 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantid` varchar(50) DEFAULT NULL COMMENT '本站的商户id',
  `merchantidy` varchar(255) DEFAULT NULL COMMENT '易宝商户id',
  `requestId` varchar(100) DEFAULT NULL COMMENT '商户请求号',
  `type` varchar(100) DEFAULT NULL COMMENT '业务类型',
  `supplierType` varchar(100) DEFAULT NULL COMMENT '收款人类型',
  `supplierName` varchar(200) DEFAULT NULL COMMENT '收款人名称',
  `businessType` varchar(100) DEFAULT NULL COMMENT '收款人业务类型',
  `accountName` varchar(200) DEFAULT NULL COMMENT '银行账户收款人名称',
  `accountNumber` varchar(200) DEFAULT NULL COMMENT '银行账户号码',
  `accountCurrency` varchar(50) DEFAULT NULL COMMENT '银行账户币种',
  `bankName` varchar(100) DEFAULT NULL COMMENT '银行名称',
  `operate` varchar(50) DEFAULT NULL COMMENT '操作',
  `subMerchantId` varchar(100) DEFAULT NULL COMMENT '子商户商户号',
  `businessDetail` text COMMENT '业务类型细分',
  `vouchers` text COMMENT '业务关联材料',
  `country` varchar(50) DEFAULT NULL COMMENT '收款人所在国家/地区\r\n\r\n',
  `address` varchar(255) DEFAULT NULL COMMENT '收款人所在地址',
  `clearingType` varchar(50) DEFAULT NULL COMMENT '清分类型',
  `accountType` varchar(50) DEFAULT NULL COMMENT '收款人银行账户类型',
  `bankCountry` varchar(50) DEFAULT NULL COMMENT '银行所在国家/地区',
  `bankAddress` varchar(255) DEFAULT NULL COMMENT '银行地址',
  `swiftCode` varchar(100) DEFAULT NULL COMMENT '银行swift code\r\n',
  `branchCode` varchar(50) DEFAULT NULL COMMENT '分支行编码',
  `firstName` varchar(100) DEFAULT NULL COMMENT '\r\n名',
  `middleName` varchar(100) DEFAULT NULL COMMENT '中间名',
  `lastName` varchar(100) DEFAULT NULL COMMENT '\r\n姓',
  `idType` varchar(100) DEFAULT NULL COMMENT '证件类型',
  `idNumber` varchar(100) DEFAULT NULL COMMENT '证件号码',
  `province` varchar(255) DEFAULT NULL COMMENT '收款人所在省/州',
  `city` varchar(255) DEFAULT NULL COMMENT '收款人所在城市',
  `sortCode` varchar(100) DEFAULT NULL COMMENT 'sort code',
  `postCode` varchar(100) DEFAULT NULL COMMENT '收款人所在地邮编',
  `abaNumber` varchar(100) DEFAULT NULL COMMENT 'ABA numbe',
  `email` varchar(200) DEFAULT NULL COMMENT '电子邮箱',
  `mobile` varchar(200) DEFAULT NULL COMMENT '手机号',
  `accountNoType` varchar(100) DEFAULT NULL COMMENT 'accountNoType',
  `routingNumber` varchar(100) DEFAULT NULL COMMENT '路由号码',
  `ifsCode` varchar(100) DEFAULT NULL COMMENT 'IFS code',
  `streetAddress` varchar(200) DEFAULT NULL COMMENT '收款方常驻地址',
  `recType` varchar(50) DEFAULT NULL COMMENT '收款人账户类型',
  `bsbCode` varchar(100) DEFAULT NULL COMMENT 'BSB Code',
  `alias` text COMMENT '收款人别名',
  `bankCode` varchar(255) DEFAULT NULL COMMENT '香港地区bank code',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `notifyUrl` text COMMENT '异步通知地址',
  `fedWireNumber` varchar(255) DEFAULT NULL COMMENT '\r\nFedWire号码',
  `iban` varchar(255) DEFAULT NULL COMMENT 'IBAN\r\n',
  `branchName` varchar(255) DEFAULT NULL COMMENT '分支行名称',
  `loginName` varchar(255) DEFAULT NULL COMMENT '报备商户登录账号',
  `merchantName` varchar(255) DEFAULT NULL COMMENT '报备商户签约名称',
  `payeeExpiryDate` varchar(255) DEFAULT NULL COMMENT '证件到期日',
  `status` varchar(100) DEFAULT NULL COMMENT '状态',
  `true_status` tinyint(1) DEFAULT '1' COMMENT '本库状态',
  `createdtime` int(11) DEFAULT NULL COMMENT '入库时间',
  `remarks` text COMMENT '本站备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_settlementexchange_trade_order 结构
CREATE TABLE IF NOT EXISTS `doopsun_settlementexchange_trade_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '合同订单号',
  `requestId` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '请求号',
  `subMerchantId` varchar(100) NOT NULL DEFAULT '0' COMMENT '客户号',
  `tradeType` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `isExchange` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'true-结汇、false-不结汇',
  `orderReferenceId` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '0' COMMENT '（商户下）唯一',
  `amount` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '0',
  `serviceCategory` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'GENERAL_TRADE-一般贸易-代采、LOGISTICS_COSTS-物流费用、TICKET_PURCHASE-机票采购tradeType=SERVICES_TRADE_COLLECTION 且 isExchange=true时只能为 LOGISTICS_COSTS',
  `fundsNature` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'GENERAL_TRADE-一般贸易-代采LOGISTICS_COSTS-物流费用RELATED_PAYMENT-关联公司打款PARTNERS_PAYMENT-业务合作方付款',
  `tradeDateTime` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'isExchange=true时必填 格式：yyyy-MM-dd',
  `buyerName` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'tradeType=SERVICES_TRADE_COLLECTION且FundsNature不等于RELATED_PAYMENT时，buyerArea不能为空; tradeType=GOODS_TRADE_COLLECTION且fundsNature=PARTNERS_PAYMENT时必填"',
  `contractFiles` varchar(5120) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `contractFilesOther` varchar(500) DEFAULT NULL,
  `productName` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `productCount` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `productUnit` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `productUrl` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `tradePlatformName` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `logisticsStatus` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `logisticsCompany` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `logisticsNumber` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `logisticsFiles` varchar(2555) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `logisticsExpectedTime` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `logisticsType` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'INIT-初始化  WAIT_MATCH - 待关联  COMPLETED - 已关联',
  `tradeRemark` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `otherFiles` varchar(2555) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `otherFilesOther` varchar(500) DEFAULT NULL,
  `buyerArea` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `createTime` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `canModifyType` varchar(255) DEFAULT NULL COMMENT '可修改信息类型',
  `receiverArea` varchar(200) DEFAULT '' COMMENT '收货地区',
  `matchedAmount` varchar(255) DEFAULT NULL COMMENT '已关联金额',
  `updateTime` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='b2b商户合同订单';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_subuser 结构
CREATE TABLE IF NOT EXISTS `doopsun_subuser` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '商户id',
  `loginName` varchar(25) NOT NULL COMMENT '登录名',
  `password` varchar(200) NOT NULL COMMENT '密码',
  `role` varchar(255) NOT NULL DEFAULT '' COMMENT '权限',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '账号状态：0正常，1禁用',
  `inTime` int(11) NOT NULL COMMENT '加入时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_superuser 结构
CREATE TABLE IF NOT EXISTS `doopsun_superuser` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `pid` int(11) DEFAULT '0' COMMENT '父级id',
  `userName` varchar(125) NOT NULL DEFAULT '' COMMENT '用户名',
  `trueName` varchar(50) NOT NULL COMMENT '姓名',
  `password` varchar(125) NOT NULL DEFAULT '' COMMENT '密码',
  `email` varchar(125) NOT NULL COMMENT '用户邮箱',
  `intTime` int(11) DEFAULT NULL COMMENT '加入时间',
  `editeTime` int(11) DEFAULT NULL COMMENT '编辑时间',
  `status` tinyint(2) DEFAULT '1' COMMENT '状态：0正常1不能登录',
  `stype` tinyint(2) DEFAULT '0' COMMENT '人员类型0 系统 1销售 2代理',
  `type` int(11) DEFAULT '1' COMMENT '类型',
  `role` text COMMENT '权限',
  `dept` varchar(50) DEFAULT NULL COMMENT '部门',
  `commission` varchar(50) DEFAULT NULL COMMENT '提出比例',
  `phone` varchar(50) DEFAULT NULL COMMENT '手机号码',
  `city` varchar(100) DEFAULT '' COMMENT '地区',
  `code` varchar(50) DEFAULT '' COMMENT '编码',
  `team_commission` decimal(5,5) DEFAULT '0.00000' COMMENT '团队提成',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_supplier 结构
CREATE TABLE IF NOT EXISTS `doopsun_supplier` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `supplierName` varchar(100) NOT NULL DEFAULT '' COMMENT '供应商名称',
  `supplierAddr` varchar(255) NOT NULL DEFAULT '' COMMENT '供应商地址',
  `supplierPhone` varchar(255) DEFAULT '' COMMENT '供应商电话',
  `supplierPeople` varchar(30) NOT NULL DEFAULT '' COMMENT '供应商联系人',
  `status` int(5) NOT NULL DEFAULT '1' COMMENT '1合作中/2放弃合作',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `merchantId` int(11) NOT NULL DEFAULT '0' COMMENT '商户号',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchantId` (`merchantId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='供应商表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_transactions 结构
CREATE TABLE IF NOT EXISTS `doopsun_transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_no` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商户号',
  `merchant_order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商户订单号',
  `channel_order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通道订单号',
  `trade_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易网址',
  `currency` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT 'USD' COMMENT '币种',
  `amount` decimal(12,2) NOT NULL COMMENT '金额',
  `card_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '卡类型',
  `card_no_mask` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '脱敏卡号',
  `bill_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '账单地址',
  `ship_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '收货地址',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易IP',
  `email` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易邮箱',
  `country_code` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '国家代码',
  `risk_level` enum('low','mid','high') COLLATE utf8mb4_unicode_ci DEFAULT 'low' COMMENT '风险等级',
  `matched_rules` text COLLATE utf8mb4_unicode_ci COMMENT '命中规则(JSON)',
  `status` enum('pending','passed','blocked','reviewing','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchant_no` (`merchant_no`) USING BTREE,
  KEY `merchant_order_no` (`merchant_order_no`) USING BTREE,
  KEY `risk_level` (`risk_level`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='交易明细';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_transactionurl 结构
CREATE TABLE IF NOT EXISTS `doopsun_transactionurl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL COMMENT '商户ID',
  `transaction_url` varchar(200) NOT NULL COMMENT '支付网址',
  `addtime` int(11) NOT NULL COMMENT '添加时间',
  `status` int(11) NOT NULL COMMENT '1审核 2审核中3拒绝',
  `enable` int(11) NOT NULL COMMENT '是否启用',
  `interface` int(11) NOT NULL DEFAULT '0' COMMENT '接口选项：1艾米 2钱成汇 ',
  `content` varchar(100) DEFAULT NULL,
  `money` int(11) NOT NULL DEFAULT '1000',
  `day` tinyint(2) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0' COMMENT '员工id',
  `shtime` int(11) NOT NULL DEFAULT '0' COMMENT '最后操作时间',
  `isdeleted` int(11) NOT NULL DEFAULT '0' COMMENT '是否被删除',
  `isrisk` int(11) DEFAULT '1' COMMENT '是否信任1为风险2为信任',
  `limit_money` int(11) NOT NULL DEFAULT '200',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `interface` (`interface`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `sid` (`sid`) USING BTREE,
  KEY `isdeleted` (`isdeleted`) USING BTREE,
  KEY `isrisk` (`isrisk`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=798004 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_va 结构
CREATE TABLE IF NOT EXISTS `doopsun_va` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) DEFAULT NULL COMMENT '商户号',
  `type` varchar(50) DEFAULT NULL COMMENT '类型：0全球，1本地',
  `vaRegCountry` varchar(50) DEFAULT NULL COMMENT '账户国家',
  `vaName` varchar(255) DEFAULT NULL COMMENT '账户名称',
  `vaNo` varchar(255) DEFAULT NULL COMMENT 'va账户',
  `currency` varchar(50) DEFAULT NULL COMMENT '币种',
  `openBankName` varchar(255) DEFAULT NULL COMMENT '开户银行',
  `localBankRouteNo` varchar(150) DEFAULT NULL COMMENT 'ACH路由号码',
  `swiftCode` varchar(50) DEFAULT NULL COMMENT 'swift Code',
  `bankAddress` text COMMENT '银行地址',
  `status` varchar(50) DEFAULT NULL COMMENT '0待分配，1已分配，删除',
  `createTime` varchar(50) DEFAULT NULL COMMENT '创建时间',
  `refundFinalAt` varchar(50) DEFAULT NULL COMMENT '退款终止时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_type` (`type`) USING BTREE,
  KEY `idx_vaRegCountry` (`vaRegCountry`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `idx_createTime` (`createTime`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='va账户管理表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_va_apply 结构
CREATE TABLE IF NOT EXISTS `doopsun_va_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) DEFAULT NULL COMMENT '商户号',
  `applyDate` varchar(50) DEFAULT NULL COMMENT '申请时间',
  `applyNum` varchar(50) DEFAULT NULL COMMENT '申请数量',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT NULL COMMENT '处理中，1成功',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_merchantId` (`merchantId`) USING BTREE,
  KEY `idx_applyDate` (`applyDate`) USING BTREE,
  KEY `idx_applyNum` (`applyNum`) USING BTREE,
  KEY `idx_remark` (`remark`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='va账户申请记录表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) DEFAULT NULL COMMENT '商户用户id',
  `card_holder_id` varchar(100) NOT NULL COMMENT '持卡用户id',
  `budget_id` varchar(100) NOT NULL DEFAULT '1' COMMENT '账户id',
  `pp_card_id` varchar(255) NOT NULL COMMENT '卡片唯一标识符',
  `use_e_card` tinyint(1) NOT NULL COMMENT '是否使用账户余额，1是，0不是',
  `cardcode` varchar(100) NOT NULL COMMENT '卡号',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '卡余额',
  `type` varchar(100) DEFAULT NULL COMMENT '卡的类型',
  `safecode` varchar(20) NOT NULL COMMENT '安全码',
  `startime` varchar(50) DEFAULT NULL COMMENT '起始时间',
  `endtime` varchar(50) DEFAULT NULL COMMENT '截止日期',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `billing_currency` varchar(50) DEFAULT NULL COMMENT '币种',
  `status` varchar(50) NOT NULL DEFAULT '0' COMMENT '卡状态',
  `cancellation_in_advance` tinyint(1) DEFAULT NULL COMMENT '是否提前取消，0否，1是',
  `apply_for_cancellation` tinyint(1) DEFAULT NULL COMMENT '申请卡注销0否，1是',
  `channel_id` int(11) DEFAULT '1' COMMENT '频道id',
  `is_3ds` tinyint(1) DEFAULT '1' COMMENT '是否3ds认证(默认1,是2否1)',
  `remark` text COMMENT '备注',
  `transaction_id` varchar(100) DEFAULT '' COMMENT '交易ID',
  `card_bin_id` varchar(100) DEFAULT '' COMMENT '卡段ID',
  `total_auth_limit` varchar(100) DEFAULT '' COMMENT '子卡限额',
  `primary_card_id` varchar(100) DEFAULT '' COMMENT '主卡ID',
  `auth_limit_flag` varchar(100) DEFAULT '' COMMENT '是否限额。Y：是，N：否',
  `used_auth_limit` varchar(100) DEFAULT '' COMMENT '子卡已使用额度',
  `card_level` varchar(100) DEFAULT '' COMMENT 'SubCard子卡 MasterCard主卡',
  `currency` varchar(100) DEFAULT '' COMMENT '卡币种',
  `expiry` varchar(100) DEFAULT '' COMMENT '过期时间',
  `fail_reason` varchar(200) DEFAULT NULL COMMENT '失败原因',
  `merchant_fee` varchar(500) DEFAULT NULL COMMENT '手续费数据',
  `card_brand` varchar(100) DEFAULT '' COMMENT '卡品牌',
  `arrivalAmount` varchar(50) DEFAULT '' COMMENT '到账金额',
  `transactionLimit` varchar(50) DEFAULT '' COMMENT '可交易额度',
  `rechargeAmount` varchar(50) DEFAULT '' COMMENT '转入金额',
  `maxOnPercent` varchar(50) DEFAULT '' COMMENT '单笔交易最大金额',
  `maxOnMonthly` varchar(50) DEFAULT '' COMMENT '月交易限额',
  `maxOnDaily` varchar(50) DEFAULT '' COMMENT '日交易限额',
  `is_agent` tinyint(2) DEFAULT '0' COMMENT '是否三方代理卡',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cardtype` (`budget_id`) USING BTREE,
  KEY `usetype` (`use_e_card`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='虚拟卡信息表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_account 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantid` int(11) NOT NULL COMMENT '商户id',
  `budget_id` varchar(100) NOT NULL DEFAULT '0' COMMENT '预算账户id',
  `account_name` varchar(200) NOT NULL COMMENT '账户名称',
  `amount` decimal(10,2) NOT NULL COMMENT '账户金额',
  `currency` datetime DEFAULT NULL COMMENT '币种',
  `status` tinyint(11) NOT NULL COMMENT '状态，1正常',
  `created_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='虚拟卡预算账户表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_apply 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(150) DEFAULT NULL COMMENT '卡片类别 1/2 3DS认证卡/非3DS认证卡',
  `apply_num` int(11) NOT NULL COMMENT '注册可申请数量',
  `apply_json` varchar(255) DEFAULT NULL COMMENT '消费记录时间窗规则(单位：月)：时间窗（月）months\n，消费次数 times，可追加数量 num',
  `channel_id` varchar(100) NOT NULL COMMENT '通道_id',
  `created_time` varchar(50) DEFAULT NULL COMMENT '添加时间',
  `update_time` varchar(50) DEFAULT NULL COMMENT '更新时间',
  `status` varchar(50) NOT NULL DEFAULT '1' COMMENT '卡状态默认1（激活1，未激活2）',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='虚拟卡申请限制管理表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_authorize 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_authorize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authorization_id` varchar(100) NOT NULL COMMENT '授权id',
  `pp_card_id` varchar(100) NOT NULL COMMENT '卡id',
  `budget_id` varchar(100) NOT NULL DEFAULT '1' COMMENT '预算账户id',
  `reversal_authorization_id` varchar(100) NOT NULL COMMENT '撤销授权id',
  `billing_amount` decimal(10,2) NOT NULL COMMENT '结算金额',
  `threeds_fee_currency` varchar(100) NOT NULL COMMENT '3ds费用货币',
  `merchant_currency` varchar(50) NOT NULL COMMENT '商户国家',
  `rate_fee_currency` varchar(50) NOT NULL COMMENT '费用货币',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `auth_fee` decimal(10,2) NOT NULL COMMENT '授权交易费',
  `mcc` varchar(50) NOT NULL COMMENT 'mcc',
  `authorization_date` varchar(50) NOT NULL COMMENT '授权日期',
  `rate_fee` decimal(10,2) DEFAULT NULL COMMENT '交易费用',
  `billing_currency` varchar(50) DEFAULT NULL COMMENT '结算货币',
  `authorization_status` varchar(50) DEFAULT NULL COMMENT '(DECLINED已删除,APPROVED已批准)',
  `merchant_amount` decimal(10,2) DEFAULT NULL COMMENT '原始订单金额',
  `approval_code` varchar(50) DEFAULT NULL COMMENT '批准代码',
  `reversal_txn_date` varchar(50) DEFAULT NULL COMMENT '反转TxnDate',
  `card_number` varchar(50) DEFAULT NULL COMMENT '卡号',
  `transaction_processing_fee` decimal(10,2) DEFAULT '0.00' COMMENT '交易处理费',
  `merchant_name` varchar(50) DEFAULT NULL COMMENT '商户头衔',
  `transaction_processing_fee_currency` varchar(50) DEFAULT NULL COMMENT '交易处理费货币',
  `authorization_type` varchar(50) DEFAULT NULL COMMENT 'Auth,Purchase Return, Reversa',
  `threeds_fee` decimal(10,2) DEFAULT '0.00' COMMENT '3ds费用',
  `merchant_country` varchar(50) DEFAULT NULL COMMENT '商户城市',
  `auth_fee_currency` varchar(50) DEFAULT NULL COMMENT '授权交易费货币',
  `fail_reason` varchar(100) DEFAULT NULL COMMENT '失败原因',
  `created_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='接收卡授权记录表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_cost 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_cost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardtype` tinyint(1) DEFAULT '0' COMMENT '卡类型',
  `type_id` varchar(150) DEFAULT NULL COMMENT '费用类别_id',
  `channel_id` varchar(100) NOT NULL COMMENT '通道_id',
  `created_time` varchar(50) DEFAULT NULL COMMENT '添加时间',
  `update_time` varchar(50) DEFAULT NULL COMMENT '更新时间',
  `minrebate` decimal(10,2) DEFAULT '0.00' COMMENT '返点政策流水区间min',
  `maxrebate` decimal(10,2) DEFAULT '0.00' COMMENT '返点政策流水区间max',
  `rebate` float(5,2) DEFAULT '0.00' COMMENT '返点率',
  `status` varchar(50) NOT NULL DEFAULT '1' COMMENT '卡状态默认1（激活1，未激活2）',
  `cost_json` varchar(255) DEFAULT NULL COMMENT '费用类型：固定，计费方式：固定额/百分比，值；计费周期：计费周期 及时/非及时 is_time 1/2 及时就是操作当时收取，非及时就是按月、年周期收费 cost_times 0 表示立即收取，1表示按天收取，2表示按月收取，3表示按年收取',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type_id` (`type_id`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='虚拟卡费用管理表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_curllog 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_curllog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(200) NOT NULL COMMENT '请求接口',
  `request` text NOT NULL COMMENT '请求参数',
  `content` text COMMENT '返回数据',
  `curltype` varchar(100) NOT NULL COMMENT '请求类型',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '接口描述',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2340 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='请求接口的日志表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_dispense 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_dispense` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL COMMENT '账户id',
  `card_id` int(11) NOT NULL COMMENT '虚拟卡id',
  `amount` decimal(10,2) NOT NULL COMMENT '分配金额',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态，1正常',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 ROW_FORMAT=FIXED COMMENT='虚拟卡分配金额记录表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_holder 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_holder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `card_holder_id` varchar(100) NOT NULL COMMENT '持卡人id',
  `budget_id` varchar(100) NOT NULL COMMENT '账户id',
  `calling_prefix` varchar(50) NOT NULL COMMENT '电话区号',
  `mobile` varchar(50) NOT NULL COMMENT '手机号',
  `email` varchar(50) NOT NULL COMMENT '电子邮件',
  `first_name` varchar(50) NOT NULL COMMENT '名字',
  `last_name` varchar(50) NOT NULL COMMENT '姓氏',
  `date_of_birth` varchar(50) NOT NULL COMMENT '出生日期',
  `address_line` varchar(200) NOT NULL COMMENT '详情地址',
  `city` varchar(100) NOT NULL COMMENT '城市',
  `state` varchar(100) NOT NULL COMMENT '州/省',
  `post_code` varchar(100) NOT NULL COMMENT '邮政编码',
  `country_code` varchar(50) NOT NULL COMMENT '国家编码CHN',
  `security_index` varchar(255) NOT NULL COMMENT '密保问题',
  `security_answer` varchar(255) NOT NULL COMMENT '密保问题答案',
  `created_time` datetime NOT NULL COMMENT '创建时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `certType` varchar(100) DEFAULT '' COMMENT '身份证件类型。id_card：身份证，passport：护照，resident_permit：居留许可证(永居、绿卡、工作签证）',
  `portrait` varchar(200) DEFAULT '' COMMENT '身份证件信息正面：请提供身份证件正面或护照首页照片',
  `reverseSide` varchar(200) DEFAULT '' COMMENT '身份证件信息反面：请提供身份证件反面照片',
  `nationalityCountryCode` varchar(200) DEFAULT '' COMMENT '国籍国家码二字码',
  `certCountryCode` varchar(200) DEFAULT '' COMMENT '证件签发国国家二字码',
  `certId` varchar(200) DEFAULT '' COMMENT '证件号',
  `country_code_two` varchar(100) DEFAULT '' COMMENT '账单地国家码二字码。建议填写账单地址国家码二字码',
  `memberId` varchar(100) DEFAULT '' COMMENT '会员号',
  `status` varchar(100) DEFAULT '' COMMENT '状态',
  `cardholderReviewStatus` varchar(100) DEFAULT '' COMMENT '用卡人信息审核状态',
  `idInfoRequirement` varchar(100) DEFAULT '' COMMENT '用卡人身份信息上传要求',
  `reason` varchar(100) DEFAULT '' COMMENT '原因',
  `cardtype` varchar(50) DEFAULT '1' COMMENT '卡类型',
  `isLegal` varchar(50) DEFAULT '1' COMMENT '是否法人',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='虚拟卡持卡人信息';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_info 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardid` int(11) NOT NULL COMMENT '虚拟卡id',
  `day_amount_limit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '每日限额',
  `month_amount_limit` decimal(10,2) DEFAULT NULL COMMENT '每月限额',
  `week_amount_limit` decimal(10,2) DEFAULT NULL COMMENT '每周限额',
  `remark` varchar(200) DEFAULT NULL COMMENT '卡备注',
  `validity_end_date` varchar(100) DEFAULT NULL COMMENT '该卡将在有效期结束后被停用',
  `allow_card_out` tinyint(1) DEFAULT NULL COMMENT '是否允许分摊',
  `transaction_amount_limit` decimal(10,2) DEFAULT NULL COMMENT '单次授权限制',
  `infotype` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1虚拟卡 2实体卡',
  `transactions_max` decimal(10,2) DEFAULT NULL COMMENT ' 最大交易额 ',
  `total_amount_limit` decimal(10,2) DEFAULT NULL COMMENT '授权总限额',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cardid` (`cardid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='卡片详情表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_limit 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `pp_card_id` varchar(255) NOT NULL COMMENT '卡片ID',
  `single_limit` decimal(12,2) DEFAULT '0.00' COMMENT '单笔限额',
  `daily_limit` decimal(12,2) DEFAULT '0.00' COMMENT '日限额',
  `monthly_limit` decimal(12,2) DEFAULT '0.00' COMMENT '月限额',
  `total_limit` decimal(12,2) DEFAULT '0.00' COMMENT '总限额',
  `single_used` decimal(12,2) DEFAULT '0.00' COMMENT '单笔限额已使用',
  `daily_used` decimal(12,2) DEFAULT '0.00' COMMENT '日限额已使用',
  `monthly_used` decimal(12,2) DEFAULT '0.00' COMMENT '月限额已使用',
  `total_used` decimal(12,2) DEFAULT '0.00' COMMENT '总限额已使用',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：1-有效，0-无效',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE,
  KEY `uk_pp_card_id` (`pp_card_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='虚拟卡限额表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_log 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardid` varchar(200) NOT NULL COMMENT '虚拟卡id',
  `content` varchar(255) DEFAULT NULL COMMENT '操作描述',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cardid` (`cardid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='卡片操作记录表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_order 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(100) NOT NULL COMMENT '清算id',
  `budget_id` varchar(100) NOT NULL COMMENT '预算账户id',
  `authorization_id` varchar(100) DEFAULT NULL COMMENT '授权id',
  `pp_card_id` varchar(100) DEFAULT NULL COMMENT '卡的唯一id',
  `card_number` varchar(100) NOT NULL DEFAULT '1' COMMENT '卡号',
  `last4` varchar(50) DEFAULT NULL COMMENT '卡的后四位',
  `billing_amount` decimal(10,2) DEFAULT NULL COMMENT '结算金额',
  `merchant_amount` decimal(10,2) DEFAULT NULL COMMENT '原始订单金额',
  `transaction_date` varchar(50) DEFAULT NULL COMMENT '交易日期',
  `approval_code` varchar(50) DEFAULT NULL COMMENT '批准代码',
  `cross_border_fee` decimal(10,2) DEFAULT NULL COMMENT '跨境费用',
  `merchant_currency` varchar(50) DEFAULT NULL COMMENT '原始订单货币',
  `merchant_name` varchar(50) DEFAULT NULL COMMENT '商户头衔',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `mcc` varchar(50) DEFAULT NULL COMMENT 'Mcc',
  `type` varchar(50) DEFAULT NULL COMMENT 'Credit/Debit',
  `cross_border_fee_currency` varchar(50) DEFAULT NULL COMMENT '跨境费用货币',
  `billing_currency` varchar(50) DEFAULT NULL COMMENT '结算货币',
  `merchant_country` varchar(50) DEFAULT NULL COMMENT '商户城市',
  `posting_date` varchar(50) DEFAULT NULL COMMENT '发布日期',
  `created_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `cardid` (`transaction_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='卡片交易记录表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_recharge 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_recharge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) NOT NULL COMMENT '用户id',
  `pp_card_id` varchar(200) DEFAULT '0' COMMENT '充值卡的id',
  `ordercode` varchar(255) DEFAULT NULL COMMENT '流水号',
  `price` decimal(10,2) DEFAULT NULL COMMENT '金额',
  `type` tinyint(1) DEFAULT '1' COMMENT '充值类型：1德普币充值，2卡充值',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0审核中 1充值成功 2充值失败',
  `remark` text,
  `imageurl` varchar(255) DEFAULT NULL COMMENT '汇款凭证',
  `channel_id` int(11) DEFAULT '1' COMMENT '频道id',
  `type_id` int(11) DEFAULT '1' COMMENT '卡类别id',
  `fee` decimal(10,2) DEFAULT NULL COMMENT '手续费',
  `pay_amount` decimal(10,2) DEFAULT NULL COMMENT '总金额',
  `paymentMethod` varchar(255) DEFAULT NULL COMMENT '支付方式',
  `account_type` tinyint(1) DEFAULT '1' COMMENT '充值账户：1普通账户，2专用账户',
  `transaction_id` varchar(200) DEFAULT NULL COMMENT '交易id',
  `authorization_id` varchar(200) DEFAULT '' COMMENT '交易ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchantId` (`merchantId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户充值表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_requestlog 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_requestlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantid` int(11) DEFAULT NULL COMMENT '商户id',
  `requestContent` text COMMENT '请求数据',
  `backContent` text COMMENT '返回数据',
  `callbackContent` text COMMENT '对方返回数据',
  `createdTime` int(11) DEFAULT NULL COMMENT '创建时间',
  `updateTime` int(11) DEFAULT NULL COMMENT '更新时间',
  `remark` text COMMENT '备注',
  `request_ip` varchar(100) DEFAULT '' COMMENT '请求IP',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=207 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_send 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_send` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(30) DEFAULT NULL COMMENT '手机号',
  `email` varchar(200) DEFAULT NULL COMMENT '邮箱',
  `content` varchar(500) DEFAULT NULL COMMENT '内容',
  `send_code` varchar(100) DEFAULT NULL COMMENT '发送通知code',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='短信/邮件发送表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_token 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_token` (
  `id` int(13) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '',
  `value` text COMMENT 'token值',
  `expires_in` varchar(30) DEFAULT NULL COMMENT '过期时间',
  `update_time` varchar(58) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='虚拟卡token信息表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_virtualcard_warning 结构
CREATE TABLE IF NOT EXISTS `doopsun_virtualcard_warning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchantId` varchar(50) NOT NULL COMMENT '商户编码',
  `type` tinyint(1) NOT NULL COMMENT '1 德普币余额预警 2卡余额预警 ',
  `send_type` tinyint(1) DEFAULT '1' COMMENT '1手机 2邮箱',
  `cardId` varchar(50) DEFAULT NULL COMMENT '卡号',
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '金额',
  `send_num` varchar(100) DEFAULT NULL COMMENT '手机号/邮箱号',
  `send_code` varchar(200) DEFAULT NULL COMMENT '发送通知code',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `merchantId` (`merchantId`) USING BTREE,
  KEY `cardId` (`cardId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='预警表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_warnings 结构
CREATE TABLE IF NOT EXISTS `doopsun_warnings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL COMMENT '交易ID',
  `merchant_no` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商户号',
  `merchant_order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商户订单号',
  `channel_order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通道订单号',
  `trade_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易网址',
  `currency` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '币种',
  `amount` decimal(12,2) NOT NULL COMMENT '金额',
  `card_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '卡类型',
  `trade_time` datetime NOT NULL COMMENT '交易时间',
  `risk_level` enum('low','mid','high') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '风险等级',
  `rule_name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '命中规则名',
  `measure` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '执行措施',
  `warning_time` datetime NOT NULL COMMENT '预警时间',
  `status` enum('pending','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT '状态',
  `resolved_at` datetime DEFAULT NULL COMMENT '解决时间',
  `resolved_by` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '解决人',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `transaction_id` (`transaction_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `warning_time` (`warning_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='预警记录';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_withdraw 结构
CREATE TABLE IF NOT EXISTS `doopsun_withdraw` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '提款申请ID',
  `merchant_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户号',
  `bankcard_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户银行卡号',
  `withdraw_amount` decimal(10,2) unsigned DEFAULT NULL COMMENT '提款金额',
  `withdraw_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提款申请时间',
  `withdraw_state` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '提款状态（0待审核 1 处理中 2拒绝 3提款成功）',
  `remarks` varchar(100) DEFAULT NULL COMMENT '备注',
  `withdraw_state_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提款状态时间',
  `withdraw_type` int(1) NOT NULL DEFAULT '1' COMMENT '1为正常提款，2为保证金提款',
  `ids` text COMMENT '结算的id',
  `settlement_time` int(11) DEFAULT '0' COMMENT '结算的日期',
  `deposits_settlement_time` int(11) DEFAULT '0' COMMENT '保证金结算时间',
  `is_old` int(1) DEFAULT '1' COMMENT '1是2否',
  `max_dishonor_id` int(11) DEFAULT '0' COMMENT '拒付最大id',
  `max_refund_id` int(11) DEFAULT '0' COMMENT '最大的退款ID',
  `max_punish_id` int(11) DEFAULT '0' COMMENT '最大拒处id',
  `withdraw_fee` decimal(10,2) DEFAULT '0.00' COMMENT '提款手续费',
  `max_yujing_id` int(11) DEFAULT '0' COMMENT '最大预警费id',
  `order_max_id` int(11) DEFAULT '0' COMMENT '最大处理费id',
  `threeds_max_id` int(11) DEFAULT '0' COMMENT '最大3ds处理id',
  `refund_max_id` int(11) DEFAULT '0' COMMENT '最大退款手续费id',
  `banktype` tinyint(1) DEFAULT '1' COMMENT '银行类型，1银行卡，2va账户',
  `exchange_rate` decimal(10,5) DEFAULT '0.00000' COMMENT '最新汇率',
  `withdraw_amount_rmb` decimal(10,2) DEFAULT '0.00' COMMENT '提款人民币',
  `fixed_deposit_money` decimal(10,2) DEFAULT '0.00' COMMENT '固定保证金费',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='提款申请表';

-- 数据导出被取消选择。

-- 导出  表 doopsun.doopsun_yujingorder 结构
CREATE TABLE IF NOT EXISTS `doopsun_yujingorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) NOT NULL DEFAULT '0' COMMENT '订单id',
  `merchant_order` varchar(40) NOT NULL COMMENT '商户订单号',
  `doopsun_orderid` varchar(155) NOT NULL DEFAULT '0' COMMENT '系统流水号',
  `orderamount` double(9,2) NOT NULL COMMENT '标价金额',
  `yujing_type` tinyint(1) NOT NULL COMMENT '预警类型1:RDR预警,2:Ethoca预警,3RDR及Ethoca预警',
  `yujing_merchantid` int(11) NOT NULL COMMENT '商户号',
  `yujing_punish` double(9,2) DEFAULT NULL COMMENT '预警处罚',
  `order_time` int(11) DEFAULT NULL COMMENT '下单时间',
  `cardnum` varchar(100) DEFAULT NULL COMMENT '卡号',
  `is_handle` tinyint(1) DEFAULT '1' COMMENT '是否处理，0已处理，1已处理',
  `email` varchar(255) DEFAULT NULL COMMENT '交易邮箱',
  `accessurl` varchar(255) DEFAULT NULL COMMENT '交易网址',
  `accessurl_id` int(11) DEFAULT '0' COMMENT '网址id',
  `yujing_style` tinyint(1) DEFAULT NULL COMMENT '是否真是预警，1真预警，2jiayujing',
  `yujing_time` int(11) NOT NULL COMMENT '预警时间',
  `remark` text COMMENT '备注',
  `order_money` decimal(10,2) DEFAULT '0.00' COMMENT '订单金额（人民币）',
  `if_jufu` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否拒绝预警 默认0 1拒绝',
  `jufu_time` int(11) NOT NULL COMMENT '拒付操作时间',
  `updated_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `dishonor_type` (`yujing_type`) USING BTREE,
  KEY `doopsun_orderid` (`doopsun_orderid`) USING BTREE,
  KEY `yujing_merchantid` (`yujing_merchantid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='预警订单';

-- 数据导出被取消选择。

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
