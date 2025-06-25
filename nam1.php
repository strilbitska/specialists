<?php
require_once '../config/database.php';

function uploadFile($file, $specialist_id, $type) {
    $target_dir = "../uploads/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Проверка типа файла
    $allowed_images = ["jpg", "jpeg", "png", "gif"];
    $allowed_videos = ["mp4", "webm"];

    if ($type === 'image' && !in_array($file_extension, $allowed_images)) {
        return ["error" => "Разрешены только изображения"];
    }
    if ($type === 'video' && !in_array($file_extension, $allowed_videos)) {
        return ["error" => "Разрешены только видео MP4 и WEBM"];
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "url" => "uploads/" . $new_filename];
    }
    return ["error" => "Ошибка при загрузке файла"];
}
?>