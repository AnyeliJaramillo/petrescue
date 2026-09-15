<?php

return [
    // Laravel renderiza la V de MVC desde la carpeta física del frontend.
    'paths' => [dirname(__DIR__, 2).'/Frontend/resources/views'],

    'compiled' => env('VIEW_COMPILED_PATH', realpath(storage_path('framework/views'))),
];
