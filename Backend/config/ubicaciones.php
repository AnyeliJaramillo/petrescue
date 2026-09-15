<?php

return [
    'geoapify_key' => env('GEOAPIFY_API_KEY'),
    'consultas_por_minuto' => (int) env('GEOCODING_REQUESTS_PER_MINUTE', 60),
    'tiles_url' => env('MAP_TILES_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
    'tiles_attribution' => env('MAP_TILES_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'),
];
