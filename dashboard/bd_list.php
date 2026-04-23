<?php
require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseACL;
use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseCloud;

session_start();

$currUser = ParseUser::getCurrentUser();
if (!$currUser) {
    header("Refresh:0; url=../index.php");
} elseif ($currUser->get("role") !== "admin"){
    header("Refresh:0; url=../auth/logout.php");
}

// Handle Add BD
$addError = '';
$addSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_bd') {
    try {
        $userId = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
        $parentAdminUid = isset($_POST['parent_admin_uid']) ? trim($_POST['parent_admin_uid']) : '';
        $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';

        if (empty($userId) || empty($parentAdminUid)) {
            $addError = "User ID and Parent Admin UID are required.";
        } else {
            // Check if BD already exists for this user
            $checkQuery = new ParseQuery("BD");
            $checkQuery->equalTo("userId", $userId);
            $existing = $checkQuery->find(true);
            
            if (count($existing) > 0) {
                $addError = "This user is already a BD.";
            } else {
                // Create new BD record
                $bd = new ParseObject("BD");
                $bd->set("userId", $userId);
                $bd->set("parentAdminUid", $parentAdminUid);
                $bd->set("remark", $remark);
                $bd->set("status", "active");
                $bd->set("createdAt", new DateTime());
                $bd->save();
                $addSuccess = "BD added successfully!";
            }
        }
    } catch (ParseException $e) {
        $addError = "Error: " . $e->getMessage();
    }
}

// Handle Delete BD
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $query = new ParseQuery("BD");
        $bd = $query->get($_GET['id'], true);
        $bd->destroy();
        header("Location: bd_list.php?deleted=1");
        exit;
    } catch (ParseException $e) {
        $addError = "Error deleting BD: " . $e->getMessage();
    }
}

// Handle Update BD Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    try {
        $bdId = isset($_POST['bd_id']) ? trim($_POST['bd_id']) : '';
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';

        if (!empty($bdId)) {
            $query = new ParseQuery("BD");
            $bd = $query->get($bdId, true);
            $bd->set("status", $status);
            $bd->save();
            $addSuccess = "BD status updated successfully!";
        }
    } catch (ParseException $e) {
        $addError = "Error: " . $e->getMessage();
    }
}

?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/dashboard/images/favicon.png">
    <title><?php echo $app_name;?> - BD Management</title>
    <link href="../assets/dashboard/css/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/dashboard/css/lib/calendar2/semantic.ui.min.css" rel="stylesheet">
    <link href="../assets/dashboard/css/lib/calendar2/pignose.calendar.min.css" rel="stylesheet">
    <link href="../assets/dashboard/css/lib/owl.carousel.min.css" rel="stylesheet" />
    <link href="../assets/dashboard/css/lib/owl.theme.default.min.css" rel="stylesheet" />
    <link href="../assets/dashboard/css/helper.css" rel="stylesheet">
    <link href="../assets/dashboard/css/style.css" rel="stylesheet">
    <link href="../assets/dashboard/css/aliki.css" rel="stylesheet">
</head>

<body class="fix-header fix-sidebar">
    <div id="main-wrapper">
        <?php
        include '../admin/header_admin.php';
        include '../admin/left_sidebar_admin.php';
        ?>

        <div class="page-wrapper">
            <div class="row page-titles">
                <div class="col">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Users</a></li>
                        <li class="breadcrumb-item active">BD Management</li>
                    </ol>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="m-0">BD List</h4>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addBDModal">
                                    <i class="fa fa-plus"></i> Add BD
                                </button>
                            </div>

                            <?php if (!empty($addError)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($addError); ?>
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($addSuccess)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($addSuccess); ?>
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_GET['deleted'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    BD deleted successfully!
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="color:#65131f;">ID</th>
                                                <th style="color:#65131f;">User ID</th>
                                                <th style="color:#65131f;">Parent Admin UID</th>
                                                <th style="color:#65131f;">Remark</th>
                                                <th style="color:#65131f;">Status</th>
                                                <th style="color:#65131f;">Created Date</th>
                                                <th style="color:#65131f;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            try {
                                                $query = new ParseQuery("BD");
                                                $query->descending('createdAt');
                                                $query->limit(500);
                                                $bdArray = $query->find(true);

                                                if (count($bdArray) === 0) {
                                                    echo '<tr><td colspan="7" class="text-center text-warning font-weight-bold">No BD found</td></tr>';
                                                } else {
                                                    foreach ($bdArray as $bd) {
                                                        $bdId = $bd->getObjectId();
                                                        $userId = $bd->get('userId');
                                                        $parentAdminUid = $bd->get('parentAdminUid');
                                                        $remark = $bd->get('remark');
                                                        $status = $bd->get('status');
                                                        $createdAt = $bd->getCreatedAt();
                                                        $createdDate = $createdAt ? $createdAt->format('Y-m-d H:i:s') : 'N/A';

                                                        $statusBadge = $status === 'active' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-warning">Inactive</span>';

                                                        echo '
                                                        <tr>
                                                            <td>' . htmlspecialchars($bdId) . '</td>
                                                            <td>' . htmlspecialchars($userId) . '</td>
                                                            <td>' . htmlspecialchars($parentAdminUid) . '</td>
                                                            <td>' . htmlspecialchars($remark ?? 'N/A') . '</td>
                                                            <td>' . $statusBadge . '</td>
                                                            <td>' . htmlspecialchars($createdDate) . '</td>
                                                            <td>
                                                                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editBDModal" onclick="editBD(\'' . $bdId . '\', \'' . addslashes($userId) . '\', \'' . addslashes($parentAdminUid) . '\', \'' . addslashes($remark ?? '') . '\', \'' . $status . '\')">
                                                                    <i class="fa fa-edit"></i> Edit
                                                                </button>
                                                                <a href="bd_list.php?action=delete&id=' . $bdId . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\');">
                                                                    <i class="fa fa-trash"></i> Delete
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        ';
                                                    }
                                                }
                                            } catch (ParseException $e) {
                                                echo '<tr><td colspan="7" class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
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

            <?php include 'footer.php' ?>
        </div>
    </div>

    <!-- Add BD Modal -->
    <div class="modal fade" id="addBDModal" tabindex="-1" role="dialog" aria-labelledby="addBDModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBDModalLabel">Add BD</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_bd">
                        <div class="form-group">
                            <label for="user_id">User ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_id" name="user_id" placeholder="Please input" required>
                        </div>
                        <div class="form-group">
                            <label for="parent_admin_uid">Parent Admin UID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="parent_admin_uid" name="parent_admin_uid" placeholder="Please input" required>
                        </div>
                        <div class="form-group">
                            <label for="remark">Remark</label>
                            <textarea class="form-control" id="remark" name="remark" placeholder="Please input" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">OK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit BD Modal -->
    <div class="modal fade" id="editBDModal" tabindex="-1" role="dialog" aria-labelledby="editBDModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBDModalLabel">Edit BD</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" id="edit_bd_id" name="bd_id">
                        <div class="form-group">
                            <label for="edit_user_id">User ID</label>
                            <input type="text" class="form-control" id="edit_user_id" disabled>
                        </div>
                        <div class="form-group">
                            <label for="edit_parent_admin_uid">Parent Admin UID</label>
                            <input type="text" class="form-control" id="edit_parent_admin_uid" disabled>
                        </div>
                        <div class="form-group">
                            <label for="edit_remark">Remark</label>
                            <textarea class="form-control" id="edit_remark" disabled rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_status">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/dashboard/js/lib/jquery/jquery.min.js"></script>
    <script src="../assets/dashboard/js/lib/bootstrap/js/bootstrap.min.js"></script>
    <script src="../assets/dashboard/js/lib/datatables/datatables.min.js"></script>
    <script src="../assets/dashboard/js/lib/datatables/cdn.datatables.min.js"></script>
    <script src="../assets/dashboard/js/lib/datatables/buttons.flash.min.js"></script>
    <script src="../assets/dashboard/js/lib/datatables/buttons.html5.min.js"></script>
    <script src="../assets/dashboard/js/lib/datatables/buttons.print.min.js"></script>
    <script src="../assets/dashboard/js/lib/datatables/buttons.colVis.min.js"></script>
    <script src="../assets/dashboard/js/lib/datatables/datatables-init.js"></script>
    <script>
        function editBD(id, userId, parentAdminUid, remark, status) {
            document.getElementById('edit_bd_id').value = id;
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_parent_admin_uid').value = parentAdminUid;
            document.getElementById('edit_remark').value = remark;
            document.getElementById('edit_status').value = status;
        }
    </script>
</body>
</html>
