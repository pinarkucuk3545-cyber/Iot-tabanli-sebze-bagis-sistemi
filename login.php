<?php
require_once 'config.php';

// Eğer zaten giriş yapılmışsa doğrudan dashboard'a yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ----------------------------------------------------
    // 🔑 GİRİŞ YAPMA İŞLEMİ (LOGIN)
    // ----------------------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            // ACİL DURUM SÜPER ADMIN BYPASS (Her ihtimale karşı koruyoruz)
            if ($username === 'admin_ruveyda' && $password === '123456') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'admin_ruveyda';
                $_SESSION['role'] = 'admin';
                header("Location: dashboard.php");
                exit;
            }

            // Normal Veritabanı Giriş Kontrolü
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Hatalı kullanıcı adı veya şifre!";
            }
        } else {
            $error = "Lütfen tüm alanları doldurun.";
        }
    }

    // ----------------------------------------------------
    // 📝 KAYIT OLMA İŞLEMİ (REGISTER)
    // ----------------------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = trim($_POST['reg_username']);
        $email = trim($_POST['reg_email']);
        $password = trim($_POST['reg_password']);

        if (!empty($username) && !empty($email) && !empty($password)) {
            // Kullanıcı adı veya E-posta sistemde zaten var mı kontrolü
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = "Bu kullanıcı adı veya e-posta adresi zaten kullanımda!";
            } else {
                // Yeni kullanıcıyı kaydet (Rol varsayılan olarak 'user', hoş geldin bonusu +10 fide!)
                $insStmt = $pdo->prepare("INSERT INTO users (username, email, password, role, fide_count) VALUES (?, ?, ?, 'user', 10)");
                if ($insStmt->execute([$username, $email, $password])) {
                    $success = "Hesabınız başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.";
                } else {
                    $error = "Kayıt sırasında bir hata oluştu, lütfen tekrar deneyin.";
                }
            }
        } else {
            $error = "Lütfen kayıt formundaki tüm alanları doldurun.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Akıllı Sera - Giriş ve Kayıt Merkezi</title>
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --neon-green: #10b981;
            --neon-glow: #34d399;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        body { 
            background: linear-gradient(135deg, #0f172a 0%, #022c22 100%); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            color: var(--text-main);
            overflow: hidden;
            position: relative;
        }

        /* Arka Plana Canlılık Katan Teknolojik Hücre Detayları */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--neon-green);
            border-radius: 50%;
            filter: blur(150px);
            opacity: 0.15;
            top: 10%;
            left: 15%;
            z-index: 0;
        }
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: #0284c7;
            border-radius: 50%;
            filter: blur(180px);
            opacity: 0.1;
            bottom: 10%;
            right: 15%;
            z-index: 0;
        }

        .container {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 10;
        }

        .brand-header { text-align: center; margin-bottom: 30px; }
        .brand-logo { font-size: 3rem; margin-bottom: 10px; animation: pulse 2s infinite; display: inline-block; }
        .brand-header h2 { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; background: linear-gradient(to right, #fff, var(--neon-glow)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .brand-header p { color: var(--text-muted); font-size: 0.9rem; margin-top: 5px; }

        /* Form Geçiş Menüsü */
        .form-toggle-bar {
            display: flex;
            background: rgba(15, 23, 42, 0.6);
            padding: 6px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .toggle-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 10px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.3s ease;
            font-size: 0.95rem;
        }
        .toggle-btn.active {
            background: var(--neon-green);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .form-box { display: none; }
        .form-box.active { display: block; animation: fadeIn 0.4s ease; }

        .form-group { margin-bottom: 20px; position: relative; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.85rem; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .input-wrapper { position: relative; }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--neon-green);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .btn-submit {
            background: linear-gradient(to right, var(--neon-green), #059669);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            width: 100%;
            font-size: 1rem;
            margin-top: 10px;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        /* Bildirim Kutuları */
        .status-alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; text-align: center; border: 1px solid; }
        .status-alert.error { background: rgba(239, 68, 68, 0.1); color: #f87171; border-color: rgba(239, 68, 68, 0.2); }
        .status-alert.success { background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.2); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0%, 100% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(16,185,129,0.2)); } 50% { transform: scale(1.05); filter: drop-shadow(0 0 15px rgba(16,185,129,0.6)); } }
    </style>
</head>
<body>

    <div class="container">
        <!-- Marka ve Logo Alanı -->
        <div class="brand-header">
            <div class="brand-logo">🌿</div>
            <h2>BIO-SERA IoT</h2>
            <p>Smart Greenhouse Ecosystem Manager</p>
        </div>

        <!-- Dinamik Durum Mesajları -->
        <?php if(!empty($error)): ?>
            <div class="status-alert error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="status-alert success">✅ <?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Giriş / Kayıt Geçiş Menüsü -->
        <div class="form-toggle-bar">
            <button class="toggle-btn active" onclick="switchForm('login-box', this)">Giriş Yap</button>
            <button class="toggle-btn" onclick="switchForm('register-box', this)">Kayıt Ol</button>
        </div>

        <!-- 1. GİRİŞ YAPMA FORMU -->
        <div id="login-box" class="form-box active">
            <form action="login.php" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="username" class="form-control" placeholder="Örn: admin_ruveyda" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••" required>
                </div>
                
                <button type="submit" class="btn-submit">Sisteme Bağlan</button>
            </form>
        </div>

        <!-- 2. YENİ KAYIT OLMA FORMU -->
        <div id="register-box" class="form-box">
            <form action="login.php" method="POST">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="reg_username" class="form-control" placeholder="Yeni kullanıcı adınız" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label>E-Posta Adresi</label>
                    <input type="email" name="reg_email" class="form-control" placeholder="ornek@sera.com" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label>Güçlü Bir Şifre</label>
                    <input type="password" name="reg_password" class="form-control" placeholder="Şifrenizi belirleyin" required>
                </div>
                
                <button type="submit" class="btn-submit" style="background: linear-gradient(to right, #0284c7, #0369a1); box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);">Hesap Oluştur (+10 Fide)</button>
            </form>
        </div>
    </div>

    <!-- Form Değiştirici JavaScript Sihirli Dokunuşu -->
    <script>
        function switchForm(formId, button) {
            // Tüm form kutularını gizle
            document.querySelectorAll('.form-box').forEach(box => {
                box.classList.remove('active');
            });
            // Tüm butonların aktiflik sınıflarını kaldır
            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Seçilen formu göster ve tıklanan butonu aktif yap
            document.getElementById(formId).classList.add('active');
            button.classList.add('active');
        }

        // Eğer kayıt başarılı olduysa otomatik giriş sekmesine odaklanması için küçük bir kontrol
        <?php if(!empty($success)): ?>
            switchForm('login-box', document.querySelectorAll('.toggle-btn')[0]);
        <?php endif; ?>
    </script>

</body>
</html>