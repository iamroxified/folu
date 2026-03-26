<?php ob_start(); session_start(); 


// Create a blank image
$width = 120;
$height = 40;
$image = imagecreate($width, $height);

// Set background and text colors
$background_color = imagecolorallocate($image, 255, 255, 255); // White background
$text_color = imagecolorallocate($image, 0, 0, 0); // Black text

// Generate random text for the CAPTCHA
$characters = '0123456789';
$captcha_text = substr(str_shuffle($characters), 0, 6);

// Store the CAPTCHA text in the session
$_SESSION['captcha'] = $captcha_text;

// Add text to the image
$font_size = 20;
$font = __DIR__ . '/assets/css/captcha.ttf'; // Use a TTF font for better security
imagettftext($image, $font_size, 0, 10, 30, $text_color, $font, $captcha_text);

// Add noise (optional)
for ($i = 0; $i < 50; $i++) {
    $noise_color = imagecolorallocate($image, rand(100, 255), rand(100, 255), rand(100, 255));
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// Output the image as a PNG
header('Content-Type: image/png');
imagepng($image);

// Free up memory
imagedestroy($image);
?>