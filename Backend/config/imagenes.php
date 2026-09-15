<?php

return [
    'almacen' => env('IMAGENES_ALMACEN', 'local'),
    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'key' => env('SUPABASE_SECRET_KEY', env('SUPABASE_SERVICE_ROLE_KEY')),
        'bucket' => env('SUPABASE_STORAGE_BUCKET', 'mascotas'),
    ],
];
