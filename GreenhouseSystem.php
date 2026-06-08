<?php
require_once 'config.php';

class GreenhouseSystem {
    private $db;

    public function __construct($pdoInstance) {
        $this->db = $pdoInstance;
    }

    // Gamification: Paylaşım Ödülü Sistemi
    public function rewardShare($userId, $platform) {
        $stmt = $this->db->prepare("UPDATE users SET fide_count = fide_count + 2 WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    // Özel Sebze Üretim Talebi
    public function requestCustomVegetable($userId, $vegName) {
        $stmt = $this->db->prepare("INSERT INTO custom_requests (user_id, vegetable_name, status) VALUES (?, ?, 'Beklemede')");
        return $stmt->execute([$userId, htmlspecialchars($vegName)]);
    }

    // Bağışçının Mühendise Soru Sorması
    public function askEngineer($userId, $question) {
        $stmt = $this->db->prepare("INSERT INTO messages (user_id, question) VALUES (?, ?)");
        return $stmt->execute([$userId, htmlspecialchars($question)]);
    }

    // Mühendisin Soruyu Cevaplaması
    public function answerQuestion($msgId, $engineerId, $answer) {
        $stmt = $this->db->prepare("UPDATE messages SET engineer_id = ?, answer = ?, answered_at = NOW() WHERE id = ?");
        return $stmt->execute([$engineerId, htmlspecialchars($answer), $msgId]);
    }

    // Fide Bağışı Yapma Fonksiyonu
    public function makeDonation($userId, $vegetableId, $quantity) {
        $quantity = (int)$quantity;
        if($quantity <= 0) return false;

        // Kullanıcının yeterli fidesi var mı kontrol et
        $userStmt = $this->db->prepare("SELECT fide_count FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();

        if ($user && $user['fide_count'] >= $quantity) {
            $this->db->beginTransaction();
            try {
                // Fide düşürülüyor
                $updateUser = $this->db->prepare("UPDATE users SET fide_count = fide_count - ? WHERE id = ?");
                $updateUser->execute([$quantity, $userId]);

                // Bağış kaydı ekleniyor
                $insertDonation = $this->db->prepare("INSERT INTO donations (user_id, vegetable_id, quantity, status) VALUES (?, ?, ?, 'Fide')");
                $insertDonation->execute([$userId, $vegetableId, $quantity]);

                $this->db->commit();
                return true;
            } catch (Exception $e) {
                $this->db->rollBack();
                return false;
            }
        }
        return false;
    }

    // Ata Tohumu Satın Alma Fonksiyonu (+Fide Kazandırır)
    public function buyAtaSeed($userId, $vegetableId) {
        $stmt = $this->db->prepare("SELECT price_ata_seed FROM vegetables WHERE id = ? AND is_ata_tohumu = 1");
        $stmt->execute([$vegetableId]);
        $veg = $stmt->fetch();

        if ($veg) {
            // Satın alma simüle edilir ve kullanıcıya 5 bonus fide hediye edilir
            $updateUser = $this->db->prepare("UPDATE users SET fide_count = fide_count + 5 WHERE id = ?");
            return $updateUser->execute([$userId]);
        }
        return false;
    }

    // IoT Durum Verisi Alma ve Otomatik Simülasyon Mekanizması
    public function getIoTStatus() {
        $stmt = $this->db->query("SELECT * FROM greenhouse_status ORDER BY id DESC LIMIT 1");
        $status = $stmt->fetch();

        if (!$status) {
            // Veritabanında veri yoksa rastgele kritik veya normal simüle veri üretilir
            $temp = rand(12, 42);
            $moisture = rand(15, 90);

            $temp_status = 'NORMAL';
            if ($temp > 35) $temp_status = 'CRITICAL_HIGH';
            elseif ($temp < 15) $temp_status = 'CRITICAL_LOW';

            $moisture_status = 'NORMAL';
            if ($moisture < 30) $moisture_status = 'NEED_WATER';
            elseif ($moisture > 80) $moisture_status = 'OVER_WATERED';

            $insert = $this->db->prepare("INSERT INTO greenhouse_status (temperature, moisture, temp_status, moisture_status) VALUES (?, ?, ?, ?)");
            $insert->execute([$temp, $moisture, $temp_status, $moisture_status]);

            return [
                'temperature' => $temp,
                'moisture' => $moisture,
                'temp_status' => $temp_status,
                'moisture_status' => $moisture_status
            ];
        }
        return $status;
    }

    // Admin: Özel Talebi Güncelleme
    public function updateCustomRequest($requestId, $status) {
        $stmt = $this->db->prepare("UPDATE custom_requests SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $requestId]);
    }
}
?>