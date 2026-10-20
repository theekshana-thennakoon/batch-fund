<?php
// Sinhala TTS Speech Endpoint & Audio Cache
$balance = isset($_GET['balance']) ? (int)$_GET['balance'] : 0;

$cacheDir = __DIR__ . '/assets/audio';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

$isMale = false;
if ($balance <= 0) {
    $filename = $cacheDir . '/fully_paid_sinhala.mp3';
    $text = "ඔයා ඔක්කොම ගෙවලා ඉවරයි. ආදරෙයි";
} else {
    $text = "තව " . $balance . "යි බං තියෙන්නේ. ඕක දීල ඉවර කරපන් බං.";
    $filename = $cacheDir . '/due_' . $balance . '_male_' . substr(md5($text), 0, 8) . '.mp3';
    $rawFilename = $cacheDir . '/due_' . $balance . '_' . substr(md5($text), 0, 8) . '.mp3';
    $isMale = true;
}

// 1. Serve from male/target cache if available
if (file_exists($filename) && filesize($filename) > 0) {
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . filesize($filename));
    header('Cache-Control: public, max-age=86400');
    readfile($filename);
    exit;
}

// 2. If raw cache exists, convert it directly using ffmpeg
if ($isMale && isset($rawFilename) && file_exists($rawFilename) && filesize($rawFilename) > 0) {
    $cmd = 'ffmpeg -y -i ' . escapeshellarg($rawFilename) . ' -af "rubberband=pitch=0.76" ' . escapeshellarg($filename) . ' 2>&1';
    @exec($cmd, $out, $ret);
    if ($ret !== 0 || !file_exists($filename) || filesize($filename) === 0) {
        $cmdFallback = 'ffmpeg -y -i ' . escapeshellarg($rawFilename) . ' -af "asetrate=24000*0.78,atempo=1.28" ' . escapeshellarg($filename) . ' 2>&1';
        @exec($cmdFallback, $out2, $ret2);
    }
    if (file_exists($filename) && filesize($filename) > 0) {
        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . filesize($filename));
        header('Cache-Control: public, max-age=86400');
        readfile($filename);
        exit;
    }
}

// 3. Fetch from Google TTS service and cache locally
$url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=si&client=tw-ob&q=" . urlencode($text);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($ch, CURLOPT_REFERER, "https://translate.google.com/");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 18);
$audioData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && !empty($audioData)) {
    if ($isMale) {
        if (isset($rawFilename)) {
            file_put_contents($rawFilename, $audioData);
        }
        $tempRaw = $filename . '.raw.mp3';
        file_put_contents($tempRaw, $audioData);

        // Convert to deep male voice using ffmpeg pitch shift (rubberband)
        $cmd = 'ffmpeg -y -i ' . escapeshellarg($tempRaw) . ' -af "rubberband=pitch=0.76" ' . escapeshellarg($filename) . ' 2>&1';
        @exec($cmd, $out, $ret);

        if ($ret !== 0 || !file_exists($filename) || filesize($filename) === 0) {
            $cmdFallback = 'ffmpeg -y -i ' . escapeshellarg($tempRaw) . ' -af "asetrate=24000*0.78,atempo=1.28" ' . escapeshellarg($filename) . ' 2>&1';
            @exec($cmdFallback, $out2, $ret2);
        }

        if (file_exists($tempRaw)) {
            @unlink($tempRaw);
        }

        if (file_exists($filename) && filesize($filename) > 0) {
            $audioData = file_get_contents($filename);
        } else {
            file_put_contents($filename, $audioData);
        }
    } else {
        file_put_contents($filename, $audioData);
    }

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
