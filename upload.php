<?php
header('Content-Type: application/json');

// تأكد من وجود مجلد uploads
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// السماح بجميع أنواع الملفات (يمكنك تحديد أنواع معينة إذا أردت)
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
$maxFileSize = 10 * 1024 * 1024; // 10MB

$response = ['success' => false, 'message' => '', 'fileUrl' => ''];

try {
    if (!isset($_FILES['file']) || !isset($_POST['patient_name']) || !isset($_POST['phone'])) {
        throw new Exception('بيانات غير مكتملة');
    }

    $file = $_FILES['file'];
    $patientName = $_POST['patient_name'];
    $phone = $_POST['phone'];

    // التحقق من عدم وجود أخطاء في الرفع
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('حدث خطأ أثناء رفع الملف');
    }

    // التحقق من نوع الملف
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('نوع الملف غير مسموح به');
    }

    // التحقق من حجم الملف
    if ($file['size'] > $maxFileSize) {
        throw new Exception('حجم الملف كبير جداً (الحد الأقصى 10MB)');
    }

    // إنشاء اسم فريد للملف
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'file_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
    $filePath = 'uploads/' . $fileName;

    // نقل الملف إلى مجلد uploads
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('فشل في حفظ الملف');
    }

    // حفظ بيانات الملف في قاعدة البيانات (هنا نستخدم ملف JSON كمثال)
    $filesData = [];
    if (file_exists('files_data.json')) {
        $filesData = json_decode(file_get_contents('files_data.json'), true);
    }

    $filesData[] = [
        'name' => $file['name'],
        'path' => $filePath,
        'url' => 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $filePath,
        'patient' => $patientName,
        'phone' => $phone,
        'uploaded_at' => date('Y-m-d H:i:s')
    ];

    file_put_contents('files_data.json', json_encode($filesData));

    $response['success'] = true;
    $response['message'] = 'تم رفع الملف بنجاح';
    $response['fileUrl'] = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $filePath;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
