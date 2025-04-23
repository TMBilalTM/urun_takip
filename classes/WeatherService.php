<?php
/**
 * Hava Durumu Servisi
 * 
 * OpenWeatherMap API ile hava durumu verilerini getiren sınıf
 */
class WeatherService {
    private $apiKey;
    private $defaultCity;
    private $unit;
    private $language;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Config dosyasını dahil et (eğer henüz dahil edilmediyse)
        if (!defined('WEATHER_API_KEY')) {
            require_once __DIR__ . '/../config/weather_config.php';
        }
        
        // Yapılandırma değerlerini al
        $this->apiKey = defined('WEATHER_API_KEY') ? WEATHER_API_KEY : 'YOUR_API_KEY';
        $this->defaultCity = defined('DEFAULT_CITY') ? DEFAULT_CITY : 'Istanbul';
        $this->unit = defined('WEATHER_UNIT') ? WEATHER_UNIT : 'metric';
        $this->language = defined('WEATHER_LANG') ? WEATHER_LANG : 'tr';
    }
    
    /**
     * Hava durumu verilerini getir
     * 
     * @param string $city Hava durumu bilgileri alınacak şehir
     * @return array|null Hava durumu verileri veya bağlantı hatası durumunda null
     */
    public function getWeatherData($city = null) {
        if (empty($this->apiKey) || $this->apiKey == 'YOUR_API_KEY') {
            return null;
        }
        
        $city = $city ?? $this->defaultCity;
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$city},tr&units={$this->unit}&lang={$this->language}&appid={$this->apiKey}";
        
        try {
            // CURL ile API'ye bağlan
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $response = curl_exec($ch);
                curl_close($ch);
                
                if ($response) {
                    $data = json_decode($response, true);
                    
                    if (isset($data['cod']) && $data['cod'] == 200) {
                        return $this->formatWeatherData($data);
                    }
                }
            }
            
            // CURL yoksa file_get_contents() ile dene
            else if (ini_get('allow_url_fopen')) {
                $response = @file_get_contents($url);
                
                if ($response) {
                    $data = json_decode($response, true);
                    
                    if (isset($data['cod']) && $data['cod'] == 200) {
                        return $this->formatWeatherData($data);
                    }
                }
            }
        } catch (Exception $e) {
            // Hata durumunda null döndür
            return null;
        }
        
        return null;
    }
    
    /**
     * Hava durumu verilerini formatla ve sadece gerekli bilgileri döndür
     * 
     * @param array $data API'den gelen ham veriler
     * @return array Formatlanmış hava durumu verileri
     */
    private function formatWeatherData($data) {
        $result = [
            'description' => ucfirst($data['weather'][0]['description'] ?? ''),
            'icon_code' => $data['weather'][0]['icon'] ?? '',
            'icon_class' => $this->getIconClass($data['weather'][0]['icon'] ?? ''),
            'temperature' => round($data['main']['temp'] ?? 0),
            'feels_like' => round($data['main']['feels_like'] ?? 0),
            'humidity' => $data['main']['humidity'] ?? 0,
            'wind_speed' => $data['wind']['speed'] ?? 0,
            'city_name' => $data['name'] ?? $this->defaultCity,
            'country' => $data['sys']['country'] ?? 'TR',
            'timestamp' => $data['dt'] ?? time(),
        ];
        
        return $result;
    }
    
    /**
     * API ikon koduna göre Font Awesome sınıfını belirle
     * 
     * @param string $iconCode API'den gelen hava durumu ikon kodu
     * @return string Font Awesome ikon sınıfı
     */
    private function getIconClass($iconCode) {
        switch (substr($iconCode, 0, 2)) {
            case '01': return 'fas fa-sun text-warning'; // güneşli
            case '02': return 'fas fa-cloud-sun text-info'; // parçalı bulutlu
            case '03': 
            case '04': return 'fas fa-cloud text-secondary'; // bulutlu
            case '09':
            case '10': return 'fas fa-cloud-rain text-info'; // yağmurlu
            case '11': return 'fas fa-bolt text-warning'; // fırtınalı
            case '13': return 'fas fa-snowflake text-info'; // karlı
            case '50': return 'fas fa-smog text-secondary'; // sisli
            default: return 'fas fa-cloud text-secondary'; // varsayılan
        }
    }
}
