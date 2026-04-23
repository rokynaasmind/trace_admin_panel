<?php
require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseException;

session_start();

$currUser = ParseUser::getCurrentUser();
if (!$currUser) {
    header("Refresh:0; url=../index.php");
} elseif ($currUser->get("role") !== "admin"){
    header("Refresh:0; url=../auth/logout.php");
}

$addError = '';
$addSuccess = '';

// Handle Add Agency Member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_member') {
    try {
        $agencyId = isset($_POST['agency_id']) ? trim($_POST['agency_id']) : '';
        $userId = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';

        if (empty($agencyId) || empty($userId) || empty($role)) {
            $addError = "Agency ID, User ID, and Role are required.";
        } else {
            // Check if member already exists
            $checkQuery = new ParseQuery("AgencyMember");
            $checkQuery->equalTo("agencyId", $agencyId);
            $checkQuery->equalTo("userId", $userId);
            $existing = $checkQuery->find(true);
            
            if (count($existing) > 0) {
                $addError = "This user is already a member of this agency.";
            } else {
                $member = new ParseObject("AgencyMember");
                $member->set("agencyId", $agencyId);
                $member->set("userId", $userId);
                $member->set("role", $role);
                $member->set("status", "active");
                $member->save();
                $addSuccess = "Agency member added successfully!";
            }
        }
    } catch (ParseException $e) {
        $addError = "Error: " . $e->getMessage();
    }
}

// Handle Delete Agency Member
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $query = new ParseQuery("AgencyMember");
        $member = $query->get($_GET['id'], true);
        $member->destroy();
        header("Location: agency_members.php?deleted=1");
        exit;
    } catch (ParseException $e) {
        $addError = "Error deleting member: " . $e->getMessage();
    }
}

?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/dashboard/images/favicon.png">
    <title><?php echo $app_name;?> - Agency Members</title>
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
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Agency</a></li>
                        <li class="breadcrumb-item active">Agency Members</li>
                    </ol>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="m-0">Agency Members</h4>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addMemberModal">
                                    <i class="fa fa-plus"></i> Add Member
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
                                    Member deleted successfully!
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="color:#65131f;">ID</th>
                                                <th style="color:#65131f;">Agency ID</th>
                                                <th style="color:#65131f;">User ID</th>
                                                <th style="color:#65131f;">Role</th>
                                                <th style="color:#65131f;">Status</th>
                                                <th style="color:#65131f;">Joined Date</th>
                                                <th style="color:#65131f;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            try {
                                                $query = new ParseQuery("AgencyMember");
                                                $query->descending('createdAt');
                                                $query->limit(500);
                                                $memberArray = $query->find(true);

                                                if (count($memberArray) === 0) {
                                                    echo '<tr><td colspan="7" class="text-center text-warning font-weight-bold">No members found</td></tr>';
                                                } else {
                                                    foreach ($memberArray as $member) {
                                                        $memberId = $member->getObjectId();
                                                        $agencyId = $member->get('agencyId');
                                                        $userId = $member->get('userId');
                                                        $role = $member->get('role');
                                                        $status = $member->get('status');
                                                        $createdAt = $member->getCreatedAt();
                                                        $joinedDate = $createdAt ? $createdAt->format('Y-m-d H:i:s') : 'N/A';

                                                        $statusBadge = $status === 'active' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-warning">Inactive</span>';
                                                        $roleBadge = '<span class="badge badge-info">' . htmlspecialchars($role) . '</span>';

                                                        echo '
                                                        <tr>
                                                            <td>' . htmlspecialchars($memberId) . '</td>
                                                            <td>' . htmlspecialchars($agencyId) . '</td>
                                                            <td>' . htmlspecialchars($userId) . '</td>
                                                            <td>' . $roleBadge . '</td>
                                                            <td>' . $statusBadge . '</td>
                                                            <td>' . htmlspecialchars($joinedDate) . '</td>
                                                            <td>
                                                                <a href="agency_members.php?action=delete&id=' . $memberId . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\');">
                                                                    <i class="fa fa-trash"></i> Remove
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

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel">Add Agency Member</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_member">
                        <div class="form-group">
                            <label for="agency_id">Agency ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="agency_id" name="agency_id" placeholder="Please input" required>
                        </div>
                        <div class="form-group">
                            <label for="user_id">User ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_id" name="user_id" placeholder="Please input" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role <span class="text-danger">*</span></label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="President">President</option>
                                <option value="Vice President">Vice President</option>
                                <option value="Manager">Manager</option>
                                <option value="Member">Member</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
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
</body>
</html>
