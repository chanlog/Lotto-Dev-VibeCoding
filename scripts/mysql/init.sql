-- 초기 데이터베이스 설정
CREATE DATABASE IF NOT EXISTS lotto_analysis;
CREATE DATABASE IF NOT EXISTS lotto_test;

-- 사용자 생성 및 권한 부여
CREATE USER IF NOT EXISTS 'lotto_user'@'%' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON lotto_analysis.* TO 'lotto_user'@'%';
GRANT ALL PRIVILEGES ON lotto_test.* TO 'lotto_user'@'%';
FLUSH PRIVILEGES;

-- 기본 설정
USE lotto_analysis;
SET time_zone = '+09:00';