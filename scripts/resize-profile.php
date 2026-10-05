<?php

// Reproduce the web variants from the owner's supplied original photograph.
// No retouching: a centered square crop, then GD resizing/encoding only.
if ($argc !== 2 || ! is_file($argv[1])) {
    fwrite(STDERR, "Usage: php scripts/resize-profile.php <original.jpeg>\n");
    exit(1);
}
$original = imagecreatefromjpeg($argv[1]);
$width = imagesx($original);
$height = imagesy($original);
$side = min($width, $height);
foreach ([320, 480, 720] as $size) {
    $image = imagecreatetruecolor($size, $size);
    imagecopyresampled($image, $original, 0, 0, (int) (($width - $side) / 2), (int) (($height - $side) / 2), $size, $size, $side, $side);
    imagewebp($image, __DIR__."/../public/icons/profile-2026-{$size}.webp", 84);
    if ($size === 720) {
        imagejpeg($image, __DIR__.'/../public/icons/profile-2026-720.jpg', 88);
    }
    imagedestroy($image);
}
imagedestroy($original);
echo "Profile variants created.\n";
