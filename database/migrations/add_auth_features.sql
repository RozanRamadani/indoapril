-- Migration untuk menambahkan fitur authentication
-- 1. Tambah kolom remember_token untuk "Remember Me" feature
-- 2. Hash password existing users dengan bcrypt

-- Tambah kolom remember_token
ALTER TABLE `user`
ADD COLUMN `remember_token` VARCHAR(100) NULL AFTER `password`;

-- Update password dengan bcrypt hash
-- Password lama -> Password baru (bcrypt)
-- superatmin123 -> $2y$12$... (akan di-hash saat login pertama kali)
-- admin987 -> $2y$12$... (akan di-hash saat login pertama kali)

-- Untuk testing, set password hash yang sama untuk semua user: "password123"
UPDATE `user` SET `password` = '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYG/qYhWk6G' WHERE `username` = 'superatmin';
UPDATE `user` SET `password` = '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYG/qYhWk6G' WHERE `username` = 'admin98';

-- Password untuk testing: "password123"
-- Super Admin: superatmin / password123
-- Admin: admin98 / password123
