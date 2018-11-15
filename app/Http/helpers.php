<?php
// custom date format
function formatDate(Carbon\Carbon $date) {
    return $date->format('d.m.Y / H:i');
}

// excerpt
function excerpt($text, $maxChars) {
    return strlen($text) <= $maxChars ?
           $text :
           substr($text, 0, $maxChars) . "..";
}