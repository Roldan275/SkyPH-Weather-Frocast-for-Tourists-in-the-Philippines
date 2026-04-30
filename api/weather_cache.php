<?php
include "db.php";
include "weather_api.php";

function getWeather($conn, $tourist_spot_id, $city) {

    // 1. CHECK DATABASE FIRST
    $sql = "SELECT * FROM weather_forecasts 
            WHERE tourist_spot_id = $tourist_spot_id 
            ORDER BY updated_at DESC 
            LIMIT 1";

    $result = $conn->query($sql);
    $cached = $result->fetch_assoc();

    $now = time();

    // 2. CHECK IF CACHE IS STILL FRESH (1 hour = 3600 sec)
    if ($cached && (strtotime($cached['updated_at']) + 3600 > $now)) {
        return $cached;
    }

    // 3. FETCH NEW DATA FROM API
    $apiData = fetchWeatherFromAPI($city);

    if (!$apiData) {
        return $cached; // fallback to old data
    }

    $temp = $apiData['main']['temp'];
    $humidity = $apiData['main']['humidity'];
    $condition = $apiData['weather'][0]['description'];

    // 4. SAVE / UPDATE DATABASE
    if ($cached) {
        $sql = "UPDATE weather_forecasts SET
                temperature = $temp,
                humidity = $humidity,
                weather_condition = '$condition',
                updated_at = NOW()
                WHERE id = " . $cached['id'];
    } else {
        $sql = "INSERT INTO weather_forecasts 
                (tourist_spot_id, temperature, humidity, weather_condition, created_at, updated_at)
                VALUES 
                ($tourist_spot_id, $temp, $humidity, '$condition', NOW(), NOW())";
    }

    $conn->query($sql);

    // 5. RETURN LATEST DATA
    return [
        "temperature" => $temp,
        "humidity" => $humidity,
        "weather_condition" => $condition
    ];
}
?>