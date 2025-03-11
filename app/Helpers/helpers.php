<?php
if (!function_exists('capitalizeWords')) {
    function capitalizeWords($string) {
        return ucwords(strtolower($string));
    }
}

