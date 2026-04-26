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
    header("Refresh:0; url=../index.php");
}

$giftMessage = '';
$giftError = '';
$giftCategories = ['love', 'moods', 'artists', 'collectibles', 'games', 'family', 'classic', '3d', 'vip', 'country', 'festival', 'trending'];

if (isset($_POST['action']) && $_POST['action'] === 'update_gift') {
    $giftId = trim($_POST['gift_id'] ?? '');
    $name = trim($_POST['gift_name'] ?? '');
    $category = trim($_POST['gift_category'] ?? '');
    $coins = (int)($_POST['gift_credits'] ?? 0);
    $removeFile = isset($_POST['remove_file']);

    if ($giftId === '' || $name === '' || $category === '' || $coins <= 0) {
        $giftError = 'Please provide valid gift details before updating.';
    } else {
        try {
            $query = new ParseQuery('Gifts');
            $gift = $query->get($giftId, true);
            $gift->set('name', $name);
            $gift->set('categories', $category);
            $gift->set('coins', $coins);

            if ($removeFile) {
                $gift->set('file', null);
            }

            if (isset($_FILES['gift_file']) && !empty($_FILES['gift_file']['tmp_name'])) {
                $filePath = realpath($_FILES['gift_file']['tmp_name']);
                $fileName = $_FILES['gift_file']['name'];
                if ($filePath && $fileName) {
                    $gift->set('file', ParseFile::createFromFile($filePath, $fileName));
                }
            }

            $gift->save(true);
            $giftMessage = 'Gift updated successfully.';
        } catch (ParseException $e) {
            $giftError = $e->getMessage();
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_gift') {
    $giftId = trim($_POST['gift_id'] ?? '');
    if ($giftId === '') {
        $giftError = 'Invalid gift selected for deletion.';
    } else {
        try {
            $query = new ParseQuery('Gifts');
            $gift = $query->get($giftId, true);
            $gift->destroy(true);
            $giftMessage = 'Gift removed successfully.';
        } catch (ParseException $e) {
            $giftError = $e->getMessage();
        }
    }
}

?>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Features</a></li>
                <li class="breadcrumb-item active">All gifts</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row bg-white m-l-0 m-r-0 box-shadow "></div>

        <div class="row">
            <div class="col-lg">
                <div class="card">

                    <?php
                    $query = new ParseQuery('Gifts');
                    $categoriesToExclude = ['avatar_frame', 'party_theme', 'entrance_effect', 'promotional_image'];
                    $query->notContainedIn('categories', $categoriesToExclude);
                    $matchCounter = 0;
                    try {
                        $matchCounter = $query->count();
                    } catch (Exception $e) {
                        $matchCounter = '?';
                    }

                    echo ' <h2 class="card-title">' . $matchCounter . ' Gift in total</h2> ';
                    ?>

                    <?php if (!empty($giftMessage)): ?>
                        <div class="alert alert-success m-3 mb-0"><?php echo htmlspecialchars($giftMessage); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($giftError)): ?>
                        <div class="alert alert-danger m-3 mb-0"><?php echo htmlspecialchars($giftError); ?></div>
                    <?php endif; ?>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="color:#65131f ;">ObjectId</th>
                                        <th style="color:#65131f ;">Date</th>
                                        <th style="color:#65131f ;">Name</th>
                                        <th style="color:#65131f ;">Category</th>
                                        <th style="color:#65131f ;">Credits</th>
                                        <th style="color:#65131f ;">Photo/File</th>
                                        <th style="color:#65131f ;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    try {
                                        $query = new ParseQuery('Gifts');
                                        $categoriesToExclude = ['avatar_frame', 'party_theme', 'entrance_effect', 'promotional_image'];
                                        $query->notContainedIn('categories', $categoriesToExclude);
                                        $query->descending('createdAt');
                                        $catArray = $query->find(false);

                                        foreach ($catArray as $iValue) {
                                            $cObj = $iValue;

                                            $objectId = $cObj->getObjectId();
                                            $date = $cObj->getCreatedAt();
                                            $created = date_format($date, 'd/m/Y');

                                            $credits = (int)($cObj->get('coins') ?? 0);
                                            $giftName = $cObj->get('name') ?? '';
                                            $giftCategory = $cObj->get('categories') ?? '';

                                            $profilePhotoUrl = '';
                                            $file = '<span class="text-danger font-weight-bold">NOT AVAILABLE</span>';
                                            $typeFile = $cObj->get('file');
                                            if ($typeFile !== null) {
                                                $profilePhotoUrl = $typeFile->getURL();
                                                $safeUrl = htmlspecialchars($profilePhotoUrl, ENT_QUOTES, 'UTF-8');
                                                $jsUrl = addslashes($profilePhotoUrl);
                                                $file = "<span style=\"margin-right: 8px;\"><a href='#' onclick='showImage(\"$jsUrl\")' class=\"badge badge-info\" style=\"background:#5d0375;\">View</a></span>";
                                                $file .= "<a target='_blank' href=\"$safeUrl\" class=\"badge badge-secondary\">Open</a>";
                                            }

                                            $editName = htmlspecialchars($giftName, ENT_QUOTES, 'UTF-8');
                                            $editCategory = htmlspecialchars($giftCategory, ENT_QUOTES, 'UTF-8');

                                            echo '
                                            <tr>
                                                <td>' . htmlspecialchars($objectId) . '</td>
                                                <td>' . htmlspecialchars($created) . '</td>
                                                <td><span>' . htmlspecialchars($giftName) . '</span></td>
                                                <td><span>' . htmlspecialchars($giftCategory) . '</span></td>
                                                <td>' . htmlspecialchars((string)$credits) . '</td>
                                                <td><span>' . $file . '</span></td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-warning"
                                                        onclick="openGiftEdit(\'' . $objectId . '\', \'' . $editName . '\', \'' . $editCategory . '\', \'' . $credits . '\')"
                                                    >
                                                        <i class="fa fa-edit"></i> Edit
                                                    </button>
                                                    <form method="post" action="" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to remove this gift?\')">
                                                        <input type="hidden" name="action" value="delete_gift">
                                                        <input type="hidden" name="gift_id" value="' . htmlspecialchars($objectId, ENT_QUOTES, 'UTF-8') . '">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            ';
                                        }
                                    } catch (ParseException $e) {
                                        echo '<tr><td colspan="7" class="text-danger">' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                    }
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div id="giftEditPanel" class="card m-3" style="display:none; border:1px solid #e5e5e5;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Edit Gift</strong>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeGiftEdit()">Close</button>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="">
            <input type="hidden" name="action" value="update_gift">
            <input type="hidden" id="gift_id" name="gift_id" value="">

            <div class="form-group">
                <label for="gift_name">Gift Name</label>
                <input type="text" id="gift_name" name="gift_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="gift_category">Category</label>
                <select id="gift_category" name="gift_category" class="form-control" required>
                    <?php foreach ($giftCategories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars(ucfirst($category)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="gift_credits">Credits</label>
                <input type="number" min="1" id="gift_credits" name="gift_credits" class="form-control" required>
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

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openGiftEdit(id, name, category, credits) {
    document.getElementById('gift_id').value = id;
    document.getElementById('gift_name').value = name;
    document.getElementById('gift_category').value = category;
    document.getElementById('gift_credits').value = credits;
    document.getElementById('remove_file').checked = false;
    document.getElementById('giftEditPanel').style.display = 'block';
}

function closeGiftEdit() {
    document.getElementById('giftEditPanel').style.display = 'none';
}
</script>
