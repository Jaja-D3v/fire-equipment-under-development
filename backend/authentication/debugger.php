<?php
session_start();


$pass = md5("ADMIN001");
echo $pass;



// $timeout = 300; // 5 minutes ceiling

// if (isset($_SESSION['LAST_ACTIVITY'])) {
//     // 1. Sukatin kung ilang segundo na ang nakalipas mula sa huling activity
//     $elapsedTime = time() - $_SESSION['LAST_ACTIVITY'];

//     // 2. I-calculate ang natitirang segundo
//     $remainingSeconds = max(0, $timeout - $elapsedTime);

//     // 3. I-convert sa minutes at seconds
//     $minutesLeft = floor($remainingSeconds / 60);
//     $secondsLeft = $remainingSeconds % 60;

//     // 4. I-echo ang natitirang oras
//     echo "Natitirang oras sa session: {$minutesLeft}m {$secondsLeft}s";
// } else {
//     echo "Walang active session activity.";
// }

