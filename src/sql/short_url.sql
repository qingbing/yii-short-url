-- ----------------------------
--  Table structure for `short_url_flag`
-- ----------------------------
CREATE TABLE `short_url_source` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `url` varchar(255) NOT NULL COMMENT '真实URL',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT 'URL的md5码',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_url` (`url`),
  KEY `idx_md5` (`md5`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短链系统URL资源库';

-- ----------------------------
--  Table structure for `short_url_flag`
-- ----------------------------
CREATE TABLE `short_url_flag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `url_source_id` bigint(20) unsigned NOT NULL COMMENT 'url资源ID',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT 'URL的md5码',
  `type` varchar(20) NOT NULL COMMENT '类型[permanent:永久,temporary:临时]',
  `desc` varchar(255) DEFAULT NULL COMMENT '描述',
  `flag` char(6) NOT NULL DEFAULT '' COMMENT '短链标记',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `times` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '链接调用次数',
  `expire_ip` varchar(255) NOT NULL DEFAULT '' COMMENT '有效IP地址',
  `expire_begin_date` date NOT NULL DEFAULT '1000-01-01' COMMENT '生效日期',
  `expire_end_date` date NOT NULL DEFAULT '1000-01-01' COMMENT '失效日期',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `access_at` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '最后访问时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_flag` (`flag`),
  KEY `idx_urlSourceId` (`url_source_id`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短链系统URL标记表';
