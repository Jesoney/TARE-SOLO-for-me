-- 个人资产管理系统数据库脚本
-- 创建数据库
CREATE DATABASE IF NOT EXISTS asset_manager DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE asset_manager;

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '用户ID',
    username VARCHAR(50) NOT NULL UNIQUE COMMENT '用户名',
    password VARCHAR(255) NOT NULL COMMENT '密码(加密存储)',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT '邮箱',
    phone VARCHAR(20) DEFAULT NULL COMMENT '手机号',
    avatar VARCHAR(255) DEFAULT NULL COMMENT '头像',
    role TINYINT DEFAULT 0 COMMENT '角色: 0-普通用户, 1-管理员',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 分类表
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '分类ID',
    name VARCHAR(50) NOT NULL UNIQUE COMMENT '分类名称',
    icon VARCHAR(50) DEFAULT 'fa-folder' COMMENT '图标类名',
    color VARCHAR(20) DEFAULT '#1e3a5f' COMMENT '颜色',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='资产分类表';

-- 资产表
CREATE TABLE IF NOT EXISTS assets (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '资产ID',
    user_id INT NOT NULL COMMENT '用户ID',
    category_id INT NOT NULL COMMENT '分类ID',
    name VARCHAR(100) NOT NULL COMMENT '资产名称',
    amount DECIMAL(15,2) NOT NULL COMMENT '金额',
    description TEXT DEFAULT NULL COMMENT '描述',
    purchase_date DATE DEFAULT NULL COMMENT '购买日期',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='资产表';

-- 插入默认分类数据
INSERT INTO categories (name, icon, color) VALUES
('现金', 'fa-money-bill-wave', '#28a745'),
('银行存款', 'fa-university', '#17a2b8'),
('股票基金', 'fa-chart-line', '#dc3545'),
('房产', 'fa-home', '#f39c12'),
('车辆', 'fa-car', '#6f42c1'),
('电子产品', 'fa-laptop', '#1e3a5f'),
('珠宝首饰', 'fa-gem', '#e83e8c'),
('其他', 'fa-box', '#6c757d');

-- 插入管理员账号 (密码: admin123)
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 1);

-- 插入测试用户 (密码: user123)
INSERT INTO users (username, password, email, role) VALUES
('testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test@example.com', 0);

-- 插入测试资产数据
INSERT INTO assets (user_id, category_id, name, amount, description, purchase_date) VALUES
(2, 1, '手头现金', 5000.00, '日常备用现金', '2024-01-01'),
(2, 2, '工商银行储蓄', 50000.00, '工资卡储蓄', '2024-01-15'),
(2, 2, '建设银行定期', 100000.00, '三年定期存款', '2024-03-01'),
(2, 3, '贵州茅台股票', 80000.00, '长期持有', '2024-02-15'),
(2, 4, '自住房产', 2000000.00, '位于市中心的三居室', '2020-06-01'),
(2, 5, '家用轿车', 150000.00, '丰田凯美瑞', '2023-08-15'),
(2, 6, 'MacBook Pro', 15000.00, '工作用笔记本电脑', '2024-01-20'),
(2, 7, '金项链', 8000.00, '24K金项链', '2023-12-25');
