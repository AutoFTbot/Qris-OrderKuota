/**
 * QRIS Mutation API (Sunda Version) by AutoFtBot
 *
 * This script fetches QRIS mutation data from OrderKuota by emulating
 * the Android app flow with signature authentication.
 *
 * Features:
 * - HMAC-SHA256 signature authentication
 * - Deterministic device fingerprinting
 * - Comprehensive error handling
 * - Rate limit handling
 * - Well-formed request headers
 */

set_time_limit(90);
ini_set('max_execution_time', 90);
ini_set('max_input_time', 90);
ini_set('default_socket_timeout', 15);

define('MODE_DEBUG', false);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Signature, Timestamp');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method tidak diizinkan']);
    exit;
}

/**
 * ApiMutasi class
 * Handles fetching and formatting mutation data with signature authentication.
 */
class ApiMutasi {
    private const KONCI_RAHASIA = 'orderkuota_mobile_app_2024';
    private const URL_API_DASAR = 'https://app.orderkuota.com/api/v2';
    private const TIMEOUT_SAMBUNG = 5;
    private const TIMEOUT_REQUEST = 15;
    private $tokenAuth;
    private $usernameAuth;
    private $idUser;
    private $idSesi;
    private $dataAcak;

    public function __construct() {
        $this->idSesi = bin2hex(random_bytes(16));
        $this->dataAcak = $this->generateDataAcak();
    }

    /**
     * Main entry point for request handling
     */
    public function tanganiRequest() {
        try {
            $input = $this->ambilJeungValidasiInput();
            $this->tokenAuth = $input['auth_token'];
            $this->usernameAuth = $input['auth_username'];
            $this->idUser = $this->parseIdUser($this->tokenAuth);
            $this->logRequest($input);
            $response = $this->ambilTiUpstream();
            $formatted = $this->olahResponse($response);
            $this->kirimResponse(200, $formatted);

        } catch (Exception $e) {
            $kode = $e->getCode() ?: 500;
            if ($kode < 100 || $kode > 599) $kode = 500;
            
            // Log error for debugging
            $this->logError($e);
            
            $this->kirimResponse($kode, [
                'error' => $e->getMessage(),
                'timestamp' => date('c'),
                'request_id' => $this->generateIdRequest()
            ]);
        }
    }

    private function ambilJeungValidasiInput() {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        if (!is_array($json)) {
            throw new Exception("Format JSON teu valid", 400);
        }

        $token = isset($json['auth_token']) ? trim($json['auth_token']) : '';
        $username = isset($json['auth_username']) ? trim($json['auth_username']) : '';

        if ($token === '' || $username === '') {
            throw new Exception("auth_token jeung auth_username kudu dieusian", 400);
        }

        return [
            'auth_token' => $token,
            'auth_username' => $username
        ];
    }

    private function parseIdUser($token) {
        $bagian = explode(':', $token, 2);
        if (count($bagian) < 2 || !ctype_digit($bagian[0])) {
            throw new Exception("Format auth_token teu valid, kudu jadi userId:token", 400);
        }
        return $bagian[0];
    }

    /**
     * Generate HMAC-SHA256 signature
     */
    private function generateSignature($params, $timestamp) {
        ksort($params);
        $stringData = http_build_query($params);
        $baseSignature = $stringData . '&timestamp=' . $timestamp . '&secret=' . self::KONCI_RAHASIA;
        return hash_hmac('sha256', $baseSignature, self::KONCI_RAHASIA);
    }

    /**
     * Generate a unique request ID
     */
    private function generateIdRequest() {
        return base_convert(time(), 10, 36) . '-' . bin2hex(random_bytes(8));
    }

    /**
     * Check whether an array is a list (indexed array)
     * Compatibility helper for PHP < 8.1
     */
    private function arrayNyaetaList($array) {
        if (!is_array($array)) return false;
        if (function_exists('array_is_list')) {
            return array_is_list($array);
        }
        return array_keys($array) === range(0, count($array) - 1);
    }

    private function ambilTiUpstream() {
        $url = self::URL_API_DASAR . '/qris/mutasi/' . rawurlencode($this->idUser);
        $payload = $this->buatPayload();
        $headers = $this->buatHeaders();

        $proxyUrl = getenv('PROXY_URL');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_ENCODING => '',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_COOKIEJAR => '/tmp/cookies_' . $this->idSesi . '.txt',
            CURLOPT_COOKIEFILE => '/tmp/cookies_' . $this->idSesi . '.txt',
            CURLOPT_TIMEOUT => self::TIMEOUT_REQUEST,
            CURLOPT_CONNECTTIMEOUT => self::TIMEOUT_SAMBUNG,
            CURLOPT_TCP_NODELAY => true,
            CURLOPT_TCP_KEEPALIVE => 1,
        ]);
        if (!empty($proxyUrl)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxyUrl);
        }

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->bereskeunFileCookie();

        if ($response === false) {
            $this->tanganiErrorCurl($errno, $error);
        }

        if ($httpCode >= 400) {
            $this->tanganiErrorHttp($httpCode, $response);
        }

        return $response;
    }

    /**
     * Clean up temporary cookie file
     */
    private function bereskeunFileCookie() {
        $fileCookie = '/tmp/cookies_' . $this->idSesi . '.txt';
        if (file_exists($fileCookie)) {
            @unlink($fileCookie);
        }
    }

    /**
     * Handle cURL errors
     */
    private function tanganiErrorCurl($errno, $error) {
        $pesan = "Sambungan gagal: $error";
        if ($errno === CURLE_OPERATION_TIMEOUTED) {
            $pesan = "Timeout sambungan ka server upstream";
        }
        throw new Exception($pesan, 504);
    }

    /**
     * Handle HTTP errors from upstream
     */
    private function tanganiErrorHttp($httpCode, $response) {
        $decoded = json_decode($response, true);
        $pesanUpstream = isset($decoded['message']) ? $decoded['message'] : "HTTP Error $httpCode";
        if ($httpCode == 429 || stripos($pesanUpstream, 'terlalu sering') !== false) {
            throw new Exception("Rate limit: $pesanUpstream", 429);
        }
        
        throw new Exception("Error Upstream: $pesanUpstream", $httpCode);
    }

    private function olahResponse($responseRaw) {
        $decoded = json_decode($responseRaw, true);
        if (!is_array($decoded)) {
            return ['raw_response' => $responseRaw];
        }

        if (isset($decoded['success']) && $decoded['success'] === false) {
            return $decoded;
        }

        $items = $this->ekstrakItemRiwayat($decoded);
        $itemFormatted = $this->formatItems($items);

        $merchantDasar = explode(':', $this->tokenAuth, 2)[0];

        return [
            'status' => true,
            'message' => 'Berhasil menampilkan mutasi',
            'donate' => 'https://raw.githubusercontent.com/AutoFTbot/AutoFTbot/refs/heads/main/qris.png',
            'merchant' => 'OK' . $merchantDasar,
            'data' => $itemFormatted
        ];
    }

    /**
     * Extract transaction history items from API response
     */
    private function ekstrakItemRiwayat($data) {
        if (!is_array($data)) return [];
        if ($this->arrayNyaetaList($data)) return $data;
        $kandidat = [
            ['qris_history', 'results'],
            ['history', 'results'],
            ['data', 'qris_history', 'results'],
            ['results'],
            ['data']
        ];

        foreach ($kandidat as $jalur) {
            $temp = $data;
            $kapanggih = true;
            
            foreach ($jalur as $konci) {
                if (isset($temp[$konci]) && is_array($temp[$konci])) {
                    $temp = $temp[$konci];
                } else {
                    $kapanggih = false;
                    break;
                }
            }
            
            if ($kapanggih && $this->arrayNyaetaList($temp)) {
                return $temp;
            }
        }
        return $this->nyariArrayDalam($data);
    }

    /**
     * Find array data recursively within nested structures
     */
    private function nyariArrayDalam($data) {
        if (!is_array($data)) return [];
        
        foreach ($data as $val) {
            if (is_array($val)) {
                if ($this->arrayNyaetaList($val)) return $val;
                $hasil = $this->nyariArrayDalam($val);
                if (!empty($hasil)) return $hasil;
            }
        }
        
        return [];
    }

    /**
     * Format transaction items into a standard structure
     */
    private function formatItems($items) {
        $hasil = [];
        
        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $itemFormatted = $this->formatItemTunggal($item);
            if ($itemFormatted) {
                $hasil[] = $itemFormatted;
            }
        }
        
        return $hasil;
    }

    /**
     * Format a single transaction item
     */
    private function formatItemTunggal($item) {
        $tanggalRaw = $this->ambilNilaiPertama($item, ['tanggal', 'date', 'created_at', 'waktu', 'time']);
        $tanggal = $this->normalisasiTanggal($tanggalRaw);
        $kredit = $this->ambilNilaiPertama($item, ['kredit', 'credit', 'amount_in']);
        $debit = $this->ambilNilaiPertama($item, ['debit', 'debet', 'amount_out']);
        $jumlahRaw = $this->ambilNilaiPertama($item, ['amount'], $kredit, $debit);
        $jumlah = $this->bereskeunAngka($jumlahRaw);
        $status = strtoupper((string)$this->ambilNilaiPertama($item, ['status'], $kredit ? 'IN' : 'OUT'));
        $tipe = ($status === 'IN') ? 'CR' : 'DB';

        return [
            'date' => $tanggal,
            'amount' => $jumlah,
            'type' => $tipe,
            'qris' => 'static',
            'brand_name' => $this->ambilNilaiPertama($item, ['brand.name', 'brand_name', 'brand'], ''),
            'issuer_reff' => (string)$this->ambilNilaiPertama($item, ['id', 'trxid', 'reference', 'ref'], ''),
            'buyer_reff' => $this->ambilNilaiPertama($item, ['keterangan', 'note', 'description', 'desc'], ''),
            'balance' => $this->bereskeunAngka($this->ambilNilaiPertama($item, ['saldo_akhir', 'balance', 'saldo'], '0'))
        ];
    }

    /**
     * Normalize date format from DD/MM/YYYY HH:MM to YYYY-MM-DD HH:MM
     */
    private function normalisasiTanggal($stringTanggal) {
        if (!$stringTanggal) return '';
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}:\d{2})$/', $stringTanggal, $cocok)) {
            return "{$cocok[3]}-{$cocok[2]}-{$cocok[1]} {$cocok[4]}";
        }
        
        return $stringTanggal;
    }

    /**
     * Clean numeric string, keep digits and minus sign only
     */
    private function bereskeunAngka($nilai) {
        return preg_replace('/[^0-9\-]/', '', (string)$nilai);
    }

    /**
     * Pick the first non-empty value using multiple possible keys
     */
    private function ambilNilaiPertama($array, $konci, ...$fallbacks) {
        if (is_array($konci)) {
            foreach ($konci as $k) {
                $nilai = $this->ambilNilaiKuKonci($array, $k);
                if ($nilai !== null && $nilai !== '') {
                    return $nilai;
                }
            }
        }
        
        foreach ($fallbacks as $fallback) {
            if ($fallback !== null && $fallback !== '') {
                return $fallback;
            }
        }
        
        return null;
    }

    /**
     * Get a value by key, supports dot notation
     */
    private function ambilNilaiKuKonci($array, $konci) {
        if (!is_array($array)) return null;
        
        // Supports dot notation (e.g., brand.name)
        if (strpos($konci, '.') !== false) {
            $bagian = explode('.', $konci);
            $nilai = $array;
            
            foreach ($bagian as $part) {
                if (is_array($nilai) && isset($nilai[$part])) {
                    $nilai = $nilai[$part];
                } else {
                    return null;
                }
            }
            
            return $nilai;
        }
        
        return isset($array[$konci]) ? $array[$konci] : null;
    }

    private function buatPayload() {
        $rd = $this->dataAcak;
        $waktuAyeuna = microtime(true);
        $jitter = rand(-100, 100) / 1000;
        $waktuRequest = (string) round(($waktuAyeuna + $jitter) * 1000);
        $idRequest = $this->generateIdRequest();

        // Base parameters for signature
        $paramDasar = [
            'auth_username' => $this->usernameAuth,
            'auth_token' => $this->tokenAuth,
            'phone_uuid' => $rd['uuid'],
            'request_time' => $waktuRequest
        ];

        // Generate signature
        $signature = $this->generateSignature($paramDasar, $waktuRequest);

        return [
            'auth_username' => $this->usernameAuth,
            'auth_token' => $this->tokenAuth,
            'app_reg_id' => $rd['fcm_token'],
            'phone_uuid' => $rd['uuid'],
            'phone_model' => $rd['phone_model'],
            'request_time' => $waktuRequest,
            'request_id' => $idRequest,
            'signature' => $signature,
            'phone_android_version' => $rd['android_version'],
            'app_version_code' => $rd['app_version_code'],
            'app_version_name' => $rd['app_version_name'],
            'ui_mode' => 'light',
            'requests[0]' => 'account',
            'requests[qris_history][keterangan]' => '',
            'requests[qris_history][jumlah]' => '',
            'requests[qris_history][page]' => '1',
            'requests[qris_history][dari_tanggal]' => '',
            'requests[qris_history][ke_tanggal]' => '',
            'device_info' => json_encode([
                'brand' => 'Samsung',
                'model' => $rd['phone_model'],
                'version' => $rd['android_version'],
                'sdk' => (int)$rd['android_version']
            ]),
            'app_info' => json_encode([
                'version_code' => $rd['app_version_code'],
                'version_name' => $rd['app_version_name'],
                'package' => 'com.orderkuota.app'
            ])
        ];
    }

    private function buatHeaders() {
        $rd = $this->dataAcak;
        $ip = rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
        
        return [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: ' . $this->ambilUserAgentAcak(),
            'Accept: */*',
            'Connection: keep-alive',
            'Cache-Control: no-cache',
            'X-Requested-With: XMLHttpRequest',
            'X-Platform: android',
            'X-App-Version: ' . $rd['app_version_name'],
            'X-Device-ID: ' . $rd['uuid'],
            'X-Forwarded-For: ' . $ip,
            'X-Real-IP: ' . $ip,
            'Cookie: session_id=' . $this->idSesi . '; device_id=' . $rd['uuid'],
            'Referer: https://app.orderkuota.com/',
            'Origin: https://app.orderkuota.com'
        ];
    }

    /**
     * Generate device data with deterministic approach
     */
    private function generateDataAcak() {
        $configDevice = $this->ambilConfigDevice();
        
        // Generate deterministic UUID based on session
        $uuid = $this->generateUuidDeterministik();
        
        // Generate FCM token
        $tokenFcm = $this->generateTokenFcm($uuid);

        // Session-based randomization for consistent device fingerprint
        $this->seedGeneratorAcak();

        $indexVersiApp = array_rand($configDevice['app_versions']);

        return [
            'uuid' => $uuid,
            'fcm_token' => $tokenFcm,
            'phone_model' => $configDevice['phone_models'][array_rand($configDevice['phone_models'])],
            'android_version' => $configDevice['android_versions'][array_rand($configDevice['android_versions'])],
            'app_version_code' => $configDevice['app_versions'][$indexVersiApp],
            'app_version_name' => $configDevice['app_version_names'][$indexVersiApp]
        ];
    }

    /**
     * Get device configuration array
     */
    private function ambilConfigDevice() {
        return [
            'android_versions' => ['10', '11', '12', '13', '14'],
            'phone_models' => [
                'SM-G973F', 'SM-G975F', 'SM-G988B', 'SM-A505F', 'SM-A515F', 
                'SM-A705F', 'SM-A715F', 'SM-A805F', 'SM-A905F', 'SM-N970F', 
                'SM-N975F', 'SM-N980F', 'SM-N985F', 'SM-N986B'
            ],
            'app_versions' => ['250918', '250920', '250925', '250930', '251005'],
            'app_version_names' => ['25.09.18', '25.09.20', '25.09.25', '25.09.30', '25.10.05']
        ];
    }

    /**
     * Generate deterministic UUID based on session
     */
    private function generateUuidDeterministik() {
        $hash = hash('sha256', $this->idSesi . 'uuid');
        return sprintf('%s-%s-%s-%s-%s', 
            substr($hash, 0, 8), substr($hash, 8, 4), substr($hash, 12, 4), 
            substr($hash, 16, 4), substr($hash, 20, 12)
        );
    }

    /**
     * Generate FCM token based on UUID
     */
    private function generateTokenFcm($uuid) {
        $hashFcm = hash('sha256', $this->idSesi . 'fcm');
        return $uuid . ':APA91b' . substr($hashFcm, 0, 100);
    }

    /**
     * Seed random generator for consistent results
     */
    private function seedGeneratorAcak() {
        $seed = hexdec(substr(hash('md5', $this->idSesi), 0, 8));
        srand($seed);
    }

    private function ambilUserAgentAcak() {
        $agents = [
            'okhttp/4.12.0', 'okhttp/4.11.0', 'okhttp/4.10.0', 
            'okhttp/4.9.3', 'okhttp/4.9.2'
        ];
        return $agents[array_rand($agents)];
    }

    /**
     * Log request for debugging (optional)
     */
    private function logRequest($input) {
        if (defined('MODE_DEBUG') && MODE_DEBUG) {
            error_log("Mutasi API Request: " . json_encode([
                'username' => $input['auth_username'],
                'user_id' => $this->idUser,
                'timestamp' => date('c'),
                'session_id' => $this->idSesi
            ]));
        }
    }

    /**
     * Log error for debugging
     */
    private function logError($exception) {
        error_log("Mutasi API Error: " . $exception->getMessage() . " di " . $exception->getFile() . ":" . $exception->getLine());
    }

    private function kirimResponse($kode, $data) {
        http_response_code($kode);
        echo json_encode($data);
        exit;
    }
}

$api = new ApiMutasi();
$api->tanganiRequest();
