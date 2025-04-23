<?php
/**
 * Hava Durumu API Yapılandırma Dosyası
 */

// OpenWeatherMap API anahtarı
define('WEATHER_API_KEY', ''); // Kendi API anahtarınızla değiştirin

define('DEFAULT_CITY', 'Cyprus');
// Birim (metric: Celsius, imperial: Fahrenheit)
define('WEATHER_UNIT', 'metric');

// Hava durumu dili (tr: Türkçe, en: İngilizce, vb.)
define('WEATHER_LANG', 'tr');

// Hava durumu güncellemesinin varsayılan sıklığı (dakika cinsinden)
define('WEATHER_UPDATE_INTERVAL', 60);

// Hava durumu efektleri aktif/pasif ayarı
define('WEATHER_EFFECTS_ENABLED', true);
