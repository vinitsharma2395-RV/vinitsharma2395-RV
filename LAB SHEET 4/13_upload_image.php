<?php
require __DIR__ . '/common.php';

pageStart('13. Validated image upload');
?>
<section>
    <p>Allowed types: JPG, JPEG, PNG, GIF, and WEBP. Maximum size: 2 MB. The upload directory is created in this lab folder.</p>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            requireCsrf();
            if (
                !isset($_FILES['image']) ||
                !is_array($_FILES['image']) ||
                !isset($_FILES['image']['error'], $_FILES['image']['size'], $_FILES['image']['tmp_name'], $_FILES['image']['name']) ||
                !is_string($_FILES['image']['tmp_name']) ||
                !is_string($_FILES['image']['name'])
            ) {
                throw new InvalidArgumentException('Choose one image to upload.');
            }
            $file = $_FILES['image'];
            if ((int) $file['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('The upload failed with PHP error code ' . (int) $file['error'] . '.');
            }
            if ((int) $file['size'] < 1 || (int) $file['size'] > 2097152 || !is_uploaded_file($file['tmp_name'])) {
                throw new InvalidArgumentException('The uploaded file must be between 1 byte and 2 MB.');
            }
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $types = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ];
            if (!isset($types[$extension])) {
                throw new InvalidArgumentException('Use a JPG, JPEG, PNG, GIF, or WEBP image.');
            }
            if (!extension_loaded('fileinfo')) {
                throw new RuntimeException('Enable the PHP fileinfo extension to validate uploaded image content.');
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if ($finfo->file($file['tmp_name']) !== $types[$extension]) {
                throw new InvalidArgumentException('File contents do not match the selected image extension.');
            }
            $directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
                throw new RuntimeException('Could not create the uploads directory.');
            }
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
            if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
                throw new RuntimeException('Could not store the uploaded image.');
            }
            echo '<p class="notice">Image stored as <code>uploads/' . e($filename) . '</code>.</p>';
        } catch (RuntimeException | InvalidArgumentException $exception) {
            showError($exception);
        }
    }
    ?>
    <form method="post" enctype="multipart/form-data">
        <?php csrfField(); ?>
        <label>Choose image <input type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" required></label>
        <button type="submit">Upload image</button>
    </form>
</section>
<?php pageEnd(); ?>
