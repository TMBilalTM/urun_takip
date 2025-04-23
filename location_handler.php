<?php
require_once 'config/config.php';
require_once 'config/weather_config.php';

// Konum alma ve işleme sınıfı
class LocationHandler {
    private $lat;
    private $lon;
    
    public function __construct($lat = null, $lon = null) {
        $this->lat = $lat;
        $this->lon = $lon;
    }
    
    /**
     * Enlem ve boylam değerlerine göre konumu algıla
     */
    public function getLocationInfo() {
        if (!$this->lat || !$this->lon) {
            return null;
        }
        
        // OpenCage API'yi kullan (ücretsiz bir geocoding API)
        $api_key = 'YOUR_OPENCAGE_API_KEY'; // OpenCage API anahtarını buraya ekleyin
        $url = "https://api.opencagedata.com/geocode/v1/json?key={$api_key}&q={$this->lat}+{$this->lon}&pretty=1&no_annotations=1";
        
        try {
            $response = @file_get_contents($url);
            if ($response === false) {
                // API ile bağlantı kurulamadı
                return $this->getFallbackLocation();
            }
            
            $data = json_decode($response, true);
            if (empty($data) || $data['status']['code'] != 200 || empty($data['results'])) {
                return $this->getFallbackLocation();
            }
            
            $result = $data['results'][0];
            $components = $result['components'];
            
            return [
                'city' => isset($components['city']) ? $components['city'] : 
                       (isset($components['town']) ? $components['town'] : 
                       (isset($components['village']) ? $components['village'] : 
                       (isset($components['county']) ? $components['county'] : 'Bilinmiyor'))),
                'country' => isset($components['country']) ? $components['country'] : 'Bilinmiyor',
                'country_code' => isset($components['country_code']) ? strtoupper($components['country_code']) : '',
                'lat' => $this->lat,
                'lon' => $this->lon,
                'timezone' => isset($result['annotations']['timezone']['name']) ? $result['annotations']['timezone']['name'] : null
            ];
        } catch (Exception $e) {
            error_log("Konum tespit hatası: " . $e->getMessage());
            return $this->getFallbackLocation();
        }
    }
    
    /**
     * API ile konum algılanamazsa varsayılan konum bilgisi
     */
    private function getFallbackLocation() {
        return [
            'city' => DEFAULT_CITY,
            'country' => 'Türkiye',
            'country_code' => 'TR',
            'lat' => $this->lat,
            'lon' => $this->lon,
            'timezone' => 'Europe/Istanbul'
        ];
    }
}

// POST isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Session başlatılmadıysa başlat
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'save_location' && isset($_POST['lat']) && isset($_POST['lon'])) {
        $lat = floatval($_POST['lat']);
        $lon = floatval($_POST['lon']);
        
        $handler = new LocationHandler($lat, $lon);
        $location = $handler->getLocationInfo();
        
        if ($location) {
            $_SESSION['user_location'] = $location;
            echo json_encode(['status' => 'success', 'message' => 'Konum başarıyla kaydedildi.', 'data' => $location]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Konum tespit edilemedi.']);
        }
        exit;
    }
}

// GET isteği ile test edebilmek için
if (isset($_GET['test'])) {
    $handler = new LocationHandler(39.7837, 30.5206); // Eskişehir'in koordinatları
    $location = $handler->getLocationInfo();
    echo '<pre>';
    print_r($location);
    echo '</pre>';
}
