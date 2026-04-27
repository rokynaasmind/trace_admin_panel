<?php

require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseQuery;
use Parse\ParseUser;

session_start();

$currUser = ParseUser::getCurrentUser();
if ($currUser) {
    $_SESSION['token'] = $currUser->getSessionToken();
} else {
    header("Refresh:0; url=../index.php");
}

$categoryId = trim((string)($_GET['objectId'] ?? ''));
$category = null;
$formError = '';
$formSuccess = '';

if ($categoryId === '') {
    $formError = 'Invalid category selected.';
} else {
    try {
        $query = new ParseQuery('GiftCategory');
        $category = $query->get($categoryId, true);
    } catch (Exception $e) {
        $formError = $e->getMessage();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update_gift_category' && $category) {
    $name = trim((string)($_POST['category_name'] ?? ''));
    $rawCode = trim((string)($_POST['category_code'] ?? ''));
    $code = strtolower(preg_replace('/[^a-z0-9_]+/', '_', $rawCode));
    $code = trim($code, '_');

    if ($name === '' || $code === '') {
        $formError = 'Please provide category name and code.';
    } else {
        try {
            $duplicateQuery = new ParseQuery('GiftCategory');
            $duplicateQuery->equalTo('code', $code);
            $duplicateQuery->notEqualTo('objectId', $category->getObjectId());
            $duplicate = $duplicateQuery->first(true);

            if ($duplicate) {
                $formError = 'This category code already exists. Please use another code.';
            } else {
                $oldCode = (string)($category->get('code') ?? '');
                $category->set('name', $name);
                $category->set('code', $code);
                $category->save(true);

                if ($oldCode !== '' && $oldCode !== $code) {
                    $giftQuery = new ParseQuery('Gifts');
                    $giftQuery->equalTo('categories', $oldCode);
                    $giftQuery->limit(1000);
                    $gifts = $giftQuery->find(true);

                    foreach ($gifts as $gift) {
                        $gift->set('categories', $code);
                        $gift->save(true);
                    }
                }

                header('Location: ../dashboard/gift_category.php?updated=1');
                exit;
            }
        } catch (Exception $e) {
            $formError = $e->getMessage();
        }
    }
}

?>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Features</a></li>
                <li class="breadcrumb-item"><a href="../dashboard/gift_category.php">Gift categories</a></li>
                <li class="breadcrumb-item active">Edit gift category</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-10 col-xl-6" style="margin-left:auto; margin-right:auto; padding:10px 30px;">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">Edit Gift Category</h4>

                        <?php if (!empty($formError)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($formError); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($formSuccess)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($formSuccess); ?></div>
                        <?php endif; ?>

                        <?php if (!$category): ?>
                            <a href="../dashboard/gift_category.php" class="btn btn-secondary">Back</a>
                        <?php else: ?>
                            <form method="post" action="" novalidate>
                                <input type="hidden" name="action" value="update_gift_category">

                                <div class="form-group">
                                    <label for="category_name">Category Name</label>
                                    <input type="text" id="category_name" name="category_name" class="form-control" required value="<?php echo htmlspecialchars((string)($_POST['category_name'] ?? $category->get('name') ?? '')); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="category_code">Category Code</label>
                                    <input type="text" id="category_code" name="category_code" class="form-control" required value="<?php echo htmlspecialchars((string)($_POST['category_code'] ?? $category->get('code') ?? '')); ?>">
                                    <small class="text-muted">Only letters, numbers and underscore are kept.</small>
                                </div>

                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="../dashboard/gift_category.php" class="btn btn-secondary">Back</a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
