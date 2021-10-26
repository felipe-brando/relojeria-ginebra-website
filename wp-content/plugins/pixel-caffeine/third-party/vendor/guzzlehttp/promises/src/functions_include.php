<?php

namespace PixelCaffeine\Dependencies;

// Don't redefine the functions if included multiple times.
if (!\function_exists('PixelCaffeine\\Dependencies\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
