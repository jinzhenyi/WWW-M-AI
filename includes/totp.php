<?php
function verifyTOTP($base32Secret, $code, $timeStep = 30, $digits = 6) {
    $secretBin = base32_decode($base32Secret);
    $timeSlice = floor(time() / $timeStep);
    // 允许前后各一个时间窗口，防止时钟偏差
    for ($i = -1; $i <= 1; $i++) {
        if (generateTOTP($secretBin, $timeSlice + $i, $digits) === $code) {
            return true;
        }
    }
    return false;
}

function generateTOTP($secretBin, $timeSlice, $digits) {
    // 将时间片转为 8 字节大端无符号整数（RFC 6238）
    $timeSliceBin = pack('J', $timeSlice); // PHP 64bit
    if (strlen($timeSliceBin) != 8) {
        // 兼容 32 位系统手动构造
        $high = floor($timeSlice / pow(2, 32));
        $low  = $timeSlice % pow(2, 32);
        $timeSliceBin = pack('NN', $high, $low);
    }
    $hash = hash_hmac('sha1', $timeSliceBin, $secretBin, true);
    $offset = ord(substr($hash, -1)) & 0x0F;
    $binary = (ord($hash[$offset]) & 0x7F) << 24
            | (ord($hash[$offset+1]) & 0xFF) << 16
            | (ord($hash[$offset+2]) & 0xFF) << 8
            | (ord($hash[$offset+3]) & 0xFF);
    $otp = $binary % pow(10, $digits);
    return str_pad((string)$otp, $digits, '0', STR_PAD_LEFT);
}

function base32_decode($base32) {
    static $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $base32 = strtoupper($base32);
    $bits = '';
    for ($i = 0; $i < strlen($base32); $i++) {
        $pos = strpos($alphabet, $base32[$i]);
        if ($pos === false) continue;
        $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }
    $bytes = '';
    for ($i = 0; $i < strlen($bits); $i += 8) {
        $chunk = substr($bits, $i, 8);
        if (strlen($chunk) < 8) break;
        $bytes .= chr(bindec($chunk));
    }
    return $bytes;
}