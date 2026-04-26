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

$frameId = $_GET['objectId'] ?? '';
$frame = null;
$frameError = '';
$frameSuccess = '';

if ($frameId !== '') {
    try {
        $query = new ParseQuery('Gifts');
        $frame = $query->get($frameId, true);
    } catch (ParseException $e) {
        $frameError = $e->getMessage();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update_frame' && $frame) {
    $name = trim($_POST['frame_name'] ?? '');
    $coins = (int)($_POST['frame_credits'] ?? 0);
    $period = (int)($_POST['frame_period'] ?? 15);
    $isWorking = isset($_POST['frame_is_working']);
    $removeFile = isset($_POST['remove_frame_file']);

    if ($name === '' || $coins <= 0) {
        $frameError = 'Please provide valid frame details before updating.';
    } else {
        try {
            $frame->set('name', $name);
            $frame->set('coins', $coins);
            $frame->set('period', $period > 0 ? $period : 15);
            $frame->set('categories', 'avatar_frame');
            $frame->set('isWorking', $isWorking);

            if ($removeFile) {
                $frame->set('file', null);
            }

            if (isset($_FILES['frame_file']) && !empty($_FILES['frame_file']['tmp_name']) && is_uploaded_file($_FILES['frame_file']['tmp_name'])) {
                $originalName = $_FILES['frame_file']['name'] ?? 'avatar_frame';
                $safeBaseName = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $safeExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $safeFileName = ($safeBaseName !== '' ? $safeBaseName : 'avatar_frame') . ($safeExtension !== '' ? '.' . $safeExtension : '');
                $frame->set('file', ParseFile::createFromFile($_FILES['frame_file']['tmp_name'], $safeFileName));
            }

            $frame->save(true);
            $frameSuccess = 'Avatar frame updated successfully.';
        } catch (ParseException $e) {
            $frameError = $e->getMessage();
        }
    }
}

$typeFile = $frame ? $frame->get('file') : null;
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
    <title><?php echo $app_name; ?> - Edit Avatar Frame</title>
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
                    <li class="breadcrumb-item"><a href="../dashboard/avatar_frame.php">Features</a></li>
                    <li class="breadcrumb-item active">Edit avatar frame</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-10 col-xl-8" style="margin-left:auto; margin-right:auto; padding:10px 30px;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-4">Edit Avatar Frame</h4>

                            <?php if (!empty($frameError)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($frameError); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($frameSuccess)): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($frameSuccess); ?></div>
                            <?php endif; ?>

                            <?php if (!$frame): ?>
                                <div class="alert alert-warning">Avatar frame not found.</div>
                            <?php else: ?>
                                <form method="post" enctype="multipart/form-data" action="">
                                    <input type="hidden" name="action" value="update_frame">

                                    <div class="form-group">
                                        <label for="frame_name">Frame Name</label>
                                        <input type="text" id="frame_name" name="frame_name" class="form-control" value="<?php echo htmlspecialchars($frame->get('name') ?? ''); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="frame_credits">Credits</label>
                                        <input type="number" min="1" id="frame_credits" name="frame_credits" class="form-control" value="<?php echo htmlspecialchars((string)($frame->get('coins') ?? 0)); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="frame_period">Period (days)</label>
                                        <input type="number" min="1" id="frame_period" name="frame_period" class="form-control" value="<?php echo htmlspecialchars((string)($frame->get('period') ?? 15)); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="frame_file">Replace File (PNG)</label>
                                        <input type="file" id="frame_file" name="frame_file" class="form-control" accept="image/png,image/jpeg,image/webp">
                                    </div>

                                    <div class="form-group form-check">
                                        <input type="checkbox" class="form-check-input" id="frame_is_working" name="frame_is_working" <?php echo ($frame->get('isWorking') ?? false) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="frame_is_working">Frame is working</label>
                                    </div>

                                    <div class="form-group form-check">
                                        <input type="checkbox" class="form-check-input" id="remove_frame_file" name="remove_frame_file">
                                        <label class="form-check-label" for="remove_frame_file">Remove current file</label>
                                    </div>

                                    <?php if ($fileUrl): ?>
                                        <div class="mb-3">
                                            <a href="<?php echo htmlspecialchars($fileUrl); ?>" target="_blank" class="btn btn-sm btn-info">Open Current File</a>
                                        </div>
                                    <?php endif; ?>

                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <a href="../dashboard/avatar_frame.php" class="btn btn-secondary">Back</a>
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
