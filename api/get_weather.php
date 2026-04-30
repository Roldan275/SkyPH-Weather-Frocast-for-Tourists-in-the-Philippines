<?php
header("Content-Type: application/json");

$apiKey = "18c04ee094db953a6ae199c79a8e95f5";

if (empty($_GET['lat']) || empty($_GET['lon'])) {
    echo json_encode(["error" => "Missing coordinates"]);
    exit;
}

$lat = $_GET['lat'];
$lon = $_GET['lon'];

/* FETCH FUNCTION */
function fetchData($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

// 1. 5-Day Forecast for basic structure
$forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";
// 2. One Call 3.0 for precise metrics (UV, Visibility, Dew Point)
$oneCallUrl = "https://api.openweathermap.org/data/3.0/onecall?lat={$lat}&lon={$lon}&exclude=minutely,hourly&units=metric&appid={$apiKey}";

$forecastData = fetchData($forecastUrl);
$oneCallData = fetchData($oneCallUrl);

/* ERROR CHECK */
if (!$forecastData || $forecastData['cod'] !== "200" || !$oneCallData) {
    echo json_encode(["error" => "Weather data retrieval failed"]);
    exit;
}

$dailyForecast = [];
$dayIndex = 0;

foreach ($forecastData['list'] as $entry) {
    // Only grab data for noon each day
    if (strpos($entry['dt_txt'], '12:00:00') !== false) {
        
        // Match this day with the corresponding index in One Call 'daily' array
        $extra = $oneCallData['daily'][$dayIndex] ?? [];

        $dailyForecast[] = [
            "day" => date("D", $entry['dt']),
            "temp" => round($entry['main']['temp']),
            "condition" => ucfirst($entry['weather'][0]['description']),
            "humidity" => $entry['main']['humidity'],
            "pressure" => $entry['main']['pressure'],
            "wind" => $entry['wind']['speed'],
            "icon" => $entry['weather'][0]['icon'],
            
            // DYNAMIC DATA FIX: Pulling directly from One Call API
            "visibility" => $oneCallData['current']['visibility'] ?? 10000, // API provides meters
            "uvi" => $extra['uvi'] ?? 0,
            "dew_point" => round($extra['dew_point'] ?? 0)
        ];

        $dayIndex++;
        if ($dayIndex >= 5) break;
    }
}

echo json_encode([
    "five_day" => $dailyForecast
]);