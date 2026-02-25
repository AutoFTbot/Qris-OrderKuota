<?php
/**
 * OrderKuota Telegram Bot
 * 
 * A Telegram bot for accessing OrderKuota services
 * Features: Login, OTP verification, balance check, transaction history
 * 
 * @author AutoFtBot
 * @version 1.0.0
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable in production
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/telegram_bot_errors.log');

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set as environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
    return true;
}

// Load .env from parent directory
$envPath = __DIR__ . '/../.env';
if (!loadEnv($envPath)) {
    error_log("Warning: .env file not found at $envPath");
}

// Bot configuration - Read from environment variables
$botToken = getenv('TELEGRAM_BOT_TOKEN');
$webhookUrl = getenv('WEBHOOK_URL');

// Validate required configuration
if (empty($botToken) || empty($webhookUrl)) {
    error_log("Error: TELEGRAM_BOT_TOKEN and WEBHOOK_URL must be set in .env file");
    http_response_code(500);
    die(json_encode(['error' => 'Bot configuration missing. Please check .env file.']));
}

// Configuration constants
define('SESSIONS_FILE', '/tmp/telegram_sessions.json');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('API_BASE_URL', 'https://app.orderkuota.com/api/v2');
define('API_TIMEOUT', 20);

// Device fingerprint constants
define('APP_REG_ID', 'eU0F2tV4Sb-ZiIRJ6SIUwl:APA91bGMegNFa1MV2kM6IluttHctVm4rg2hjN-vW1tydMTh5HWwlY61PAqiglIPWshp40ySRhvUPMEdZq6xxnvjlksYwc0ArsD-OlPeM2dSSMXC6JBWrYc4');
define('PHONE_UUID', 'eU0F2tV4Sb-ZiIRJ6SIUwl');
define('PHONE_MODEL', 'sdk_gphone_x86');
define('PHONE_ANDROID_VERSION', '11');
define('APP_VERSION_CODE', '250918');
define('APP_VERSION_NAME', '25.09.18');

// ============================================================================
// SESSION MANAGEMENT
// ============================================================================

/**
 * Load sessions from file storage
 * @return array Sessions data
 */
function loadSessions() {
    if (file_exists(SESSIONS_FILE)) {
        $data = file_get_contents(SESSIONS_FILE);
        return json_decode($data, true) ?: [];
    }
    return [];
}

/**
 * Save sessions to file storage
 * @param array $sessions Sessions data to save
 * @return bool Success status
 */
function saveSessions($sessions) {
    return file_put_contents(SESSIONS_FILE, json_encode($sessions, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Clean expired sessions
 * @param array $sessions Sessions data
 * @return array Cleaned sessions
 */
function cleanExpiredSessions($sessions) {
    $cleaned = false;
    foreach ($sessions as $id => $session) {
        if (isset($session['login_time']) && (time() - $session['login_time']) > SESSION_TIMEOUT) {
            unset($sessions[$id]);
            $cleaned = true;
            
            // Clean cookie file
            $cookieFile = '/tmp/orderkuota_cookies_' . $id . '.txt';
            if (file_exists($cookieFile)) {
                @unlink($cookieFile);
            }
        }
    }
    if ($cleaned) {
        saveSessions($sessions);
    }
    return $sessions;
}

$sessions = loadSessions();

// ============================================================================
// TELEGRAM API FUNCTIONS
// ============================================================================

/**
 * Send message to Telegram chat
 * @param int $chatId Chat ID
 * @param string $text Message text
 * @param array|null $replyMarkup Reply markup (keyboard)
 * @param bool $deletePrevious Delete previous message
 * @return bool Success status
 */
function sendMessage($chatId, $text, $replyMarkup = null, $deletePrevious = false) {
    global $botToken;
    
    if ($deletePrevious) {
        deleteLastMessage($chatId);
    }
    
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    if ($replyMarkup) {
        $data['reply_markup'] = $replyMarkup;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("Telegram API error: " . $error);
        return false;
    }
    
    $response = json_decode($result, true);
    if ($response && isset($response['result']['message_id'])) {
        storeLastMessageId($chatId, $response['result']['message_id']);
        return true;
    }
    
    return false;
}

/**
 * Store last message ID for deletion
 * @param int $chatId Chat ID
 * @param int $messageId Message ID
 */
function storeLastMessageId($chatId, $messageId) {
    global $sessions;
    if (!isset($sessions[$chatId])) {
        $sessions[$chatId] = [];
    }
    $sessions[$chatId]['last_message_id'] = $messageId;
    saveSessions($sessions);
}

/**
 * Delete last message from chat
 * @param int $chatId Chat ID
 */
function deleteLastMessage($chatId) {
    global $botToken, $sessions;
    
    if (isset($sessions[$chatId]['last_message_id'])) {
        $messageId = $sessions[$chatId]['last_message_id'];
        $url = "https://api.telegram.org/bot{$botToken}/deleteMessage";
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
        
        unset($sessions[$chatId]['last_message_id']);
        saveSessions($sessions);
    }
}

// ============================================================================
// ORDERKUOTA API FUNCTIONS
// ============================================================================

/**
 * Build common API request fields
 * @param string $authUsername Username for authentication
 * @param string $authToken Authentication token
 * @return array Common request fields
 */
function buildCommonFields($authUsername = '', $authToken = '') {
    return [
        'app_reg_id' => APP_REG_ID,
        'phone_uuid' => PHONE_UUID,
        'phone_model' => PHONE_MODEL,
        'request_time' => (string) round(microtime(true) * 1000),
        'phone_android_version' => PHONE_ANDROID_VERSION,
        'app_version_code' => APP_VERSION_CODE,
        'auth_username' => $authUsername,
        'auth_token' => $authToken,
        'app_version_name' => APP_VERSION_NAME,
        'ui_mode' => 'light'
    ];
}

/**
 * Make HTTP request to OrderKuota API
 * @param string $url API endpoint URL
 * @param array $postFields POST data
 * @param int $chatId Chat ID for cookie storage
 * @return array Response with status and body
 */
function makeApiRequest($url, $postFields, $chatId) {
    $proxyUrl = getenv('PROXY_URL');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, API_TIMEOUT);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    if (!empty($proxyUrl)) {
        curl_setopt($ch, CURLOPT_PROXY, $proxyUrl);
    }
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/orderkuota_cookies_' . $chatId . '.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/orderkuota_cookies_' . $chatId . '.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: okhttp/4.12.0',
        'Accept-Encoding: identity'
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("API request error: " . $error);
        return ['status' => 0, 'body' => null, 'error' => $error];
    }

    $body = substr($response, $headerSize);
    return ['status' => $status, 'body' => $body, 'error' => null];
}

/**
 * Login user to OrderKuota
 * @param string $username Username
 * @param string $password Password or OTP code
 * @param int $chatId Chat ID
 * @return array Login result
 */
function loginUser($username, $password, $chatId) {
    global $sessions;
    $sessions = loadSessions();
    
    $postFields = array_merge(buildCommonFields(), [
        'username' => $username,
        'password' => $password
    ]);

    $url = API_BASE_URL . '/login';
    $response = makeApiRequest($url, $postFields, $chatId);
    
    if ($response['error']) {
        if (getenv('LOG_UPSTREAM') === '1') {
            error_log("OrderKuota login error: " . $response['error']);
        }
        return ['success' => false, 'error' => 'Network error: ' . $response['error']];
    }

    $decoded = json_decode($response['body'], true);

    if ($response['status'] >= 200 && $response['status'] < 300 && is_array($decoded)) {
        // Check for OTP requirement
        if (isset($decoded['success']) && $decoded['success'] === true && 
            isset($decoded['results']['otp']) && $decoded['results']['otp'] === 'email') {
            $sessions[$chatId] = [
                'username' => $username,
                'step' => 'otp',
                'otp_value' => $decoded['results']['otp_value'] ?? ''
            ];
            saveSessions($sessions);
            return [
                'success' => true,
                'otp_required' => true,
                'otp_value' => $decoded['results']['otp_value'] ?? ''
            ];
        }
        
        // Check for successful login
        if (isset($decoded['success']) && $decoded['success'] === true && 
            isset($decoded['results']['token']) && isset($decoded['results']['id'])) {
            $sessions[$chatId] = [
                'username' => $username,
                'step' => 'logged_in',
                'auth_token' => $decoded['results']['token'],
                'user_id' => $decoded['results']['id'],
                'name' => $decoded['results']['name'] ?? '',
                'balance' => $decoded['results']['balance'] ?? '0',
                'login_time' => time()
            ];
            saveSessions($sessions);
            return [
                'success' => true,
                'auth_token' => $decoded['results']['token'],
                'user_id' => $decoded['results']['id'],
                'username' => $decoded['results']['username'] ?? $username,
                'name' => $decoded['results']['name'] ?? '',
                'balance' => $decoded['results']['balance'] ?? '0'
            ];
        }
    }

    if (getenv('LOG_UPSTREAM') === '1') {
        $snippet = substr((string)$response['body'], 0, 800);
        error_log("OrderKuota login response: status={$response['status']} body={$snippet}");
    }

    return ['success' => false, 'error' => $decoded['message'] ?? 'Login failed'];
}

/**
 * Get QRIS balance
 * @param int $chatId Chat ID
 * @return array Balance result
 */
function getSaldo($chatId) {
    global $sessions;
    
    if (!isset($sessions[$chatId]) || $sessions[$chatId]['step'] !== 'logged_in') {
        return ['success' => false, 'error' => 'Not logged in'];
    }

    $session = $sessions[$chatId];
    $postFields = array_merge(buildCommonFields($session['username'], $session['auth_token']), [
        'requests[0]' => 'account'
    ]);

    $userId = explode(':', $session['auth_token'])[0];
    $url = API_BASE_URL . '/qris/saldo/' . $userId;
    $response = makeApiRequest($url, $postFields, $chatId);
    
    if ($response['error']) {
        return ['success' => false, 'error' => 'Network error: ' . $response['error']];
    }

    $decoded = json_decode($response['body'], true);

    if ($response['status'] >= 200 && $response['status'] < 300 && is_array($decoded)) {
        return [
            'success' => true,
            'balance' => $decoded['results']['balance'] ?? $session['balance']
        ];
    }

    return ['success' => false, 'error' => $decoded['message'] ?? 'Failed to get balance'];
}

/**
 * Get transaction history (mutasi)
 * @param int $chatId Chat ID
 * @param int $page Page number
 * @return array Transaction history result
 */
function getMutasi($chatId, $page = 1) {
    global $sessions;
    
    if (!isset($sessions[$chatId]) || $sessions[$chatId]['step'] !== 'logged_in') {
        return ['success' => false, 'error' => 'Not logged in'];
    }

    $session = $sessions[$chatId];
    $postFields = array_merge(buildCommonFields($session['username'], $session['auth_token']), [
        'requests[0]' => 'account',
        'requests[qris_history][keterangan]' => '',
        'requests[qris_history][jumlah]' => '',
        'requests[qris_history][page]' => (string) $page,
        'requests[qris_history][dari_tanggal]' => '',
        'requests[qris_history][ke_tanggal]' => ''
    ]);

    $userId = explode(':', $session['auth_token'])[0];
    $url = API_BASE_URL . '/qris/mutasi/' . $userId;
    $response = makeApiRequest($url, $postFields, $chatId);
    
    if ($response['error']) {
        return ['success' => false, 'error' => 'Network error: ' . $response['error']];
    }

    $decoded = json_decode($response['body'], true);

    if ($response['status'] >= 200 && $response['status'] < 300 && is_array($decoded)) {
        return [
            'success' => true,
            'data' => $decoded,
            'page' => $page
        ];
    }

    return ['success' => false, 'error' => $decoded['message'] ?? 'Failed to get transaction history'];
}

/**
 * Format transaction history for display
 * @param array $data Transaction data
 * @param int $limit Number of items to display
 * @return string Formatted message
 */
function formatMutasi($data, $limit = 5) {
    $message = "📊 <b>Mutasi Terbaru</b>\n\n";
    
    if (!isset($data['qris_history']['results']) || !is_array($data['qris_history']['results'])) {
        return $message . "❌ Tidak ada data mutasi";
    }
    
    $items = array_slice($data['qris_history']['results'], 0, $limit);
    foreach ($items as $item) {
        $status = $item['status'] ?? '';
        $type = $status === 'IN' ? '💰' : '💸';
        $originalAmount = $status === 'IN' ? ($item['kredit'] ?? '0') : ($item['debet'] ?? '0');
        $amountFloat = (float)$originalAmount;
        
        // Format amount with proper decimal places
        $formattedAmount = strpos($originalAmount, '.') !== false 
            ? number_format($amountFloat, 3, ',', '.') 
            : number_format($amountFloat, 0, ',', '.');
        
        $date = $item['tanggal'] ?? 'N/A';
        $desc = $item['keterangan'] ?? 'N/A';
        $brand = $item['brand']['name'] ?? '';
        
        $message .= $type . " <code>Rp " . $formattedAmount . "</code>\n";
        $message .= "📅 " . $date . "\n";
        $message .= "📝 " . $desc . "\n";
        if ($brand) {
            $message .= "🏷️ " . $brand . "\n";
        }
        $message .= "\n";
    }
    
    return $message;
}

// ============================================================================
// WEBHOOK HANDLER
// ============================================================================

/**
 * Set webhook URL
 * @return string Result message
 */
function setWebhook() {
    global $botToken, $webhookUrl;
    
    $url = "https://api.telegram.org/bot{$botToken}/setWebhook";
    $data = ['url' => $webhookUrl];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

// Handle webhook
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    if (isset($_GET['setwebhook'])) {
        echo setWebhook();
    } else {
        echo json_encode(['status' => 'ok', 'message' => 'Bot is running']);
    }
    exit;
}

// ============================================================================
// MESSAGE HANDLERS
// ============================================================================

if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $ownerId = getenv('OWNER_CHAT_ID');

    if (!empty($ownerId) && (string)$chatId !== (string)$ownerId) {
        http_response_code(200);
        exit;
    }

    // Load and clean sessions
    $sessions = loadSessions();
    $sessions = cleanExpiredSessions($sessions);

    // Command: /start
    if ($text === '/start') {
        sendMessage($chatId, 
            "🤖 <b>Sebelas Token Bot</b>\n\n" .
            "Selamat datang! Bot untuk akses OrderKuota via Telegram.\n\n" .
            "<b>Commands:</b>\n\n" .
            "/login username password - Login ke akun\n" .
            "/otp kode - Verifikasi OTP\n" .
            "/saldo - Cek saldo QRIS\n" .
            "/mutasi - Lihat mutasi transaksi\n" .
            "/logout - Logout dari akun\n" .
            "/donate - Donasi untuk developer\n" .
            "/help - Bantuan penggunaan"
        );
    }
    // Command: /help
    elseif ($text === '/help') {
        sendMessage($chatId, 
            "📖 <b>Cara Pakai Bot</b>\n\n" .
            "/login username password - Login ke akun\n" .
            "/otp kode - Verifikasi OTP\n" .
            "/saldo - Cek saldo QRIS\n" .
            "/mutasi - Lihat mutasi transaksi\n" .
            "/logout - Logout dari akun\n" .
            "/donate - Donasi untuk developer\n" .
            "/help - Bantuan penggunaan"
        );
    }
    // Command: /login
    elseif (strpos($text, '/login ') === 0) {
        $parts = explode(' ', $text, 3);
        if (count($parts) < 3) {
            sendMessage($chatId, 
                "❌ <b>Format salah!</b>\n\n" .
                "Gunakan: <code>/login username password</code>\n\n" .
                "Contoh: <code>/login user@email.com password123</code>"
            );
        } else {
            $username = trim($parts[1]);
            $password = trim($parts[2]);
            
            sendMessage($chatId, "🔄 Sedang login...", null, true);
            $result = loginUser($username, $password, $chatId);
            
            if ($result['success']) {
                if (isset($result['otp_required'])) {
                    sendMessage($chatId, 
                        "📧 <b>OTP Diperlukan</b>\n\n" .
                        "Kode OTP telah dikirim ke:\n<code>" . htmlspecialchars($result['otp_value']) . "</code>\n\n" .
                        "Silakan cek email Anda dan kirim:\n<code>/otp kode_otp</code>", 
                        null, true
                    );
                } else {
                    sendMessage($chatId, 
                        "✅ <b>Login Berhasil!</b>\n\n" .
                        "👤 Username: <code>" . htmlspecialchars($result['username']) . "</code>\n" .
                        "🏷️ Nama: " . htmlspecialchars($result['name']) . "\n" .
                        "💰 Saldo: <code>Rp " . number_format($result['balance'], 0, ',', '.') . "</code>\n\n" .
                        "Gunakan <code>/help</code> untuk melihat perintah lainnya", 
                        null, true
                    );
                }
            } else {
                sendMessage($chatId, 
                    "❌ <b>Login gagal!</b>\n\n" .
                    "Error: " . htmlspecialchars($result['error'] ?? 'Unknown error') . "\n\n" .
                    "Pastikan username dan password Anda benar.", 
                    null, true
                );
            }
        }
    }
    // Command: /otp
    elseif (strpos($text, '/otp ') === 0) {
        $parts = explode(' ', $text, 2);
        if (count($parts) < 2) {
            sendMessage($chatId, 
                "❌ <b>Format salah!</b>\n\n" .
                "Gunakan: <code>/otp kode_otp</code>\n\n" .
                "Contoh: <code>/otp 123456</code>"
            );
        } else {
            $otp = trim($parts[1]);
            
            if (!isset($sessions[$chatId])) {
                sendMessage($chatId, 
                    "❌ <b>Tidak ada sesi login!</b>\n\n" .
                    "Silakan login terlebih dahulu dengan:\n<code>/login username password</code>"
                );
            } elseif ($sessions[$chatId]['step'] !== 'otp') {
                sendMessage($chatId, 
                    "❌ <b>Tidak dalam proses OTP!</b>\n\n" .
                    "Status saat ini: " . htmlspecialchars($sessions[$chatId]['step']) . "\n\n" .
                    "Silakan login ulang dengan:\n<code>/login username password</code>"
                );
            } else {
                sendMessage($chatId, "🔄 Memverifikasi OTP...", null, true);
                
                $username = $sessions[$chatId]['username'];
                $result = loginUser($username, $otp, $chatId);
                
                if ($result['success'] && !isset($result['otp_required'])) {
                    sendMessage($chatId, 
                        "✅ <b>Login Berhasil!</b>\n\n" .
                        "👤 Username: <code>" . htmlspecialchars($result['username']) . "</code>\n" .
                        "🏷️ Nama: " . htmlspecialchars($result['name']) . "\n" .
                        "💰 Saldo: <code>Rp " . number_format($result['balance'], 0, ',', '.') . "</code>\n\n" .
                        "Gunakan <code>/help</code> untuk melihat perintah lainnya", 
                        null, true
                    );
                } else {
                    sendMessage($chatId, 
                        "❌ <b>Verifikasi OTP gagal!</b>\n\n" .
                        "Kode OTP salah atau sudah kadaluarsa.\n" .
                        "Silakan login ulang untuk mendapatkan kode baru.", 
                        null, true
                    );
                }
            }
        }
    }
    // Command: /saldo
    elseif ($text === '/saldo') {
        if (!isset($sessions[$chatId]) || $sessions[$chatId]['step'] !== 'logged_in') {
            sendMessage($chatId, 
                "❌ <b>Belum login!</b>\n\n" .
                "Silakan login terlebih dahulu dengan:\n<code>/login username password</code>"
            );
        } else {
            sendMessage($chatId, "🔄 Mengambil data saldo...", null, true);
            $result = getSaldo($chatId);
            
            if ($result['success']) {
                sendMessage($chatId, 
                    "💰 <b>Saldo QRIS</b>\n\n" .
                    "💵 <code>Rp " . number_format($result['balance'], 0, ',', '.') . "</code>\n\n" .
                    "Terakhir diperbarui: " . date('d/m/Y H:i:s'), 
                    null, true
                );
            } else {
                sendMessage($chatId, 
                    "❌ <b>Gagal mengambil saldo!</b>\n\n" .
                    "Error: " . htmlspecialchars($result['error']) . "\n\n" .
                    "Coba login ulang jika masalah berlanjut.", 
                    null, true
                );
            }
        }
    }
    // Command: /mutasi
    elseif ($text === '/mutasi') {
        if (!isset($sessions[$chatId]) || $sessions[$chatId]['step'] !== 'logged_in') {
            sendMessage($chatId, 
                "❌ <b>Belum login!</b>\n\n" .
                "Silakan login terlebih dahulu dengan:\n<code>/login username password</code>"
            );
        } else {
            sendMessage($chatId, "🔄 Mengambil data mutasi...", null, true);
            $result = getMutasi($chatId);
            
            if ($result['success']) {
                $message = formatMutasi($result['data']);
                sendMessage($chatId, $message, null, true);
            } else {
                sendMessage($chatId, 
                    "❌ <b>Gagal mengambil mutasi!</b>\n\n" .
                    "Error: " . htmlspecialchars($result['error']) . "\n\n" .
                    "Coba login ulang jika masalah berlanjut.", 
                    null, true
                );
            }
        }
    }
    // Command: /logout
    elseif ($text === '/logout') {
        if (isset($sessions[$chatId])) {
            unset($sessions[$chatId]);
            saveSessions($sessions);
            
            // Clean cookie file
            $cookieFile = '/tmp/orderkuota_cookies_' . $chatId . '.txt';
            if (file_exists($cookieFile)) {
                @unlink($cookieFile);
            }
            
            sendMessage($chatId, 
                "✅ <b>Logout berhasil!</b>\n\n" .
                "Sesi Anda telah dihapus.\n" .
                "Gunakan <code>/login</code> untuk masuk kembali.", 
                null, true
            );
        } else {
            sendMessage($chatId, 
                "❌ <b>Anda belum login!</b>\n\n" .
                "Tidak ada sesi aktif untuk di-logout."
            );
        }
    }
    // Command: /donate
    elseif ($text === '/donate') {
            sendMessage($chatId, 
                "💝 <b>Dukung Pengembangan Bot</b>\n\n" .
                "Terima kasih atas dukungannya! 🙏\n\n" .
                "🔗 <a href=\"https://buymeacoffee.com/aliahmadnawawi\">Buy Me a Coffee</a>\n\n" .
                "Setiap donasi sangat berarti untuk pengembangan bot ini!"
            );
    }
    // Unknown command
    else {
        sendMessage($chatId, 
            "❓ <b>Command tidak dikenal</b>\n\n" .
            "Ketik <code>/help</code> untuk melihat daftar perintah yang tersedia."
        );
    }
}
