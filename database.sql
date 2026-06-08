CREATE DATABASE IF NOT EXISTS akilli_sera DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE akilli_sera;

-- 1. Kullanıcılar Tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'engineer', 'user') NOT NULL DEFAULT 'user',
    fide_count INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 2. Sebze Kütüphanesi Tablosu
CREATE TABLE IF NOT EXISTS vegetables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    history TEXT NOT NULL,
    growing_instructions TEXT NOT NULL,
    is_ata_tohumu TINYINT(1) NOT NULL DEFAULT 0,
    price_ata_seed DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB;

-- 3. Bağışlar Tablosu
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vegetable_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    status ENUM('Fide', 'Büyüyor', 'Hasat', 'Dağıtıldı') NOT NULL DEFAULT 'Fide',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vegetable_id) REFERENCES vegetables(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Mesajlar (Soru-Cevap) Tablosu
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    engineer_id INT NULL,
    question TEXT NOT NULL,
    answer TEXT NULL,
    asked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    answered_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (engineer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Özel Üretim Talepleri Tablosu
CREATE TABLE IF NOT EXISTS custom_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vegetable_name VARCHAR(100) NOT NULL,
    status ENUM('Beklemede', 'Onaylandı', 'Reddedildi') NOT NULL DEFAULT 'Beklemede',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. IoT Sera Durum Tablosu
CREATE TABLE IF NOT EXISTS greenhouse_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temperature DECIMAL(4,2) NOT NULL,
    moisture DECIMAL(4,2) NOT NULL,
    temp_status VARCHAR(50) NOT NULL,
    moisture_status VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Hazır Verilerin Eklenmesi (Seed Data)
INSERT INTO users (username, email, password, role, fide_count) VALUES
('ruveyda', 'admin@sera.com', '$2y$10$mC28bNUXzT7O2FPhcI8Nxe1DClxO3u2UoUWhR0V3VvNenqR1k.IWO', 'admin', 50),
('ahmet_muhendis', 'engineer@sera.com', '$2y$10$mC28bNUXzT7O2FPhcI8Nxe1DClxO3u2UoUWhR0V3VvNenqR1k.IWO', 'engineer', 0),
('mehmet_bagisci', 'user@sera.com', '$2y$10$mC28bNUXzT7O2FPhcI8Nxe1DClxO3u2UoUWhR0V3VvNenqR1k.IWO', 'user', 10);

INSERT INTO vegetables (name, history, growing_instructions, is_ata_tohumu, price_ata_seed) VALUES
('Domates', 'Güney ve Orta Amerika kökenli olan domates, küresel mutfağın vazgeçilmezidir.', 'Bol güneş alan, drenajı iyi topraklarda düzenli sulama ile yetiştirilir.', 0, 0.00),
('Ayaş Domatesi', 'Ankaranın Ayaş ilçesine özgü, etli, hafif ekşimsi ve yoğun kokulu geleneksel yerli türdür.', 'Fideler derin dikilmeli, can suyu verilmeli ve organik gübre ile desteklenmelidir.', 1, 45.50),
('Kandıra Karpuzu', 'Kocaeli Kandıra bölgesinde yetişen, yüksek şeker oranı ve ince kabuğuyla bilinen nadide bir ırktır.', 'Geniş arazi ister, meyve döküm döneminde sulama azaltılarak tatlanması sağlanır.', 1, 60.00),
('Salatalık', 'Hindistan kökenli olup, %95 oranında su içeren ferahlatıcı bir yaz sebzesidir.', 'Sırık desteği ister, sabah erken saatlerde dipten sulama yapılması önerilir.', 0, 0.00),
('Biber', 'C vitamini deposu olan acı ve tatlı çeşitleri bulunan bir kapalı tohumludur.', 'Sıcak toprak sever, aşırı sulamadan kaçınılmalı, yapraklarına su değdirilmemelidir.', 0, 0.00),
('Patlıcan', 'Ilık iklimleri seven, Hindistan menşeili, lif oranı yüksek bir sebzedir.', 'Derin ve humuslu topraklarda, güneş ışığını doğrudan alacak şekilde konumlandırılmalıdır.', 0, 0.00),
('Marul', 'Antik Mısırdan beri yetiştirilen, hızlı büyüyen serin iklim salata bitkisidir.', 'Yüzeysel köklüdür, toprağı sürekli nemli tutulmalı, gölge tüllerle korunmalıdır.', 0, 0.00);