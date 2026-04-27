<?php

require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseUser;

session_start();

$currUser = ParseUser::getCurrentUser();
if ($currUser) {
    $_SESSION['token'] = $currUser->getSessionToken();
} else {
    header("Refresh:0; url=../index.php");
}

$categoryMessage = '';
$categoryError = '';

if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $categoryMessage = 'Gift category updated successfully.';
}

if (isset($_GET['created']) && $_GET['created'] === '1') {
    $categoryMessage = 'Gift category created successfully.';
}

?>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Features</a></li>
                <li class="breadcrumb-item active">Gift categories</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row bg-white m-l-0 m-r-0 box-shadow "></div>

        <div class="row">
            <div class="col-lg">
                <div class="card">

                    <?php
                    $query = new ParseQuery('GiftCategory');
                    $categoryCounter = 0;
                    try {
                        $categoryCounter = $query->count();
                    } catch (Exception $e) {
                        $categoryCounter = '?';
                    }

                    echo ' <h2 class="card-title">' . $categoryCounter . ' Gift categories in total</h2> ';
                    ?>

                    <?php if (!empty($categoryMessage)): ?>
                        <div class="alert alert-success m-3 mb-0"><?php echo htmlspecialchars($categoryMessage); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($categoryError)): ?>
                        <div class="alert alert-danger m-3 mb-0"><?php echo htmlspecialchars($categoryError); ?></div>
                    <?php endif; ?>

                    <div class="card-body">
                        <div class="mb-3">
                            <a href="../dashboard/add_gift_category.php" class="btn btn-sm btn-primary">+ Add Gift Category</a>
                        </div>
                        <div class="table-responsive">
                            <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="color:#65131f ;">ObjectId</th>
                                        <th style="color:#65131f ;">Date</th>
                                        <th style="color:#65131f ;">Name</th>
                                        <th style="color:#65131f ;">Code</th>
                                        <th style="color:#65131f ;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    try {
                                        $query = new ParseQuery('GiftCategory');
                                        $query->ascending('name');
                                        $categoryArray = $query->find(false);

                                        foreach ($categoryArray as $cObj) {
                                            $objectId = $cObj->getObjectId();
                                            $date = $cObj->getCreatedAt();
                                            $created = date_format($date, 'd/m/Y');

                                            $name = (string)($cObj->get('name') ?? '');
                                            $code = (string)($cObj->get('code') ?? '');

                                            echo '
                                            <tr>
                                                <td>' . htmlspecialchars($objectId) . '</td>
                                                <td>' . htmlspecialchars($created) . '</td>
                                                <td>' . htmlspecialchars($name) . '</td>
                                                <td>' . htmlspecialchars($code) . '</td>
                                                <td>
                                                    <a href="../dashboard/edit_gift_category.php?objectId=' . urlencode($objectId) . '" class="btn btn-sm btn-warning">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                            ';
                                        }
                                    } catch (ParseException $e) {
                                        echo '<tr><td colspan="5" class="text-danger">' . htmlspecialchars($e->getMessage()) . '</td></tr>';
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
