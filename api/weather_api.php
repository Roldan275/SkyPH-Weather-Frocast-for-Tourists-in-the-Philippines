<?php
include_once "config.php";

function getWeatherByCity($city) {

    $url = "https://api.openweathermap.org/data/2.5/weather?q="
        . urlencode($city)
        . "&units=metric&appid=" . WEATHER_API_KEY;

    $response = @file_get_contents($url);

    if ($response === FALSE) {
        return null;
    }

    return json_decode($response, true);
}
?>