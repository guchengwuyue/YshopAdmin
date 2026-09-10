/*
 Navicat Premium Dump SQL

 Source Server         : mydb
 Source Server Type    : MySQL
 Source Server Version : 50726 (5.7.26)
 Source Host           : localhost:3306
 Source Schema         : yshopadmin1.0

 Target Server Type    : MySQL
 Target Server Version : 50726 (5.7.26)
 File Encoding         : 65001

 Date: 09/09/2026 10:03:05
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for gen_table
-- ----------------------------
DROP TABLE IF EXISTS `gen_table`;
CREATE TABLE `gen_table`  (
  `table_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `table_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '表名称',
  `table_comment` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '表描述',
  `sub_table_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '关联子表的表名',
  `sub_table_fk_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '子表关联的外键名',
  `class_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '实体类名称',
  `tpl_category` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'crud' COMMENT '使用的模板（crud单表操作 tree树表操作 sub主子表操作）',
  `package_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '生成包路径',
  `module_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '生成模块名',
  `business_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '生成业务名',
  `function_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '生成功能名',
  `function_author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '生成功能作者',
  `form_col_num` int(1) NULL DEFAULT 1 COMMENT '表单布局（单列 双列 三列）',
  `gen_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '生成代码方式（0zip压缩包 1自定义路径）',
  `gen_path` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '/' COMMENT '生成路径（不填默认项目路径）',
  `options` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '其它生成选项',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`table_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '代码生成业务表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of gen_table
-- ----------------------------
INSERT INTO `gen_table` VALUES (1, 'sys_role', '角色信息表', NULL, NULL, 'Role', 'crud', 'app', 'system', 'role', '角色信息表', 'admin', 1, '0', '/', NULL, 'admin', '2026-09-05 23:30:42', '', NULL, NULL);

-- ----------------------------
-- Table structure for gen_table_column
-- ----------------------------
DROP TABLE IF EXISTS `gen_table_column`;
CREATE TABLE `gen_table_column`  (
  `column_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `table_id` bigint(20) NULL DEFAULT NULL COMMENT '归属表编号',
  `column_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '列名称',
  `column_comment` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '列描述',
  `column_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '列类型',
  `java_type` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'JAVA类型',
  `java_field` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'JAVA字段名',
  `is_pk` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '是否主键（1是）',
  `is_increment` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '是否自增（1是）',
  `is_required` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '是否必填（1是）',
  `is_insert` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '是否为插入字段（1是）',
  `is_edit` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '是否编辑字段（1是）',
  `is_list` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '是否列表字段（1是）',
  `is_query` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '是否查询字段（1是）',
  `query_type` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'EQ' COMMENT '查询方式（等于、不等于、大于、小于、范围）',
  `html_type` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '显示类型（文本框、文本域、下拉框、复选框、单选框、日期控件）',
  `dict_type` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '字典类型',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`column_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '代码生成业务表字段' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of gen_table_column
-- ----------------------------
INSERT INTO `gen_table_column` VALUES (1, 1, 'role_id', '角色ID', 'bigint(20)', 'int', 'roleId', '1', '1', '0', '0', '0', '1', '0', 'EQ', 'input', '', 1, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (2, 1, 'role_name', '角色名称', 'varchar(30)', 'string', 'roleName', '0', '0', '1', '1', '1', '1', '0', 'EQ', 'input', '', 2, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (3, 1, 'role_key', '角色权限字符串', 'varchar(100)', 'string', 'roleKey', '0', '0', '1', '1', '1', '1', '0', 'EQ', 'input', '', 3, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (4, 1, 'role_sort', '显示顺序', 'int(4)', 'int', 'roleSort', '0', '0', '1', '1', '1', '1', '0', 'EQ', 'input', '', 4, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (5, 1, 'data_scope', '数据范围（1：全部数据权限 2：自定数据权限 3：本部门数据权限 4：本部门及以下数据权限）', 'char(1)', 'string', 'dataScope', '0', '0', '0', '1', '1', '1', '0', 'EQ', 'input', '', 5, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (6, 1, 'status', '角色状态（0正常 1停用）', 'char(1)', 'string', 'status', '0', '0', '1', '1', '1', '1', '0', 'EQ', 'select', '', 6, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (7, 1, 'del_flag', '删除标志（0代表存在 2代表删除）', 'char(1)', 'string', 'delFlag', '0', '0', '0', '1', '1', '1', '0', 'EQ', 'input', '', 7, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (8, 1, 'create_by', '创建者', 'varchar(64)', 'string', 'createBy', '0', '0', '0', '0', '0', '0', '0', 'EQ', 'input', '', 8, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (9, 1, 'create_time', '创建时间', 'datetime', 'string', 'createTime', '0', '0', '0', '0', '0', '0', '0', 'EQ', 'datetime', '', 9, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (10, 1, 'update_by', '更新者', 'varchar(64)', 'string', 'updateBy', '0', '0', '0', '0', '0', '0', '0', 'EQ', 'input', '', 10, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (11, 1, 'update_time', '更新时间', 'datetime', 'string', 'updateTime', '0', '0', '0', '0', '0', '0', '0', 'EQ', 'datetime', '', 11, 'admin', '2026-09-05 23:30:42', '', NULL);
INSERT INTO `gen_table_column` VALUES (12, 1, 'remark', '备注', 'varchar(500)', 'string', 'remark', '0', '0', '0', '0', '0', '1', '0', 'EQ', 'input', '', 12, 'admin', '2026-09-05 23:30:42', '', NULL);

-- ----------------------------
-- Table structure for sys_addon
-- ----------------------------
DROP TABLE IF EXISTS `sys_addon`;
CREATE TABLE `sys_addon`  (
  `addon_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '插件ID',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '插件标识',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '插件名称',
  `version` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '版本',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '描述',
  `author` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '作者',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '状态（0停用 1启用）',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '配置JSON快照',
  `install_time` datetime NULL DEFAULT NULL COMMENT '安装时间',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`addon_id`) USING BTREE,
  UNIQUE INDEX `uk_name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '插件注册表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_addon
-- ----------------------------

-- ----------------------------
-- Table structure for sys_config
-- ----------------------------
DROP TABLE IF EXISTS `sys_config`;
CREATE TABLE `sys_config`  (
  `config_id` int(5) NOT NULL AUTO_INCREMENT COMMENT '参数主键',
  `config_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '参数名称',
  `config_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '参数键名',
  `config_value` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '参数键值',
  `config_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '系统内置（Y是 N否）',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`config_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 101 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '参数配置表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_config
-- ----------------------------
INSERT INTO `sys_config` VALUES (1, '主框架页-默认皮肤样式名称', 'sys.index.skinName', 'skin-blue', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '蓝色 skin-blue、绿色 skin-green、紫色 skin-purple、红色 skin-red、黄色 skin-yellow');
INSERT INTO `sys_config` VALUES (2, '用户管理-账号初始密码', 'sys.user.initPassword', '123456', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '初始化密码 123456');
INSERT INTO `sys_config` VALUES (3, '主框架页-侧边栏主题', 'sys.index.sideTheme', 'theme-dark', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '深黑主题theme-dark，浅色主题theme-light，深蓝主题theme-blue');
INSERT INTO `sys_config` VALUES (4, '账号自助-是否开启用户注册功能', 'sys.account.registerUser', 'false', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '是否开启注册用户功能（true开启，false关闭）');
INSERT INTO `sys_config` VALUES (5, '用户管理-密码字符范围', 'sys.account.chrtype', '0', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '默认任意字符范围，0任意（密码可以输入任意字符），1数字（密码只能为0-9数字），2英文字母（密码只能为a-z和A-Z字母），3字母和数字（密码必须包含字母，数字）,4字母数字和特殊字符（目前支持的特殊字符包括：~!@#$%^&*()-=_+）');
INSERT INTO `sys_config` VALUES (6, '用户管理-初始密码修改策略', 'sys.account.initPasswordModify', '1', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '0：初始密码修改策略关闭，没有任何提示，1：提醒用户，如果未修改初始密码，则在登录时就会提醒修改密码对话框');
INSERT INTO `sys_config` VALUES (7, '用户管理-账号密码更新周期', 'sys.account.passwordValidateDays', '0', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '密码更新周期（填写数字，数据初始化值为0不限制，若修改必须为大于0小于365的正整数），如果超过这个周期登录系统时，则在登录时就会提醒修改密码对话框');
INSERT INTO `sys_config` VALUES (8, '主框架页-菜单导航显示风格', 'sys.index.menuStyle', 'default', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '菜单导航显示风格（default为左侧导航菜单，topnav为顶部导航菜单）');
INSERT INTO `sys_config` VALUES (9, '主框架页-是否开启页脚', 'sys.index.footer', 'true', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '是否开启底部页脚显示（true显示，false隐藏）');
INSERT INTO `sys_config` VALUES (10, '主框架页-是否开启页签', 'sys.index.tagsView', 'true', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '是否开启菜单多页签显示（true显示，false隐藏）');
INSERT INTO `sys_config` VALUES (11, '用户登录-黑名单列表', 'sys.login.blackIPList', '', 'Y', 'admin', '2026-09-03 16:41:16', '', NULL, '设置登录IP黑名单限制，多个匹配项以;分隔，支持匹配（*通配、网段）');
INSERT INTO `sys_config` VALUES (100, '演示模式开启', 'sys.demo.enabled', 'false', 'Y', 'admin', '2026-09-06 15:46:34', '', NULL, '');

-- ----------------------------
-- Table structure for sys_dept
-- ----------------------------
DROP TABLE IF EXISTS `sys_dept`;
CREATE TABLE `sys_dept`  (
  `dept_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '部门id',
  `parent_id` bigint(20) NULL DEFAULT 0 COMMENT '父部门id',
  `ancestors` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '祖级列表',
  `dept_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '部门名称',
  `order_num` int(4) NULL DEFAULT 0 COMMENT '显示顺序',
  `leader` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '负责人',
  `phone` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '部门状态（0正常 1停用）',
  `del_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '删除标志（0代表存在 2代表删除）',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`dept_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 201 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '部门表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_dept
-- ----------------------------
INSERT INTO `sys_dept` VALUES (100, 0, '0', '意象科技', 0, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (101, 100, '0,100', '深圳总公司', 1, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (102, 100, '0,100', '长沙分公司', 2, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (103, 101, '0,100,101', '研发部门', 1, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (104, 101, '0,100,101', '市场部门', 2, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (105, 101, '0,100,101', '测试部门', 3, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (106, 101, '0,100,101', '财务部门', 4, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (107, 101, '0,100,101', '运维部门', 5, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (108, 102, '0,100,102', '市场部门', 1, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (109, 102, '0,100,102', '财务部门', 2, '意象', '15888888888', 'ry@qq.com', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL);
INSERT INTO `sys_dept` VALUES (200, 103, '0,100,101,103', '全工程师', 0, 'yshop先生', '', '', '0', '0', 'admin', '2026-09-06 08:13:44', '', NULL);

-- ----------------------------
-- Table structure for sys_dict_data
-- ----------------------------
DROP TABLE IF EXISTS `sys_dict_data`;
CREATE TABLE `sys_dict_data`  (
  `dict_code` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '字典编码',
  `dict_sort` int(4) NULL DEFAULT 0 COMMENT '字典排序',
  `dict_label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '字典标签',
  `dict_value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '字典键值',
  `dict_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '字典类型',
  `css_class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '样式属性（其他样式扩展）',
  `list_class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '表格回显样式',
  `is_default` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'N' COMMENT '是否默认（Y是 N否）',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '状态（0正常 1停用）',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`dict_code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 100 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '字典数据表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_dict_data
-- ----------------------------
INSERT INTO `sys_dict_data` VALUES (1, 1, '男', '0', 'sys_user_sex', '', '', 'Y', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '性别男');
INSERT INTO `sys_dict_data` VALUES (2, 2, '女', '1', 'sys_user_sex', '', '', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '性别女');
INSERT INTO `sys_dict_data` VALUES (3, 3, '未知', '2', 'sys_user_sex', '', '', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '性别未知');
INSERT INTO `sys_dict_data` VALUES (4, 1, '显示', '0', 'sys_show_hide', '', 'primary', 'Y', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '显示菜单');
INSERT INTO `sys_dict_data` VALUES (5, 2, '隐藏', '1', 'sys_show_hide', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '隐藏菜单');
INSERT INTO `sys_dict_data` VALUES (6, 1, '正常', '0', 'sys_normal_disable', '', 'primary', 'Y', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '正常状态');
INSERT INTO `sys_dict_data` VALUES (7, 2, '停用', '1', 'sys_normal_disable', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '停用状态');
INSERT INTO `sys_dict_data` VALUES (8, 1, '正常', '0', 'sys_job_status', '', 'primary', 'Y', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '正常状态');
INSERT INTO `sys_dict_data` VALUES (9, 2, '暂停', '1', 'sys_job_status', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '停用状态');
INSERT INTO `sys_dict_data` VALUES (10, 1, '默认', 'DEFAULT', 'sys_job_group', '', '', 'Y', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '默认分组');
INSERT INTO `sys_dict_data` VALUES (11, 2, '系统', 'SYSTEM', 'sys_job_group', '', '', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '系统分组');
INSERT INTO `sys_dict_data` VALUES (12, 1, '是', 'Y', 'sys_yes_no', '', 'primary', 'Y', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '系统默认是');
INSERT INTO `sys_dict_data` VALUES (13, 2, '否', 'N', 'sys_yes_no', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '系统默认否');
INSERT INTO `sys_dict_data` VALUES (14, 1, '通知', '1', 'sys_notice_type', '', 'warning', 'Y', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '通知');
INSERT INTO `sys_dict_data` VALUES (15, 2, '公告', '2', 'sys_notice_type', '', 'success', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '公告');
INSERT INTO `sys_dict_data` VALUES (16, 1, '正常', '0', 'sys_notice_status', '', 'primary', 'Y', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '正常状态');
INSERT INTO `sys_dict_data` VALUES (17, 2, '关闭', '1', 'sys_notice_status', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '关闭状态');
INSERT INTO `sys_dict_data` VALUES (18, 99, '其他', '0', 'sys_oper_type', '', 'info', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '其他操作');
INSERT INTO `sys_dict_data` VALUES (19, 1, '新增', '1', 'sys_oper_type', '', 'info', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '新增操作');
INSERT INTO `sys_dict_data` VALUES (20, 2, '修改', '2', 'sys_oper_type', '', 'info', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '修改操作');
INSERT INTO `sys_dict_data` VALUES (21, 3, '删除', '3', 'sys_oper_type', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '删除操作');
INSERT INTO `sys_dict_data` VALUES (22, 4, '授权', '4', 'sys_oper_type', '', 'primary', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '授权操作');
INSERT INTO `sys_dict_data` VALUES (23, 5, '导出', '5', 'sys_oper_type', '', 'warning', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '导出操作');
INSERT INTO `sys_dict_data` VALUES (24, 6, '导入', '6', 'sys_oper_type', '', 'warning', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '导入操作');
INSERT INTO `sys_dict_data` VALUES (25, 7, '强退', '7', 'sys_oper_type', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '强退操作');
INSERT INTO `sys_dict_data` VALUES (26, 8, '生成代码', '8', 'sys_oper_type', '', 'warning', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '生成操作');
INSERT INTO `sys_dict_data` VALUES (27, 9, '清空数据', '9', 'sys_oper_type', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '清空操作');
INSERT INTO `sys_dict_data` VALUES (28, 1, '成功', '0', 'sys_common_status', '', 'primary', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '正常状态');
INSERT INTO `sys_dict_data` VALUES (29, 2, '失败', '1', 'sys_common_status', '', 'danger', 'N', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '停用状态');

-- ----------------------------
-- Table structure for sys_dict_type
-- ----------------------------
DROP TABLE IF EXISTS `sys_dict_type`;
CREATE TABLE `sys_dict_type`  (
  `dict_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '字典主键',
  `dict_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '字典名称',
  `dict_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '字典类型',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '状态（0正常 1停用）',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`dict_id`) USING BTREE,
  UNIQUE INDEX `dict_type`(`dict_type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 100 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '字典类型表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_dict_type
-- ----------------------------
INSERT INTO `sys_dict_type` VALUES (1, '用户性别', 'sys_user_sex', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '用户性别列表');
INSERT INTO `sys_dict_type` VALUES (2, '菜单状态', 'sys_show_hide', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '菜单状态列表');
INSERT INTO `sys_dict_type` VALUES (3, '系统开关', 'sys_normal_disable', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '系统开关列表');
INSERT INTO `sys_dict_type` VALUES (4, '任务状态', 'sys_job_status', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '任务状态列表');
INSERT INTO `sys_dict_type` VALUES (5, '任务分组', 'sys_job_group', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '任务分组列表');
INSERT INTO `sys_dict_type` VALUES (6, '系统是否', 'sys_yes_no', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '系统是否列表');
INSERT INTO `sys_dict_type` VALUES (7, '通知类型', 'sys_notice_type', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '通知类型列表');
INSERT INTO `sys_dict_type` VALUES (8, '通知状态', 'sys_notice_status', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '通知状态列表');
INSERT INTO `sys_dict_type` VALUES (9, '操作类型', 'sys_oper_type', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '操作类型列表');
INSERT INTO `sys_dict_type` VALUES (10, '系统状态', 'sys_common_status', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '登录状态列表');

-- ----------------------------
-- Table structure for sys_job
-- ----------------------------
DROP TABLE IF EXISTS `sys_job`;
CREATE TABLE `sys_job`  (
  `job_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `job_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '任务名称',
  `job_group` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'DEFAULT' COMMENT '任务组名',
  `invoke_target` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '调用目标字符串',
  `cron_expression` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'cron执行表达式',
  `misfire_policy` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '3' COMMENT '计划执行错误策略（1立即执行 2执行一次 3放弃执行）',
  `concurrent` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1' COMMENT '是否并发执行（0允许 1禁止）',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '状态（0正常 1暂停）',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注信息',
  PRIMARY KEY (`job_id`, `job_name`, `job_group`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 100 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '定时任务调度表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_job
-- ----------------------------
INSERT INTO `sys_job` VALUES (1, '系统默认（无参）', 'DEFAULT', 'ryTask.ryNoParams', '0/10 * * * * ?', '3', '1', '1', 'admin', '2026-09-03 16:41:17', 'admin', '2026-09-07 10:15:02', '');
INSERT INTO `sys_job` VALUES (2, '系统默认（有参）', 'DEFAULT', 'ryTask.ryParams(\'ry\')', '0/15 * * * * ?', '3', '1', '1', 'admin', '2026-09-03 16:41:17', '', NULL, '');
INSERT INTO `sys_job` VALUES (3, '系统默认（多参）', 'DEFAULT', 'ryTask.ryMultipleParams(\'ry\', true, 2000L, 316.50D, 100)', '0/20 * * * * ?', '3', '1', '1', 'admin', '2026-09-03 16:41:17', '', NULL, '');

-- ----------------------------
-- Table structure for sys_job_log
-- ----------------------------
DROP TABLE IF EXISTS `sys_job_log`;
CREATE TABLE `sys_job_log`  (
  `job_log_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '任务日志ID',
  `job_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '任务名称',
  `job_group` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '任务组名',
  `invoke_target` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '调用目标字符串',
  `job_message` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '日志信息',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '执行状态（0正常 1失败）',
  `exception_info` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '异常信息',
  `start_time` datetime NULL DEFAULT NULL COMMENT '执行开始时间',
  `end_time` datetime NULL DEFAULT NULL COMMENT '执行结束时间',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`job_log_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '定时任务调度日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_job_log
-- ----------------------------
INSERT INTO `sys_job_log` VALUES (1, '系统默认（无参）', 'DEFAULT', 'ryTask.ryNoParams', 'success', '0', '', '2026-09-04 13:41:22', '2026-09-04 13:41:22', '2026-09-04 13:41:22');
INSERT INTO `sys_job_log` VALUES (2, '系统默认（无参）', 'DEFAULT', 'ryTask.ryNoParams', '[manual] success', '0', '', '2026-09-07 10:09:01', '2026-09-07 10:09:01', '2026-09-07 10:09:01');
INSERT INTO `sys_job_log` VALUES (3, '系统默认（无参）', 'DEFAULT', 'ryTask.ryNoParams', '[manual] failed', '1', 'Class \"app\\job\\Log\" not found', '2026-09-07 10:10:04', '2026-09-07 10:10:04', '2026-09-07 10:10:04');
INSERT INTO `sys_job_log` VALUES (4, '系统默认（无参）', 'DEFAULT', 'ryTask.ryNoParams', '[manual] success', '0', '', '2026-09-07 10:14:04', '2026-09-07 10:14:04', '2026-09-07 10:14:04');

-- ----------------------------
-- Table structure for sys_logininfor
-- ----------------------------
DROP TABLE IF EXISTS `sys_logininfor`;
CREATE TABLE `sys_logininfor`  (
  `info_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '访问ID',
  `login_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录账号',
  `ipaddr` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录IP地址',
  `login_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录地点',
  `browser` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '浏览器类型',
  `os` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '操作系统',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '登录状态（0成功 1失败）',
  `msg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '提示消息',
  `login_time` datetime NULL DEFAULT NULL COMMENT '访问时间',
  PRIMARY KEY (`info_id`) USING BTREE,
  INDEX `idx_sys_logininfor_s`(`status`) USING BTREE,
  INDEX `idx_sys_logininfor_lt`(`login_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 138 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统访问记录' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_logininfor
-- ----------------------------
INSERT INTO `sys_logininfor` VALUES (100, 'admin', '127.0.0.1', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb', '', '1', 'login success', '2026-09-03 16:43:10');
INSERT INTO `sys_logininfor` VALUES (101, 'admin', '127.0.0.1', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb', '', '1', 'login success', '2026-09-03 17:16:48');
INSERT INTO `sys_logininfor` VALUES (102, 'admin', '127.0.0.1', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb', '', '1', 'login success', '2026-09-03 17:57:52');
INSERT INTO `sys_logininfor` VALUES (103, 'admin', '127.0.0.1', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb', '', '1', 'logout', '2026-09-03 18:16:04');
INSERT INTO `sys_logininfor` VALUES (104, 'admin', '127.0.0.1', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb', '', '1', 'login success', '2026-09-03 18:16:10');
INSERT INTO `sys_logininfor` VALUES (105, 'admin', '127.0.0.1', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb', '', '1', 'logout', '2026-09-03 18:24:50');
INSERT INTO `sys_logininfor` VALUES (106, 'admin', '127.0.0.1', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb', '', '1', 'login success', '2026-09-03 18:24:55');
INSERT INTO `sys_logininfor` VALUES (107, 'admin', '127.0.0.1', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWeb', '', '1', 'login success', '2026-09-03 21:29:49');
INSERT INTO `sys_logininfor` VALUES (108, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 13:49:03');
INSERT INTO `sys_logininfor` VALUES (109, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 17:03:24');
INSERT INTO `sys_logininfor` VALUES (110, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 22:26:50');
INSERT INTO `sys_logininfor` VALUES (111, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 22:35:42');
INSERT INTO `sys_logininfor` VALUES (112, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 22:39:44');
INSERT INTO `sys_logininfor` VALUES (113, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 22:43:12');
INSERT INTO `sys_logininfor` VALUES (114, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 22:43:30');
INSERT INTO `sys_logininfor` VALUES (115, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 22:53:01');
INSERT INTO `sys_logininfor` VALUES (116, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-04 23:16:13');
INSERT INTO `sys_logininfor` VALUES (117, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-05 17:24:18');
INSERT INTO `sys_logininfor` VALUES (118, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'logout', '2026-09-05 17:27:43');
INSERT INTO `sys_logininfor` VALUES (119, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-05 17:27:48');
INSERT INTO `sys_logininfor` VALUES (120, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-05 22:53:05');
INSERT INTO `sys_logininfor` VALUES (121, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-06 07:44:13');
INSERT INTO `sys_logininfor` VALUES (122, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-06 10:18:50');
INSERT INTO `sys_logininfor` VALUES (123, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'logout', '2026-09-06 10:20:04');
INSERT INTO `sys_logininfor` VALUES (124, 'yshop', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-06 10:20:13');
INSERT INTO `sys_logininfor` VALUES (125, 'yshop', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'logout', '2026-09-06 10:20:25');
INSERT INTO `sys_logininfor` VALUES (126, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-06 10:20:29');
INSERT INTO `sys_logininfor` VALUES (127, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-06 15:44:39');
INSERT INTO `sys_logininfor` VALUES (128, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'logout', '2026-09-06 15:56:40');
INSERT INTO `sys_logininfor` VALUES (129, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-06 15:56:46');
INSERT INTO `sys_logininfor` VALUES (130, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'logout', '2026-09-06 16:03:21');
INSERT INTO `sys_logininfor` VALUES (131, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-07 09:53:21');
INSERT INTO `sys_logininfor` VALUES (132, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'logout', '2026-09-07 09:57:18');
INSERT INTO `sys_logininfor` VALUES (133, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-07 09:57:22');
INSERT INTO `sys_logininfor` VALUES (134, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-07 16:53:39');
INSERT INTO `sys_logininfor` VALUES (135, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-08 22:38:23');
INSERT INTO `sys_logininfor` VALUES (136, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-09 07:10:45');
INSERT INTO `sys_logininfor` VALUES (137, 'admin', '127.0.0.1', '', 'Chrome', 'Windows 10', '1', 'login success', '2026-09-09 08:05:33');

-- ----------------------------
-- Table structure for sys_menu
-- ----------------------------
DROP TABLE IF EXISTS `sys_menu`;
CREATE TABLE `sys_menu`  (
  `menu_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `menu_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '菜单名称',
  `parent_id` bigint(20) NULL DEFAULT 0 COMMENT '父菜单ID',
  `order_num` int(4) NULL DEFAULT 0 COMMENT '显示顺序',
  `url` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '#' COMMENT '请求地址',
  `target` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '打开方式（menuItem页签 menuBlank新窗口）',
  `menu_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '菜单类型（M目录 C菜单 F按钮）',
  `visible` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '菜单状态（0显示 1隐藏）',
  `is_refresh` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1' COMMENT '是否刷新（0刷新 1不刷新）',
  `perms` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '权限标识',
  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '#' COMMENT '菜单图标',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`menu_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2035 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '菜单权限表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_menu
-- ----------------------------
INSERT INTO `sys_menu` VALUES (1, '系统管理', 0, 1, '#', '', 'M', '0', '1', '', 'fa fa-gear', 'admin', '2026-09-03 16:41:16', '', NULL, '系统管理目录');
INSERT INTO `sys_menu` VALUES (2, '系统监控', 0, 2, '#', '', 'M', '0', '1', '', 'fa fa-video-camera', 'admin', '2026-09-03 16:41:16', '', NULL, '系统监控目录');
INSERT INTO `sys_menu` VALUES (3, '系统工具', 0, 3, '#', '', 'M', '0', '1', '', 'fa fa-bars', 'admin', '2026-09-03 16:41:16', '', NULL, '系统工具目录');
INSERT INTO `sys_menu` VALUES (4, '意象官网', 0, 99, 'https://www.yixiang.co', 'menuBlank', 'C', '0', '1', '', 'fa fa-location-arrow', 'admin', '2026-09-03 16:41:16', 'admin', '2026-09-08 22:43:42', '意象官网地址');
INSERT INTO `sys_menu` VALUES (100, '用户管理', 1, 1, '/system/user', '', 'C', '0', '1', 'system:user:view', 'fa fa-user-o', 'admin', '2026-09-03 16:41:16', '', NULL, '用户管理菜单');
INSERT INTO `sys_menu` VALUES (101, '角色管理', 1, 2, '/system/role', '', 'C', '0', '1', 'system:role:view', 'fa fa-user-secret', 'admin', '2026-09-03 16:41:16', '', NULL, '角色管理菜单');
INSERT INTO `sys_menu` VALUES (102, '菜单管理', 1, 3, '/system/menu', '', 'C', '0', '1', 'system:menu:view', 'fa fa-th-list', 'admin', '2026-09-03 16:41:16', '', NULL, '菜单管理菜单');
INSERT INTO `sys_menu` VALUES (103, '部门管理', 1, 4, '/system/dept', '', 'C', '0', '1', 'system:dept:view', 'fa fa-outdent', 'admin', '2026-09-03 16:41:16', '', NULL, '部门管理菜单');
INSERT INTO `sys_menu` VALUES (104, '岗位管理', 1, 5, '/system/post', '', 'C', '0', '1', 'system:post:view', 'fa fa-address-card-o', 'admin', '2026-09-03 16:41:16', '', NULL, '岗位管理菜单');
INSERT INTO `sys_menu` VALUES (105, '字典管理', 1, 6, '/system/dict', '', 'C', '0', '1', 'system:dict:view', 'fa fa-bookmark-o', 'admin', '2026-09-03 16:41:16', '', NULL, '字典管理菜单');
INSERT INTO `sys_menu` VALUES (106, '参数设置', 1, 7, '/system/config', '', 'C', '0', '1', 'system:config:view', 'fa fa-sun-o', 'admin', '2026-09-03 16:41:16', '', NULL, '参数设置菜单');
INSERT INTO `sys_menu` VALUES (107, '通知公告', 1, 8, '/system/notice', '', 'C', '0', '1', 'system:notice:view', 'fa fa-bullhorn', 'admin', '2026-09-03 16:41:16', '', NULL, '通知公告菜单');
INSERT INTO `sys_menu` VALUES (108, '日志管理', 1, 9, '#', '', 'M', '0', '1', '', 'fa fa-pencil-square-o', 'admin', '2026-09-03 16:41:16', '', NULL, '日志管理菜单');
INSERT INTO `sys_menu` VALUES (109, '在线用户', 2, 1, '/monitor/online', '', 'C', '0', '1', 'monitor:online:view', 'fa fa-user-circle', 'admin', '2026-09-03 16:41:16', '', NULL, '在线用户菜单');
INSERT INTO `sys_menu` VALUES (110, '定时任务', 2, 2, '/monitor/job', '', 'C', '0', '1', 'monitor:job:view', 'fa fa-tasks', 'admin', '2026-09-03 16:41:16', '', NULL, '定时任务菜单');
INSERT INTO `sys_menu` VALUES (111, '数据监控', 2, 3, '/monitor/data', '', 'C', '1', '1', 'monitor:data:view', 'fa fa-bug', 'admin', '2026-09-03 16:41:16', '', NULL, '数据监控菜单');
INSERT INTO `sys_menu` VALUES (112, '服务监控', 2, 4, '/monitor/server', '', 'C', '0', '1', 'monitor:server:view', 'fa fa-server', 'admin', '2026-09-03 16:41:16', '', NULL, '服务监控菜单');
INSERT INTO `sys_menu` VALUES (113, '缓存监控', 2, 5, '/monitor/cache', '', 'C', '0', '1', 'monitor:cache:view', 'fa fa-cube', 'admin', '2026-09-03 16:41:16', '', NULL, '缓存监控菜单');
INSERT INTO `sys_menu` VALUES (114, '表单构建', 3, 1, '/tool/build', '', 'C', '0', '1', 'tool:build:view', 'fa fa-wpforms', 'admin', '2026-09-03 16:41:16', '', NULL, '表单构建菜单');
INSERT INTO `sys_menu` VALUES (115, '代码生成', 3, 2, '/tool/gen', '', 'C', '0', '1', 'tool:gen:view', 'fa fa-code', 'admin', '2026-09-03 16:41:16', '', NULL, '代码生成菜单');
INSERT INTO `sys_menu` VALUES (116, '系统接口', 3, 3, '/tool/swagger', '', 'C', '1', '1', 'tool:swagger:view', 'fa fa-gg', 'admin', '2026-09-03 16:41:16', '', NULL, '系统接口菜单');
INSERT INTO `sys_menu` VALUES (500, '操作日志', 108, 1, '/monitor/operlog', '', 'C', '0', '1', 'monitor:operlog:view', 'fa fa-address-book', 'admin', '2026-09-03 16:41:16', '', NULL, '操作日志菜单');
INSERT INTO `sys_menu` VALUES (501, '登录日志', 108, 2, '/monitor/logininfor', '', 'C', '0', '1', 'monitor:logininfor:view', 'fa fa-file-image-o', 'admin', '2026-09-03 16:41:16', '', NULL, '登录日志菜单');
INSERT INTO `sys_menu` VALUES (1000, '用户查询', 100, 1, '#', '', 'F', '0', '1', 'system:user:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1001, '用户新增', 100, 2, '#', '', 'F', '0', '1', 'system:user:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1002, '用户修改', 100, 3, '#', '', 'F', '0', '1', 'system:user:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1003, '用户删除', 100, 4, '#', '', 'F', '0', '1', 'system:user:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1004, '用户导出', 100, 5, '#', '', 'F', '0', '1', 'system:user:export', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1005, '用户导入', 100, 6, '#', '', 'F', '0', '1', 'system:user:import', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1006, '重置密码', 100, 7, '#', '', 'F', '0', '1', 'system:user:resetPwd', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1007, '角色查询', 101, 1, '#', '', 'F', '0', '1', 'system:role:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1008, '角色新增', 101, 2, '#', '', 'F', '0', '1', 'system:role:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1009, '角色修改', 101, 3, '#', '', 'F', '0', '1', 'system:role:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1010, '角色删除', 101, 4, '#', '', 'F', '0', '1', 'system:role:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1011, '角色导出', 101, 5, '#', '', 'F', '0', '1', 'system:role:export', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1012, '菜单查询', 102, 1, '#', '', 'F', '0', '1', 'system:menu:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1013, '菜单新增', 102, 2, '#', '', 'F', '0', '1', 'system:menu:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1014, '菜单修改', 102, 3, '#', '', 'F', '0', '1', 'system:menu:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1015, '菜单删除', 102, 4, '#', '', 'F', '0', '1', 'system:menu:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1016, '部门查询', 103, 1, '#', '', 'F', '0', '1', 'system:dept:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1017, '部门新增', 103, 2, '#', '', 'F', '0', '1', 'system:dept:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1018, '部门修改', 103, 3, '#', '', 'F', '0', '1', 'system:dept:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1019, '部门删除', 103, 4, '#', '', 'F', '0', '1', 'system:dept:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1020, '岗位查询', 104, 1, '#', '', 'F', '0', '1', 'system:post:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1021, '岗位新增', 104, 2, '#', '', 'F', '0', '1', 'system:post:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1022, '岗位修改', 104, 3, '#', '', 'F', '0', '1', 'system:post:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1023, '岗位删除', 104, 4, '#', '', 'F', '0', '1', 'system:post:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1024, '岗位导出', 104, 5, '#', '', 'F', '0', '1', 'system:post:export', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1025, '字典查询', 105, 1, '#', '', 'F', '0', '1', 'system:dict:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1026, '字典新增', 105, 2, '#', '', 'F', '0', '1', 'system:dict:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1027, '字典修改', 105, 3, '#', '', 'F', '0', '1', 'system:dict:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1028, '字典删除', 105, 4, '#', '', 'F', '0', '1', 'system:dict:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1029, '字典导出', 105, 5, '#', '', 'F', '0', '1', 'system:dict:export', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1030, '参数查询', 106, 1, '#', '', 'F', '0', '1', 'system:config:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1031, '参数新增', 106, 2, '#', '', 'F', '0', '1', 'system:config:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1032, '参数修改', 106, 3, '#', '', 'F', '0', '1', 'system:config:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1033, '参数删除', 106, 4, '#', '', 'F', '0', '1', 'system:config:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1034, '参数导出', 106, 5, '#', '', 'F', '0', '1', 'system:config:export', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1035, '公告查询', 107, 1, '#', '', 'F', '0', '1', 'system:notice:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1036, '公告新增', 107, 2, '#', '', 'F', '0', '1', 'system:notice:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1037, '公告修改', 107, 3, '#', '', 'F', '0', '1', 'system:notice:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1038, '公告删除', 107, 4, '#', '', 'F', '0', '1', 'system:notice:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1039, '操作查询', 500, 1, '#', '', 'F', '0', '1', 'monitor:operlog:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1040, '操作删除', 500, 2, '#', '', 'F', '0', '1', 'monitor:operlog:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1041, '详细信息', 500, 3, '#', '', 'F', '0', '1', 'monitor:operlog:detail', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1042, '日志导出', 500, 4, '#', '', 'F', '0', '1', 'monitor:operlog:export', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1043, '登录查询', 501, 1, '#', '', 'F', '0', '1', 'monitor:logininfor:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1044, '登录删除', 501, 2, '#', '', 'F', '0', '1', 'monitor:logininfor:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1045, '日志导出', 501, 3, '#', '', 'F', '0', '1', 'monitor:logininfor:export', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1046, '账户解锁', 501, 4, '#', '', 'F', '0', '1', 'monitor:logininfor:unlock', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1047, '在线查询', 109, 1, '#', '', 'F', '0', '1', 'monitor:online:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1048, '批量强退', 109, 2, '#', '', 'F', '0', '1', 'monitor:online:batchForceLogout', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1049, '单条强退', 109, 3, '#', '', 'F', '0', '1', 'monitor:online:forceLogout', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1050, '任务查询', 110, 1, '#', '', 'F', '0', '1', 'monitor:job:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1051, '任务新增', 110, 2, '#', '', 'F', '0', '1', 'monitor:job:add', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1052, '任务修改', 110, 3, '#', '', 'F', '0', '1', 'monitor:job:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1053, '任务删除', 110, 4, '#', '', 'F', '0', '1', 'monitor:job:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1054, '状态修改', 110, 5, '#', '', 'F', '0', '1', 'monitor:job:changeStatus', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1055, '任务详细', 110, 6, '#', '', 'F', '0', '1', 'monitor:job:detail', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1056, '任务导出', 110, 7, '#', '', 'F', '0', '1', 'monitor:job:export', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1057, '生成查询', 115, 1, '#', '', 'F', '0', '1', 'tool:gen:list', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1058, '生成修改', 115, 2, '#', '', 'F', '0', '1', 'tool:gen:edit', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1059, '生成删除', 115, 3, '#', '', 'F', '0', '1', 'tool:gen:remove', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1060, '预览代码', 115, 4, '#', '', 'F', '0', '1', 'tool:gen:preview', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (1061, '生成代码', 115, 5, '#', '', 'F', '0', '1', 'tool:gen:code', '#', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_menu` VALUES (2000, '插件管理', 0, 10, '/system/addon', 'menuItem', 'M', '0', '1', 'system:addon:view', 'fa fa-puzzle-piece', 'admin', '2026-09-08 22:38:06', 'admin', '2026-09-08 22:48:50', '插件管理菜单');
INSERT INTO `sys_menu` VALUES (2001, '插件查询', 2000, 1, '#', '', 'F', '0', '1', 'system:addon:list', '#', 'admin', '2026-09-08 22:38:06', '', NULL, '');
INSERT INTO `sys_menu` VALUES (2002, '插件安装', 2000, 2, '#', '', 'F', '0', '1', 'system:addon:install', '#', 'admin', '2026-09-08 22:38:06', '', NULL, '');
INSERT INTO `sys_menu` VALUES (2003, '插件卸载', 2000, 3, '#', '', 'F', '0', '1', 'system:addon:uninstall', '#', 'admin', '2026-09-08 22:38:06', '', NULL, '');
INSERT INTO `sys_menu` VALUES (2004, '插件启停', 2000, 4, '#', '', 'F', '0', '1', 'system:addon:edit', '#', 'admin', '2026-09-08 22:38:06', '', NULL, '');
INSERT INTO `sys_menu` VALUES (2005, '插件配置', 2000, 5, '#', '', 'F', '0', '1', 'system:addon:config', '#', 'admin', '2026-09-08 22:38:06', '', NULL, '');

-- ----------------------------
-- Table structure for sys_notice
-- ----------------------------
DROP TABLE IF EXISTS `sys_notice`;
CREATE TABLE `sys_notice`  (
  `notice_id` int(4) NOT NULL AUTO_INCREMENT COMMENT '公告ID',
  `notice_title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '公告标题',
  `notice_type` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '公告类型（1通知 2公告）',
  `notice_content` longblob NULL COMMENT '公告内容',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '公告状态（0正常 1关闭）',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`notice_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '通知公告表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_notice
-- ----------------------------
INSERT INTO `sys_notice` VALUES (1, '温馨提醒：2018-07-01 意象新版本发布啦', '2', 0xE696B0E78988E69CACE58685E5AEB9, '0', 'admin', '2026-09-03 16:41:17', '', NULL, '管理员');
INSERT INTO `sys_notice` VALUES (2, '维护通知：2018-07-01 意象系统凌晨维护', '1', 0xE7BBB4E68AA4E58685E5AEB9, '0', 'admin', '2026-09-03 16:41:17', '', NULL, '管理员');
INSERT INTO `sys_notice` VALUES (3, '意象开源框架介绍', '1', 0x3C6832207374796C653D22666F6E742D66616D696C793A202671756F743B6F70656E2073616E732671756F743B2C202671756F743B48656C766574696361204E6575652671756F743B2C2048656C7665746963612C20417269616C2C2073616E732D73657269663B20636F6C6F723A20726762283130332C203130362C20313038293B206D617267696E2D746F703A20313070783B20666F6E742D73697A653A20323670783B223E5973686F7041646D696E202F20E6848FE8B1A1E5908EE58FB0E7AEA1E79086E6A186E69EB63C2F68323E3C703E5468696E6B504850382B70687038E78988E69CACE38082E58FAFE794A8E4BA8EE7BD91E7AB99E7AEA1E79086E5908EE58FB0E38081434D53E3808143524DE380814F4120E7AD89E59CBAE699AFE380823C2F703E, '0', 'admin', '2026-09-03 16:41:17', 'admin', '2026-09-06 10:32:40', '');
INSERT INTO `sys_notice` VALUES (10, '666', '1', 0x36363636, '0', 'admin', '2026-09-06 08:15:08', '', NULL, '');

-- ----------------------------
-- Table structure for sys_notice_read
-- ----------------------------
DROP TABLE IF EXISTS `sys_notice_read`;
CREATE TABLE `sys_notice_read`  (
  `read_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '已读主键',
  `notice_id` int(4) NOT NULL COMMENT '公告id',
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `read_time` datetime NOT NULL COMMENT '阅读时间',
  PRIMARY KEY (`read_id`) USING BTREE,
  UNIQUE INDEX `uk_user_notice`(`user_id`, `notice_id`) USING BTREE COMMENT '同一用户同一公告只记录一次'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '公告已读记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_notice_read
-- ----------------------------

-- ----------------------------
-- Table structure for sys_oper_log
-- ----------------------------
DROP TABLE IF EXISTS `sys_oper_log`;
CREATE TABLE `sys_oper_log`  (
  `oper_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '日志主键',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '模块标题',
  `business_type` int(2) NULL DEFAULT 0 COMMENT '业务类型（0其它 1新增 2修改 3删除）',
  `method` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '方法名称',
  `request_method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '请求方式',
  `operator_type` int(1) NULL DEFAULT 0 COMMENT '操作类别（0其它 1后台用户 2手机端用户）',
  `oper_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '操作人员',
  `dept_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '部门名称',
  `oper_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '请求URL',
  `oper_ip` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '主机地址',
  `oper_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '操作地点',
  `oper_param` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '请求参数',
  `json_result` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '返回参数',
  `status` int(1) NULL DEFAULT 0 COMMENT '操作状态（0正常 1异常）',
  `error_msg` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '错误消息',
  `oper_time` datetime NULL DEFAULT NULL COMMENT '操作时间',
  `cost_time` bigint(20) NULL DEFAULT 0 COMMENT '消耗时间',
  PRIMARY KEY (`oper_id`) USING BTREE,
  INDEX `idx_sys_oper_log_bt`(`business_type`) USING BTREE,
  INDEX `idx_sys_oper_log_s`(`status`) USING BTREE,
  INDEX `idx_sys_oper_log_ot`(`oper_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 148 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '操作日志记录' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_oper_log
-- ----------------------------
INSERT INTO `sys_oper_log` VALUES (100, '角色管理', 0, 'system.Role/add', 'POST', 1, 'admin', '', '/system/role/add', '127.0.0.1', '', '{\"menuIds\":\"1,100,1000,1001,1002,1003,1004,1005,1006\",\"roleName\":\"新角色\",\"roleKey\":\"newrole\",\"roleSort\":\"0\",\"status\":\"0\",\"remark\":\"\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-04 23:18:17', 145);
INSERT INTO `sys_oper_log` VALUES (101, '导入表', 0, 'tool.Gen/importTable', 'POST', 1, 'admin', '', '/tool/gen/importTable', '127.0.0.1', '', '{\"tables\":\"sys_role\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-05 23:30:42', 116);
INSERT INTO `sys_oper_log` VALUES (102, '用户管理', 0, 'system.User/add', 'POST', 1, 'admin', '', '/system/user/add', '127.0.0.1', '', '{\"deptId\":\"103\",\"userName\":\"yshop\",\"deptName\":\"研发部门\",\"phonenumber\":\"15888888888\",\"email\":\"yshop@qq.com\",\"loginName\":\"yshop\",\"password\":\"123456\",\"sex\":\"0\",\"role\":\"100\",\"remark\":\"\",\"status\":\"0\",\"roleIds\":\"100\",\"postIds\":\"\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-06 07:46:45', 80);
INSERT INTO `sys_oper_log` VALUES (103, '部门管理', 0, 'system.Dept/add', 'POST', 1, 'admin', '', '/system/dept/add', '127.0.0.1', '', '{\"parentId\":\"103\",\"deptName\":\"全工程师\",\"orderNum\":\"0\",\"leader\":\"yshop先生\",\"phone\":\"\",\"email\":\"\",\"status\":\"0\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-06 08:13:44', 61);
INSERT INTO `sys_oper_log` VALUES (104, '岗位管理', 0, 'system.Post/add', 'POST', 1, 'admin', '', '/system/post/add', '127.0.0.1', '', '{\"postName\":\"JavA\",\"postCode\":\"JAVA 100\",\"postSort\":\"11\",\"status\":\"0\",\"remark\":\"\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-06 08:14:35', 51);
INSERT INTO `sys_oper_log` VALUES (105, '通知公告', 0, 'system.Notice/add', 'POST', 1, 'admin', '', '/system/notice/add', '127.0.0.1', '', '{\"noticeTitle\":\"666\",\"noticeType\":\"1\",\"status\":\"0\",\"noticeContent\":\"6666\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-06 08:15:08', 55);
INSERT INTO `sys_oper_log` VALUES (106, '通知公告', 0, 'system.Notice/edit', 'POST', 1, 'admin', '', '/system/notice/edit', '127.0.0.1', '', '{\"noticeId\":\"3\",\"noticeTitle\":\"意象开源框架介绍\",\"noticeType\":\"1\",\"status\":\"0\",\"noticeContent\":\"<h2 style=\\\"font-family: &quot;open sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(103, 106, 108); margin-top: 10px; font-size: 26px;\\\">YshopAdmin \\/ 意象后台管理框架<\\/h2><p>ThinkPHP8+php8版本。可用于网站管理后台、CMS、CRM、OA 等场景。<\\/p>\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-06 10:32:40', 78);
INSERT INTO `sys_oper_log` VALUES (107, '参数设置', 0, 'system.Config/add', 'POST', 1, 'admin', '', '/system/config/add', '127.0.0.1', '', '{\"configName\":\"演示模式开启\",\"configKey\":\"sys.demo.enabled\",\"configValue\":\"true\",\"configType\":\"Y\",\"remark\":\"\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-06 15:46:34', 97);
INSERT INTO `sys_oper_log` VALUES (108, '通知公告', 0, 'system.Notice/edit', 'POST', 1, 'admin', '', '/system/notice/edit', '127.0.0.1', '', '{\"noticeId\":\"10\",\"noticeTitle\":\"6667\",\"noticeType\":\"1\",\"status\":\"0\",\"noticeContent\":\"6666\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-06 15:56:05', 79);
INSERT INTO `sys_oper_log` VALUES (109, '通知公告', 0, 'system.Notice/remove', 'POST', 1, 'admin', '', '/system/notice/remove', '127.0.0.1', '', '{\"ids\":\"1\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-06 15:56:16', 57);
INSERT INTO `sys_oper_log` VALUES (110, '任务状态', 0, 'monitor.Job/changeStatus', 'POST', 1, 'admin', '', '/monitor/job/changeStatus', '127.0.0.1', '', '{\"jobId\":\"1\",\"status\":\"0\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-07 09:54:59', 69);
INSERT INTO `sys_oper_log` VALUES (111, '参数设置', 0, 'system.Config/edit', 'POST', 1, 'admin', '', '/system/config/edit', '127.0.0.1', '', '{\"configId\":\"100\",\"configName\":\"演示模式开启\",\"configKey\":\"sys.demo.enabled\",\"configValue\":\"false\",\"configType\":\"Y\",\"remark\":\"\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-07 09:55:24', 70);
INSERT INTO `sys_oper_log` VALUES (112, '任务状态', 0, 'monitor.Job/changeStatus', 'POST', 1, 'admin', '', '/monitor/job/changeStatus', '127.0.0.1', '', '{\"jobId\":\"1\",\"status\":\"0\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-07 09:56:42', 61);
INSERT INTO `sys_oper_log` VALUES (113, '任务状态', 0, 'monitor.Job/changeStatus', 'POST', 1, 'admin', '', '/monitor/job/changeStatus', '127.0.0.1', '', '{\"jobId\":\"1\",\"status\":\"0\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-07 09:56:54', 54);
INSERT INTO `sys_oper_log` VALUES (114, '执行任务', 0, 'monitor.Job/run', 'POST', 1, 'admin', '', '/monitor/job/run', '127.0.0.1', '', '{\"jobId\":\"1\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-07 09:57:14', 50);
INSERT INTO `sys_oper_log` VALUES (115, '任务状态', 0, 'monitor.Job/changeStatus', 'POST', 1, 'admin', '', '/monitor/job/changeStatus', '127.0.0.1', '', '{\"jobId\":\"1\",\"status\":\"0\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-07 09:57:28', 45);
INSERT INTO `sys_oper_log` VALUES (116, '任务状态', 0, 'monitor.Job/changeStatus', 'POST', 1, 'admin', '', '/monitor/job/changeStatus', '127.0.0.1', '', '{\"jobId\":\"1\",\"status\":\"0\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-07 09:58:02', 53);
INSERT INTO `sys_oper_log` VALUES (117, '任务状态', 0, 'monitor.Job/changeStatus', 'POST', 1, 'admin', '', '/monitor/job/changeStatus', '127.0.0.1', '', '{\"jobId\":\"1\",\"status\":\"0\"}', '{\"code\":500,\"msg\":\"演示环境模式禁止操作\"}', 1, '演示环境模式禁止操作', '2026-09-07 10:00:06', 65);
INSERT INTO `sys_oper_log` VALUES (118, '任务状态', 0, 'monitor.Job/changeStatus', 'POST', 1, 'admin', '', '/monitor/job/changeStatus', '127.0.0.1', '', '{\"jobId\":\"1\",\"status\":\"0\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-07 10:07:37', 72);
INSERT INTO `sys_oper_log` VALUES (119, '执行任务', 0, 'monitor.Job/run', 'POST', 1, 'admin', '', '/monitor/job/run', '127.0.0.1', '', '{\"jobId\":\"1\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-07 10:09:01', 89);
INSERT INTO `sys_oper_log` VALUES (120, '执行任务', 0, 'monitor.Job/run', 'POST', 1, 'admin', '', '/monitor/job/run', '127.0.0.1', '', '{\"jobId\":\"1\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-07 10:10:04', 68);
INSERT INTO `sys_oper_log` VALUES (121, '执行任务', 0, 'monitor.Job/run', 'POST', 1, 'admin', '', '/monitor/job/run', '127.0.0.1', '', '{\"jobId\":\"1\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-07 10:14:04', 100);
INSERT INTO `sys_oper_log` VALUES (122, '任务状态', 0, 'monitor.Job/changeStatus', 'POST', 1, 'admin', '', '/monitor/job/changeStatus', '127.0.0.1', '', '{\"jobId\":\"1\",\"status\":\"1\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-07 10:15:02', 57);
INSERT INTO `sys_oper_log` VALUES (123, '菜单管理', 0, 'system.Menu/edit', 'POST', 1, 'admin', '', '/system/menu/edit', '127.0.0.1', '', '{\"menuId\":\"4\",\"parentId\":\"0\",\"menuType\":\"C\",\"menuName\":\"意象官网\",\"orderNum\":\"99\",\"url\":\"https:\\/\\/www.yixiang.co\",\"target\":\"menuBlank\",\"perms\":\"\",\"icon\":\"fa fa-location-arrow\",\"visible\":\"0\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 22:43:42', 53);
INSERT INTO `sys_oper_log` VALUES (124, '菜单管理', 0, 'system.Menu/edit', 'POST', 1, 'admin', '', '/system/menu/edit', '127.0.0.1', '', '{\"menuId\":\"2000\",\"parentId\":\"0\",\"menuType\":\"M\",\"menuName\":\"插件管理\",\"orderNum\":\"10\",\"url\":\"\\/system\\/addon\",\"target\":\"menuItem\",\"perms\":\"system:addon:view\",\"icon\":\"fa fa-puzzle-piece\",\"visible\":\"0\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 22:48:51', 57);
INSERT INTO `sys_oper_log` VALUES (125, '插件安装', 0, 'system.Addon/install', 'POST', 1, 'admin', '', '/system/addon/install', '127.0.0.1', '', '{\"name\":\"addondev\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 22:51:05', 114);
INSERT INTO `sys_oper_log` VALUES (126, '插件启用', 0, 'system.Addon/enable', 'POST', 1, 'admin', '', '/system/addon/enable', '127.0.0.1', '', '{\"name\":\"addondev\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 23:04:35', 25);
INSERT INTO `sys_oper_log` VALUES (127, '插件禁用', 0, 'system.Addon/disable', 'POST', 1, 'admin', '', '/system/addon/disable', '127.0.0.1', '', '{\"name\":\"addondev\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 23:37:26', 33);
INSERT INTO `sys_oper_log` VALUES (128, '插件卸载', 0, 'system.Addon/uninstall', 'POST', 1, 'admin', '', '/system/addon/uninstall', '127.0.0.1', '', '{\"name\":\"addondev\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 23:37:29', 41);
INSERT INTO `sys_oper_log` VALUES (129, '插件安装', 0, 'system.Addon/install', 'POST', 1, 'admin', '', '/system/addon/install', '127.0.0.1', '', '{\"name\":\"addondev\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 23:41:48', 70);
INSERT INTO `sys_oper_log` VALUES (130, '插件安装', 0, 'system.Addon/install', 'POST', 1, 'admin', '', '/system/addon/install', '127.0.0.1', '', '{\"name\":\"yspay\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 23:42:16', 52);
INSERT INTO `sys_oper_log` VALUES (131, '插件打包', 0, 'Addons/package', 'POST', 1, 'admin', '', '/addondev/addons/package/yspay', '127.0.0.1', '', '{\"version\":\"1.0.1\"}', '{\"code\":0,\"msg\":\"打包成功\",\"data\":{\"path\":\"D:\\\\web\\\\www\\\\yshopplugin\\\\YshopAdmin\\\\runtime\\\\addons\\\\yspay-1.0.1.zip\"}}', 0, '', '2026-09-08 23:44:12', 62);
INSERT INTO `sys_oper_log` VALUES (132, '插件打包', 0, 'Addons/package', 'POST', 1, 'admin', '', '/addondev/addons/package/yspay', '127.0.0.1', '', '{\"version\":\"1.0.1\"}', '{\"code\":0,\"msg\":\"打包成功\",\"data\":{\"path\":\"D:\\\\web\\\\www\\\\yshopplugin\\\\YshopAdmin\\\\runtime\\\\addons\\\\yspay-1.0.1.zip\"}}', 0, '', '2026-09-08 23:45:20', 59);
INSERT INTO `sys_oper_log` VALUES (133, '插件禁用', 0, 'system.Addon/disable', 'POST', 1, 'admin', '', '/system/addon/disable', '127.0.0.1', '', '{\"name\":\"yspay\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 23:48:20', 35);
INSERT INTO `sys_oper_log` VALUES (134, '插件卸载', 0, 'system.Addon/uninstall', 'POST', 1, 'admin', '', '/system/addon/uninstall', '127.0.0.1', '', '{\"name\":\"yspay\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 23:48:27', 33);
INSERT INTO `sys_oper_log` VALUES (135, '插件安装', 0, 'system.Addon/install', 'POST', 1, 'admin', '', '/system/addon/install', '127.0.0.1', '', '{\"name\":\"yspay\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-08 23:48:54', 51);
INSERT INTO `sys_oper_log` VALUES (136, '插件打包', 0, 'Addons/package', 'POST', 1, 'admin', '', '/addondev/addons/package/yspay', '127.0.0.1', '', '{\"version\":\"1.0.2\"}', '{\"code\":0,\"msg\":\"打包成功\",\"data\":{\"path\":\"D:\\\\web\\\\www\\\\yshopplugin\\\\YshopAdmin\\\\runtime\\\\addons\\\\yspay-1.0.2.zip\"}}', 0, '', '2026-09-08 23:49:26', 53);
INSERT INTO `sys_oper_log` VALUES (137, '本地安装插件', 0, 'system.Addon/upload', 'POST', 1, 'admin', '', '/system/addon/upload', '127.0.0.1', '', '[]', '{\"code\":500,\"msg\":\"???????????: yspay\"}', 1, '???????????: yspay', '2026-09-09 07:11:03', 316);
INSERT INTO `sys_oper_log` VALUES (138, '本地安装插件', 0, 'system.Addon/upload', 'POST', 1, 'admin', '', '/system/addon/upload', '127.0.0.1', '', '[]', '{\"code\":500,\"msg\":\"插件目录已存在: yspay\"}', 1, '插件目录已存在: yspay', '2026-09-09 08:05:48', 702);
INSERT INTO `sys_oper_log` VALUES (139, '本地安装插件', 0, 'system.Addon/upload', 'POST', 1, 'admin', '', '/system/addon/upload', '127.0.0.1', '', '[]', '{\"code\":500,\"msg\":\"插件目录已存在: yspay\"}', 1, '插件目录已存在: yspay', '2026-09-09 08:22:57', 257);
INSERT INTO `sys_oper_log` VALUES (140, '本地安装插件', 0, 'system.Addon/upload', 'POST', 1, 'admin', '', '/system/addon/upload', '127.0.0.1', '', '[]', '{\"code\":500,\"msg\":\"插件目录已存在: yspay\"}', 1, '插件目录已存在: yspay', '2026-09-09 08:23:54', 783);
INSERT INTO `sys_oper_log` VALUES (141, '插件禁用', 0, 'system.Addon/disable', 'POST', 1, 'admin', '', '/system/addon/disable', '127.0.0.1', '', '{\"name\":\"yspay\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-09 08:25:27', 23);
INSERT INTO `sys_oper_log` VALUES (142, '插件卸载', 0, 'system.Addon/uninstall', 'POST', 1, 'admin', '', '/system/addon/uninstall', '127.0.0.1', '', '{\"name\":\"yspay\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-09 08:25:32', 48);
INSERT INTO `sys_oper_log` VALUES (143, '本地安装插件', 0, 'system.Addon/upload', 'POST', 1, 'admin', '', '/system/addon/upload', '127.0.0.1', '', '[]', '{\"code\":500,\"msg\":\"插件目录已存在: yspay\"}', 1, '插件目录已存在: yspay', '2026-09-09 08:26:05', 166);
INSERT INTO `sys_oper_log` VALUES (144, '本地安装插件', 0, 'system.Addon/upload', 'POST', 1, 'admin', '', '/system/addon/upload', '127.0.0.1', '', '[]', '{\"code\":500,\"msg\":\"插件目录已存在: yspay\"}', 1, '插件目录已存在: yspay', '2026-09-09 08:55:55', 189);
INSERT INTO `sys_oper_log` VALUES (145, '本地安装插件', 0, 'system.Addon/upload', 'POST', 1, 'admin', '', '/system/addon/upload', '127.0.0.1', '', '[]', '{\"code\":0,\"msg\":\"安装成功: yspay\"}', 0, '', '2026-09-09 09:19:04', 258);
INSERT INTO `sys_oper_log` VALUES (146, '插件禁用', 0, 'system.Addon/disable', 'POST', 1, 'admin', '', '/system/addon/disable', '127.0.0.1', '', '{\"name\":\"yspay\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-09 09:48:38', 30);
INSERT INTO `sys_oper_log` VALUES (147, '插件卸载', 0, 'system.Addon/uninstall', 'POST', 1, 'admin', '', '/system/addon/uninstall', '127.0.0.1', '', '{\"name\":\"yspay\"}', '{\"code\":0,\"msg\":\"操作成功\"}', 0, '', '2026-09-09 09:48:41', 45);

-- ----------------------------
-- Table structure for sys_post
-- ----------------------------
DROP TABLE IF EXISTS `sys_post`;
CREATE TABLE `sys_post`  (
  `post_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '岗位ID',
  `post_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '岗位编码',
  `post_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '岗位名称',
  `post_sort` int(4) NOT NULL COMMENT '显示顺序',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '状态（0正常 1停用）',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`post_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '岗位信息表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_post
-- ----------------------------
INSERT INTO `sys_post` VALUES (1, 'ceo', '董事长', 1, '0', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_post` VALUES (2, 'se', '项目经理', 2, '0', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_post` VALUES (3, 'hr', '人力资源', 3, '0', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_post` VALUES (4, 'user', '普通员工', 4, '0', 'admin', '2026-09-03 16:41:16', '', NULL, '');
INSERT INTO `sys_post` VALUES (5, 'JAVA 100', 'JavA', 11, '0', 'admin', '2026-09-06 08:14:35', '', NULL, '');

-- ----------------------------
-- Table structure for sys_role
-- ----------------------------
DROP TABLE IF EXISTS `sys_role`;
CREATE TABLE `sys_role`  (
  `role_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `role_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
  `role_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色权限字符串',
  `role_sort` int(4) NOT NULL COMMENT '显示顺序',
  `data_scope` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1' COMMENT '数据范围（1：全部数据权限 2：自定数据权限 3：本部门数据权限 4：本部门及以下数据权限）',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色状态（0正常 1停用）',
  `del_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '删除标志（0代表存在 2代表删除）',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 101 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色信息表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_role
-- ----------------------------
INSERT INTO `sys_role` VALUES (1, '超级管理员', 'admin', 1, '1', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '超级管理员');
INSERT INTO `sys_role` VALUES (2, '普通角色', 'common', 2, '2', '0', '0', 'admin', '2026-09-03 16:41:16', '', NULL, '普通角色');
INSERT INTO `sys_role` VALUES (100, '新角色', 'newrole', 0, '1', '0', '0', 'admin', '2026-09-04 23:18:17', '', NULL, '');

-- ----------------------------
-- Table structure for sys_role_dept
-- ----------------------------
DROP TABLE IF EXISTS `sys_role_dept`;
CREATE TABLE `sys_role_dept`  (
  `role_id` bigint(20) NOT NULL COMMENT '角色ID',
  `dept_id` bigint(20) NOT NULL COMMENT '部门ID',
  PRIMARY KEY (`role_id`, `dept_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色和部门关联表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_role_dept
-- ----------------------------
INSERT INTO `sys_role_dept` VALUES (2, 100);
INSERT INTO `sys_role_dept` VALUES (2, 101);
INSERT INTO `sys_role_dept` VALUES (2, 105);

-- ----------------------------
-- Table structure for sys_role_menu
-- ----------------------------
DROP TABLE IF EXISTS `sys_role_menu`;
CREATE TABLE `sys_role_menu`  (
  `role_id` bigint(20) NOT NULL COMMENT '角色ID',
  `menu_id` bigint(20) NOT NULL COMMENT '菜单ID',
  PRIMARY KEY (`role_id`, `menu_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色和菜单关联表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_role_menu
-- ----------------------------
INSERT INTO `sys_role_menu` VALUES (2, 1);
INSERT INTO `sys_role_menu` VALUES (2, 2);
INSERT INTO `sys_role_menu` VALUES (2, 3);
INSERT INTO `sys_role_menu` VALUES (2, 4);
INSERT INTO `sys_role_menu` VALUES (2, 100);
INSERT INTO `sys_role_menu` VALUES (2, 101);
INSERT INTO `sys_role_menu` VALUES (2, 102);
INSERT INTO `sys_role_menu` VALUES (2, 103);
INSERT INTO `sys_role_menu` VALUES (2, 104);
INSERT INTO `sys_role_menu` VALUES (2, 105);
INSERT INTO `sys_role_menu` VALUES (2, 106);
INSERT INTO `sys_role_menu` VALUES (2, 107);
INSERT INTO `sys_role_menu` VALUES (2, 108);
INSERT INTO `sys_role_menu` VALUES (2, 109);
INSERT INTO `sys_role_menu` VALUES (2, 110);
INSERT INTO `sys_role_menu` VALUES (2, 111);
INSERT INTO `sys_role_menu` VALUES (2, 112);
INSERT INTO `sys_role_menu` VALUES (2, 113);
INSERT INTO `sys_role_menu` VALUES (2, 114);
INSERT INTO `sys_role_menu` VALUES (2, 115);
INSERT INTO `sys_role_menu` VALUES (2, 116);
INSERT INTO `sys_role_menu` VALUES (2, 500);
INSERT INTO `sys_role_menu` VALUES (2, 501);
INSERT INTO `sys_role_menu` VALUES (2, 1000);
INSERT INTO `sys_role_menu` VALUES (2, 1001);
INSERT INTO `sys_role_menu` VALUES (2, 1002);
INSERT INTO `sys_role_menu` VALUES (2, 1003);
INSERT INTO `sys_role_menu` VALUES (2, 1004);
INSERT INTO `sys_role_menu` VALUES (2, 1005);
INSERT INTO `sys_role_menu` VALUES (2, 1006);
INSERT INTO `sys_role_menu` VALUES (2, 1007);
INSERT INTO `sys_role_menu` VALUES (2, 1008);
INSERT INTO `sys_role_menu` VALUES (2, 1009);
INSERT INTO `sys_role_menu` VALUES (2, 1010);
INSERT INTO `sys_role_menu` VALUES (2, 1011);
INSERT INTO `sys_role_menu` VALUES (2, 1012);
INSERT INTO `sys_role_menu` VALUES (2, 1013);
INSERT INTO `sys_role_menu` VALUES (2, 1014);
INSERT INTO `sys_role_menu` VALUES (2, 1015);
INSERT INTO `sys_role_menu` VALUES (2, 1016);
INSERT INTO `sys_role_menu` VALUES (2, 1017);
INSERT INTO `sys_role_menu` VALUES (2, 1018);
INSERT INTO `sys_role_menu` VALUES (2, 1019);
INSERT INTO `sys_role_menu` VALUES (2, 1020);
INSERT INTO `sys_role_menu` VALUES (2, 1021);
INSERT INTO `sys_role_menu` VALUES (2, 1022);
INSERT INTO `sys_role_menu` VALUES (2, 1023);
INSERT INTO `sys_role_menu` VALUES (2, 1024);
INSERT INTO `sys_role_menu` VALUES (2, 1025);
INSERT INTO `sys_role_menu` VALUES (2, 1026);
INSERT INTO `sys_role_menu` VALUES (2, 1027);
INSERT INTO `sys_role_menu` VALUES (2, 1028);
INSERT INTO `sys_role_menu` VALUES (2, 1029);
INSERT INTO `sys_role_menu` VALUES (2, 1030);
INSERT INTO `sys_role_menu` VALUES (2, 1031);
INSERT INTO `sys_role_menu` VALUES (2, 1032);
INSERT INTO `sys_role_menu` VALUES (2, 1033);
INSERT INTO `sys_role_menu` VALUES (2, 1034);
INSERT INTO `sys_role_menu` VALUES (2, 1035);
INSERT INTO `sys_role_menu` VALUES (2, 1036);
INSERT INTO `sys_role_menu` VALUES (2, 1037);
INSERT INTO `sys_role_menu` VALUES (2, 1038);
INSERT INTO `sys_role_menu` VALUES (2, 1039);
INSERT INTO `sys_role_menu` VALUES (2, 1040);
INSERT INTO `sys_role_menu` VALUES (2, 1041);
INSERT INTO `sys_role_menu` VALUES (2, 1042);
INSERT INTO `sys_role_menu` VALUES (2, 1043);
INSERT INTO `sys_role_menu` VALUES (2, 1044);
INSERT INTO `sys_role_menu` VALUES (2, 1045);
INSERT INTO `sys_role_menu` VALUES (2, 1046);
INSERT INTO `sys_role_menu` VALUES (2, 1047);
INSERT INTO `sys_role_menu` VALUES (2, 1048);
INSERT INTO `sys_role_menu` VALUES (2, 1049);
INSERT INTO `sys_role_menu` VALUES (2, 1050);
INSERT INTO `sys_role_menu` VALUES (2, 1051);
INSERT INTO `sys_role_menu` VALUES (2, 1052);
INSERT INTO `sys_role_menu` VALUES (2, 1053);
INSERT INTO `sys_role_menu` VALUES (2, 1054);
INSERT INTO `sys_role_menu` VALUES (2, 1055);
INSERT INTO `sys_role_menu` VALUES (2, 1056);
INSERT INTO `sys_role_menu` VALUES (2, 1057);
INSERT INTO `sys_role_menu` VALUES (2, 1058);
INSERT INTO `sys_role_menu` VALUES (2, 1059);
INSERT INTO `sys_role_menu` VALUES (2, 1060);
INSERT INTO `sys_role_menu` VALUES (2, 1061);
INSERT INTO `sys_role_menu` VALUES (100, 1);
INSERT INTO `sys_role_menu` VALUES (100, 100);
INSERT INTO `sys_role_menu` VALUES (100, 1000);
INSERT INTO `sys_role_menu` VALUES (100, 1001);
INSERT INTO `sys_role_menu` VALUES (100, 1002);
INSERT INTO `sys_role_menu` VALUES (100, 1003);
INSERT INTO `sys_role_menu` VALUES (100, 1004);
INSERT INTO `sys_role_menu` VALUES (100, 1005);
INSERT INTO `sys_role_menu` VALUES (100, 1006);

-- ----------------------------
-- Table structure for sys_user
-- ----------------------------
DROP TABLE IF EXISTS `sys_user`;
CREATE TABLE `sys_user`  (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `dept_id` bigint(20) NULL DEFAULT NULL COMMENT '部门ID',
  `login_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '登录账号',
  `user_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '用户昵称',
  `user_type` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '00' COMMENT '用户类型（00系统用户 01注册用户）',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '用户邮箱',
  `phonenumber` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '手机号码',
  `sex` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '用户性别（0男 1女 2未知）',
  `avatar` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '头像路径',
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '盐加密',
  `status` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '账号状态（0正常 1停用）',
  `del_flag` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '删除标志（0代表存在 2代表删除）',
  `login_ip` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '最后登录IP',
  `login_date` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `pwd_update_date` datetime NULL DEFAULT NULL COMMENT '密码最后更新时间',
  `create_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '创建者',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '更新者',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 101 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户信息表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_user
-- ----------------------------
INSERT INTO `sys_user` VALUES (1, 103, 'admin', '意象', '00', 'ry@163.com', '15888888888', '1', '', '29c67a30398638269fe600f73a054934', '111111', '0', '0', '127.0.0.1', '2026-09-09 08:05:32', NULL, 'admin', '2026-09-03 16:41:16', '', NULL, '管理员');
INSERT INTO `sys_user` VALUES (2, 105, 'ry', '意象', '00', 'ry@qq.com', '15666666666', '1', '', '8e6d98b90472783cc73c17047ddccf36', '222222', '0', '0', '127.0.0.1', NULL, NULL, 'admin', '2026-09-03 16:41:16', '', NULL, '测试员');
INSERT INTO `sys_user` VALUES (100, 103, 'yshop', 'yshop', '00', 'yshop@qq.com', '15888888888', '0', '', '7c9911677d804336e2488079aa0f2ba4', '4c7f79', '0', '0', '127.0.0.1', '2026-09-06 10:20:12', '2026-09-06 07:46:45', 'admin', '2026-09-06 07:46:45', '', NULL, '');

-- ----------------------------
-- Table structure for sys_user_online
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_online`;
CREATE TABLE `sys_user_online`  (
  `sessionId` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户会话id',
  `login_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录账号',
  `dept_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '部门名称',
  `ipaddr` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录IP地址',
  `login_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录地点',
  `browser` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '浏览器类型',
  `os` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '操作系统',
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '在线状态on_line在线off_line离线',
  `start_timestamp` datetime NULL DEFAULT NULL COMMENT 'session创建时间',
  `last_access_time` datetime NULL DEFAULT NULL COMMENT 'session最后访问时间',
  `expire_time` int(5) NULL DEFAULT 0 COMMENT '超时时间，单位为分钟',
  `session_data` blob NULL COMMENT '序列化的Session数据，用于服务重启后恢复会话',
  PRIMARY KEY (`sessionId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '在线用户记录' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_user_online
-- ----------------------------
INSERT INTO `sys_user_online` VALUES ('0301b41c9fa7788ee586cfad6185a877', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 13:49:03', '2026-09-04 14:04:41', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('19a8e1c8b45d17e8e15804204f27031c', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 17:03:24', '2026-09-04 17:21:44', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('1ea523942efe1b3e45ad0b75462a576c', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-06 07:44:13', '2026-09-06 08:15:08', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('20420976d0dbc1d84cf8d4bd3f102e32', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 22:26:50', '2026-09-04 22:27:10', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('2a2757e596f8351b2dff2537b3592d9e', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 22:43:12', '2026-09-04 22:43:13', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('4040c97c2d4abb3e9c18751bd598418a', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-07 09:57:22', '2026-09-07 10:15:02', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('45d71768f3edf33cf98b9ba71186023f', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-06 10:20:29', '2026-09-06 10:33:18', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('478fbc901c5715d2c1b4581be8535d8d', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-09 07:10:45', '2026-09-09 07:11:02', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('513693e9fc7f85b72f7265a04cbf67fc', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-08 22:38:23', '2026-09-08 23:49:34', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('a2454882b8f3adb56013155572200d44', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-09 08:05:32', '2026-09-09 09:51:33', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('c24b4fb8e1a8765c7a07244a08c08f4e', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 22:43:30', '2026-09-04 22:43:33', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('cbefdf7d40f537a2a549af3f0fb1dcf2', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 22:53:01', '2026-09-04 22:53:04', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('da4edaab55ef018195e93cf666476287', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-07 16:53:39', '2026-09-07 16:54:14', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('de53ace937d350a53dd443f75ada5067', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 23:16:13', '2026-09-04 23:48:34', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('e0cebef89d38c1005ade8cbea97973e3', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 22:39:44', '2026-09-04 22:39:48', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('e0ee0a8e5684e28207089a361bc7417c', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-05 17:27:48', '2026-09-05 17:35:55', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('e3cd8ad67f1f451d26984515fef076a7', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-05 22:53:05', '2026-09-05 23:39:31', 1440, NULL);
INSERT INTO `sys_user_online` VALUES ('f36908743504244dc96a0411a6dfe6cb', 'admin', '研发部门', '127.0.0.1', '', 'Chrome', 'Windows 10', 'on_line', '2026-09-04 22:35:42', '2026-09-04 22:35:42', 1440, NULL);

-- ----------------------------
-- Table structure for sys_user_post
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_post`;
CREATE TABLE `sys_user_post`  (
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `post_id` bigint(20) NOT NULL COMMENT '岗位ID',
  PRIMARY KEY (`user_id`, `post_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户与岗位关联表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_user_post
-- ----------------------------
INSERT INTO `sys_user_post` VALUES (1, 1);
INSERT INTO `sys_user_post` VALUES (2, 2);

-- ----------------------------
-- Table structure for sys_user_role
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_role`;
CREATE TABLE `sys_user_role`  (
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `role_id` bigint(20) NOT NULL COMMENT '角色ID',
  PRIMARY KEY (`user_id`, `role_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户和角色关联表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_user_role
-- ----------------------------
INSERT INTO `sys_user_role` VALUES (1, 1);
INSERT INTO `sys_user_role` VALUES (2, 2);
INSERT INTO `sys_user_role` VALUES (100, 100);

SET FOREIGN_KEY_CHECKS = 1;
