<?php

require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseException;
use Parse\ParseFile;
use Parse\ParseQuery;
use Parse\ParseUser;

session_start();

$currUser = ParseUser::getCurrentUser();
if ($currUser) {
    $_SESSION['token'] = $currUser->getSessionToken();
} else {
    header('Refresh:0; url=../index.php');
}

$giftCategories = ['love', 'moods', 'artists', 'collectibles', 'games', 'family', 'classic', '3d', 'vip', 'country', 'festival', 'trending'];
$giftId = $_GET['objectId'] ?? '';
$gift = null;
$giftError = '';
$giftSuccess = '';

if ($giftId !== '') {
    try {
        $query = new ParseQuery('Gifts');
        $gift = $query->get($giftId, true);
    } catch (ParseException $e) {
        $giftError = $e->getMessage();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update_gift' && $gift) {
    $name = trim($_POST['gift_name'] ?? '');
    $category = trim($_POST['gift_category'] ?? '');
    $coins = (int)($_POST['gift_credits'] ?? 0);
    $removeFile = isset($_POST['remove_file']);

    if ($name === '' || $category === '' || $coins <= 0) {
        $giftError = 'Please provide valid gift details before updating.';
    } else {
        try {
            $gift->set('name', $name);
            $gift->set('categories', $category);
            $gift->set('coins', $coins);

            if ($removeFile) {
                $gift->set('file', null);
            }

            if (isset($_FILES['gift_file']) && !empty($_FILES['gift_file']['tmp_name']) && is_uploaded_file($_FILES['gift_file']['tmp_name'])) {
                $originalName = $_FILES['gift_file']['name'] ?? 'gift_file';
                $safeBaseName = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $safeExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $safeFileName = ($safeBaseName !== '' ? $safeBaseName : 'gift_file') . ($safeExtension !== '' ? '.' . $safeExtension : '');
                $gift->set('file', ParseFile::createFromFile($_FILES['gift_file']['tmp_name'], $safeFileName));
            }

            $gift->save(true);
            $giftSuccess = 'Gift updated successfully.';
        } catch (ParseException $e) {
            $giftError = $e->getMessage();
        }
    }
}

$typeFile = $gift ? $gift->get('file') : null;
$fileUrl = '';
if ($typeFile !== null && is_object($typeFile) && method_exists($typeFile, 'getURL')) {
    $fileUrl = $typeFile->getURL();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/dashboard/images/favicon.png">
    <title><?php echo $app_name; ?> - Edit Gift</title>
    <link href="../assets/dashboard/css/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/dashboard/css/helper.css" rel="stylesheet">
    <link href="../assets/dashboard/css/style.css" rel="stylesheet">
    <link href="../assets/dashboard/css/aliki.css" rel="stylesheet">
</head>
<body class="fix-header fix-sidebar">
<div id="main-wrapper">
    <?php include '../admin/header_admin.php'; ?>
    <?php include '../admin/left_sidebar_admin.php'; ?>

    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard/gift.php">Features</a></li>
                    <li class="breadcrumb-item active">Edit gift</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-10 col-xl-8" style="margin-left:auto; margin-right:auto; padding:10px 30px;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-4">Edit Gift</h4>

                            <?php if (!empty($giftError)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($giftError); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($giftSuccess)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($giftSuccess); ?></div>
                            <?php endif; ?>

                            <?php if (!$gift): ?>
                                <div class="alert alert-warning">Gift not found.</div>
                            <?php else: ?>
                                <form method="post" enctype="multipart/form-data" action="">
                                    <input type="hidden" name="action" value="update_gift">

                                    <div class="form-group">
                                        <label for="gift_name">Gift Name</label>
                                        <input type="text" id="gift_name" name="gift_name" class="form-control" value="<?php echo htmlspecialchars($gift->get('name') ?? ''); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="gift_category">Category</label>
                                        <select id="gift_category" name="gift_category" class="form-control" required>
                                            <?php foreach ($giftCategories as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo (($gift->get('categories') ?? '') === $category) ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucfirst($category)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="gift_credits">Credits</label>
                                        <input type="number" min="1" id="gift_credits" name="gift_credits" class="form-control" value="<?php echo htmlspecialchars((string)($gift->get('coins') ?? 0)); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="gift_file">Replace Photo/File</label>
                                        <input type="file" id="gift_file" name="gift_file" class="form-control" accept="application/json,image/png,image/jpeg,image/webp,image/gif">
                                        <small class="text-muted">Upload JSON or image file.</small>
                                    </div>

                                    <div class="form-group form-check">
                                        <input type="checkbox" class="form-check-input" id="remove_file" name="remove_file">
                                        <label class="form-check-label" for="remove_file">Remove current photo/file</label>
                                    </div>

                                    <?php if ($fileUrl): ?>
                                        <div class="mb-3">
                                            <a href="<?php echo htmlspecialchars($fileUrl); ?>" target="_blank" class="btn btn-sm btn-info">Open Current File</a>
                                        </div>
                                    <?php endif; ?>

                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <a href="../dashboard/gift.php" class="btn btn-secondary">Back</a>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../dashboard/footer.php'; ?>
</body>
</html>
