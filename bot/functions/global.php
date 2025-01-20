<?php


function adminm($text){
    //send message to admin:
    bot('SendMessage', ['chat_id' => ADMIN_ID, 'text' => $text]);
}


// Encrypt using XOR and Base64
function encrypt($text) {
    $encrypted = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $encrypted .= chr(ord($text[$i]) ^ ord(ENCRYPTION_KEY[$i % strlen(ENCRYPTION_KEY)]));
    }
    return base64_encode($encrypted);
}

// Decrypt using XOR and Base64
function decrypt($encryptedText) {
    $encrypted = base64_decode($encryptedText);
    $decrypted = '';
    for ($i = 0; $i < strlen($encrypted); $i++) {
        $decrypted .= chr(ord($encrypted[$i]) ^ ord(ENCRYPTION_KEY[$i % strlen(ENCRYPTION_KEY)]));
    }
    return $decrypted;
}
