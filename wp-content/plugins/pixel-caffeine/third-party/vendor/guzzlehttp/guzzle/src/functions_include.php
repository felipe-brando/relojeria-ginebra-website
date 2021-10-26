<?php

namespace PixelCaffeine\Dependencies;

// Don't redefine the functions if included multiple times.
if (!\function_exists('PixelCaffeine\\Dependencies\\GuzzleHttp\\uri_template')) {
    require __DIR__ . '/functions.php';
}
