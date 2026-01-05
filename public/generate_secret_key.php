<?php
$secretKey = bin2hex(random_bytes(32)); // Generates a 64-character hexadecimal key
echo $secretKey;
