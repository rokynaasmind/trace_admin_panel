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

$frameMessage = '';
$frameError = '';

if (isset($_POST['action']) && $_POST['action'] === 'update_frame') {
    $frameId = trim($_POST['frame_id'] ?? '');
    $name = trim($_POST['frame_name'] ?? '');
    $coins = (int)($_POST['frame_credits'] ?? 0);
    $period = (int)($_POST['frame_period'] ?? 15);
    $isWorking = isset($_POST['frame_is_working']);
    $removeFile = isset($_POST['remove_frame_file']);

    if ($frameId === '' || $name === '' || $coins <= 0) {
        $frameError = 'Please provide valid frame details before updating.';
    } else {
        try {
            $query = new ParseQuery('Gifts');
            $frame = $query->get($frameId, true);
            $frame->set('name', $name);
            $frame->set('coins', $coins);
            $frame->set('period', $period > 0 ? $period : 15);
            $frame->set('categories', 'avatar_frame');
            $frame->set('isWorking', $isWorking);

            if ($removeFile) {
                $frame->set('file', null);
            }

            if (isset($_FILES['frame_file']) && !empty($_FILES['frame_file']['tmp_name'])) {
                $filePath = realpath($_FILES['frame_file']['tmp_name']);
                $fileName = $_FILES['frame_file']['name'];
                if ($filePath && $fileName) {
                    $frame->set('file', ParseFile::createFromFile($filePath, $fileName));
                }
            }

            $frame->save(true);
            $frameMessage = 'Avatar frame updated successfully.';
        } catch (ParseException $e) {
            $frameError = $e->getMessage();
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_frame') {
    $frameId = trim($_POST['frame_id'] ?? '');
    if ($frameId === '') {
        $frameError = 'Invalid frame selected for deletion.';
    } else {
        try {
            $query = new ParseQuery('Gifts');
            $frame = $query->get($frameId, true);
            $frame->destroy(true);
            $frameMessage = 'Avatar frame removed successfully.';
        } catch (ParseException $e) {
            $frameError = $e->getMessage();
        }
    }
}

?>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Features</a></li>
                <li class="breadcrumb-item active">All Avatar Frames</li>
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
                    $query->equalTo('categories', 'avatar_frame');
                    $matchCounter = 0;
                    try {
                        $matchCounter = $query->count();
                    } catch (Exception $e) {
                        $matchCounter = '?';
                    }

                    echo ' <h2 class="card-title">' . $matchCounter . ' Avatar Frames in total</h2> ';
                    ?>

                    <?php if (!empty($frameMessage)): ?>
                        <div class="alert alert-success m-3 mb-0"><?php echo htmlspecialchars($frameMessage); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($frameError)): ?>
                        <div class="alert alert-danger m-3 mb-0"><?php echo htmlspecialchars($frameError); ?></div>
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
                                        <th style="color:#65131f ;">Working</th>
                                        <th style="color:#65131f ;">File</th>
                                        <th style="color:#65131f ;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    try {
                                        $query = new ParseQuery('Gifts');
                                        $query->equalTo('categories', 'avatar_frame');
                                        $query->descending('createdAt');
                                        $catArray = $query->find(false);

                                        foreach ($catArray as $iValue) {
                                            $cObj = $iValue;

                                            $objectId = $cObj->getObjectId();
                                            $date = $cObj->getCreatedAt();
                                            $created = date_format($date, 'd/m/Y');

                                            $credits = (int)($cObj->get('coins') ?? 0);
                                            $period = (int)($cObj->get('period') ?? 15);
                                            $isWorking = (bool)($cObj->get('isWorking') ?? false);

                                            $typeFile = $cObj->get('file');
                                            if ($typeFile !== null) {
                                                $profilePhotoUrl = $typeFile->getURL();
                                                $safeUrl = htmlspecialchars($profilePhotoUrl, ENT_QUOTES, 'UTF-8');
                                                $jsUrl = addslashes($profilePhotoUrl);
                                                $file = "<span style=\"margin-right: 8px;\"><a href='#' onclick='showImage(\"$jsUrl\")' class=\"badge badge-info\" style=\"background:#5d0375;\">View</a></span>";
                                                $file .= "<a target='_blank' href=\"$safeUrl\" class=\"badge badge-secondary\">Open</a>";
                                            } else {
                                                $file = '<span class="text-danger font-weight-bold">NOT AVAILABLE</span>';
                                            }

                                            $giftName = $cObj->get('name') ?? '';
                                            $giftCategory = $cObj->get('categories') ?? 'avatar_frame';
                                            $workingBadge = $isWorking
                                                ? '<span class="badge badge-success">Working</span>'
                                                : '<span class="badge badge-secondary">Disabled</span>';

                                            $editName = htmlspecialchars($giftName, ENT_QUOTES, 'UTF-8');

                                            echo '
                                            <tr>
                                                <td>' . htmlspecialchars($objectId) . '</td>
                                                <td>' . htmlspecialchars($created) . '</td>
                                                <td><span>' . htmlspecialchars($giftName) . '</span></td>
                                                <td><span>' . htmlspecialchars($giftCategory) . '</span></td>
                                                <td>' . htmlspecialchars((string)$credits) . '</td>
                                                <td>' . $workingBadge . '</td>
                                                <td><span>' . $file . '</span></td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-warning"
                                                        onclick="openFrameEdit(\'' . $objectId . '\', \'' . $editName . '\', \'' . $credits . '\', \'' . $period . '\', ' . ($isWorking ? 'true' : 'false') . ')"
                                                    >
                                                        <i class="fa fa-edit"></i> Edit
                                                    </button>
                                                    <form method="post" action="" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to remove this avatar frame?\')">
                                                        <input type="hidden" name="action" value="delete_frame">
                                                        <input type="hidden" name="frame_id" value="' . htmlspecialchars($objectId, ENT_QUOTES, 'UTF-8') . '">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            ';
                                        }
                                    } catch (ParseException $e) {
                                        echo '<tr><td colspan="8" class="text-danger">' . htmlspecialchars($e->getMessage()) . '</td></tr>';
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

<div id="frameEditPanel" class="card m-3" style="display:none; border:1px solid #e5e5e5;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Edit Avatar Frame</strong>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeFrameEdit()">Close</button>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="">
            <input type="hidden" name="action" value="update_frame">
            <input type="hidden" id="frame_id" name="frame_id" value="">

            <div class="form-group">
                <label for="frame_name">Frame Name</label>
                <input type="text" id="frame_name" name="frame_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="frame_credits">Credits</label>
                <input type="number" min="1" id="frame_credits" name="frame_credits" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="frame_period">Period (days)</label>
                <input type="number" min="1" id="frame_period" name="frame_period" class="form-control" value="15">
            </div>

            <div class="form-group">
                <label for="frame_file">Replace File (PNG)</label>
                <input type="file" id="frame_file" name="frame_file" class="form-control" accept="image/png,image/jpeg,image/webp">
            </div>

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="frame_is_working" name="frame_is_working">
                <label class="form-check-label" for="frame_is_working">Frame is working</label>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="remove_frame_file" name="remove_frame_file">
                <label class="form-check-label" for="remove_frame_file">Remove current file</label>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openFrameEdit(id, name, credits, period, isWorking) {
    document.getElementById('frame_id').value = id;
    document.getElementById('frame_name').value = name;
    document.getElementById('frame_credits').value = credits;
    document.getElementById('frame_period').value = period;
    document.getElementById('frame_is_working').checked = !!isWorking;
    document.getElementById('remove_frame_file').checked = false;
    document.getElementById('frameEditPanel').style.display = 'block';
}

function closeFrameEdit() {
    document.getElementById('frameEditPanel').style.display = 'none';
}
</script>
