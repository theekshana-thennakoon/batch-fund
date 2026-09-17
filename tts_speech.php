<?php
// Sinhala TTS Speech Endpoint & Audio Cache
$balance = isset($_GET['balance']) ? (int)$_GET['balance'] : 0;

$cacheDir = __DIR__ . '/assets/audio';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

if ($balance <= 0) {
    $filename = $cacheDir . '/fully_paid_sinhala.mp3';
    $text = "ඔයා ඔක්කොම ගෙවලා ඉවරයි. ආදරෙයි";
} else {
    $filename = $cacheDir . '/due_' . $balance . '.mp3';
    $text = "ඔයා රුපියක් " . $balance . "ක් ගෙවන්න තියෙනවා. ඒක ගෙවලා ඉවර කරන්නකෝ";
}

// Serve from cache if available
if (file_exists($filename) && filesize($filename) > 0) {
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . filesize($filename));
    header('Cache-Control: public, max-age=86400');
    readfile($filename);
    exit;
}

// Fetch from TTS service and cache locally
$url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=si&client=tw-ob&q=" . urlencode($text);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
$audioData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && !empty($audioData)) {
    file_put_contents($filename, $audioData);
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . strlen($audioData));
    header('Cache-Control: public, max-age=86400');
    echo $audioData;
    exit;
} else {
    http_response_code(500);
    echo "Failed to generate TTS audio";
    exit;
}
