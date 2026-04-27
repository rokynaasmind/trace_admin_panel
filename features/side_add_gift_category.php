<?php

require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;

session_start();

$currUser = ParseUser::getCurrentUser();
if ($currUser) {
    $_SESSION['token'] = $currUser->getSessionToken();
} else {
    header("Refresh:0; url=../index.php");
}

$formError = '';
$formSuccess = '';

if (isset($_POST['val-name'])) {
    $name = trim((string)($_POST['val-name'] ?? ''));
    $rawCode = trim((string)($_POST['val-code'] ?? ''));
    $code = strtolower(preg_replace('/[^a-z0-9_]+/', '_', $rawCode));
    $code = trim($code, '_');

    if ($name === '') {
        $formError = 'Category name is required.';
    } else {
        if ($code === '') {
            $generatedCode = strtolower(preg_replace('/[^a-z0-9_]+/', '_', $name));
            $code = trim($generatedCode, '_');
        }

        if ($code === '') {
            $formError = 'Please provide a valid category code.';
        } else {
            try {
                $duplicateQuery = new ParseQuery('GiftCategory');
                $duplicateQuery->equalTo('code', $code);
                $alreadyExists = $duplicateQuery->first(true);

                if ($alreadyExists) {
                    $formError = 'This category code already exists. Please use a unique code.';
                } else {
                    $newCategory = ParseObject::create('GiftCategory');
                    $newCategory->set('name', $name);
                    $newCategory->set('code', $code);
                    $newCategory->save(true);

                    echo '<script>window.location.href="../dashboard/gift_category.php?created=1";</script>';
                    exit;
                }
            } catch (Exception $e) {
                $formError = $e->getMessage();
            }
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
                <li class="breadcrumb-item active">Add gift category</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row bg-white m-l-0 m-r-0 box-shadow "></div>
        <div class="row">
            <div class="col-12 col-md-10 col-xl-6" style="margin-left:auto; margin-right:auto;padding:10px 30px">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">Add Gift Category</h4>

                        <?php if (!empty($formError)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($formError); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($formSuccess)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($formSuccess); ?></div>
                        <?php endif; ?>

                        <form class="form-valide" action="" method="post" novalidate>
                            <div class="form-group row">
                                <label for="val-name" class="col-sm-4 col-form-label">Category Name<span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="val-name" name="val-name" placeholder="Example: Festival" required value="<?php echo htmlspecialchars((string)($_POST['val-name'] ?? '')); ?>">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="val-code" class="col-sm-4 col-form-label">Category Code<span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="val-code" name="val-code" placeholder="Example: festival" required value="<?php echo htmlspecialchars((string)($_POST['val-code'] ?? '')); ?>">
                                    <small class="text-muted">Only letters, numbers and underscore are kept.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-10">
                                    <button type="submit" class="btn text-white" style="background:#5d0375;">Save</button>
                                    <a href="../dashboard/gift_category.php" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
