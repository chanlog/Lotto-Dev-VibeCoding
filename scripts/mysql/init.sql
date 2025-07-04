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

-- 로또 회차 정보 초기 데이터 (최근 몇 회차)
INSERT INTO lotto_draws (draw_no, draw_date, number1, number2, number3, number4, number5, number6, bonus_number, prize_amount, winners) VALUES
(1100, '2024-01-06', 1, 5, 8, 17, 41, 43, 31, 2800000000, 12),
(1101, '2024-01-13', 7, 19, 20, 25, 30, 39, 37, 2400000000, 8),
(1102, '2024-01-20', 2, 6, 12, 18, 36, 44, 9, 3200000000, 15);

-- 기본 통계 데이터
INSERT INTO number_statistics (number, frequency, last_drawn, hot_rank) VALUES
(1, 85, '2024-01-06', 1),
(2, 82, '2024-01-20', 2),
(3, 78, '2024-01-01', 3),
(4, 75, '2023-12-30', 4),
(5, 88, '2024-01-06', 1);