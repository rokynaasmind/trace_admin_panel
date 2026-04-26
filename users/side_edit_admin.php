<?php

require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseUser;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currUser = ParseUser::getCurrentUser();
if (!$currUser) {
    header('Refresh:0; url=../index.php');
    exit;
}

if ($currUser->get('role') !== 'admin') {
    header('Refresh:0; url=../auth/logout.php');
    exit;
}

if ((($currUser->get('isSuperAdmin') ?? false) === true) !== true) {
    $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Only super admin can edit admin users.'];
    header('Location: ../dashboard/admin_users.php');
    exit;
}

$adminId = trim($_GET['objectId'] ?? '');
$formError = '';

if ($adminId === '') {
    $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Invalid admin selected for editing.'];
    header('Location: ../dashboard/admin_users.php');
    exit;
}

try {
    $query = new ParseQuery('_User');
    $targetAdmin = $query->get($adminId, true);
} catch (ParseException $e) {
    $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
    header('Location: ../dashboard/admin_users.php');
    exit;
}

if (($targetAdmin->get('role') ?? '') !== 'admin') {
    $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Selected user is not an admin account.'];
    header('Location: ../dashboard/admin_users.php');
    exit;
}

if (($targetAdmin->get('isSuperAdmin') ?? false) === true) {
    $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Super admin account cannot be edited from this page.'];
    header('Location: ../dashboard/admin_users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_admin_page') {
    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminUsername = trim($_POST['admin_username'] ?? '');
    $adminPassword = trim($_POST['admin_password'] ?? '');
    $adminGender = trim($_POST['admin_gender'] ?? 'OTH');
    $adminMode = trim($_POST['admin_mode'] ?? '0');

    if ($adminName === '' || $adminEmail === '' || $adminUsername === '') {
        $formError = 'Name, email, and username are required.';
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Please provide a valid email address.';
    } elseif ($adminPassword !== '' && strlen($adminPassword) < 6) {
        $formError = 'Password must be at least 6 characters if provided.';
    } else {
        try {
            $checkUsername = new ParseQuery('_User');
            $checkUsername->equalTo('username', $adminUsername);
            $checkUsername->notEqualTo('objectId', $adminId);
            $checkUsername->limit(1);
            $usernameExists = count($checkUsername->find(true)) > 0;

            $checkEmail = new ParseQuery('_User');
            $checkEmail->equalTo('email', $adminEmail);
            $checkEmail->notEqualTo('objectId', $adminId);
            $checkEmail->limit(1);
            $emailExists = count($checkEmail->find(true)) > 0;

            if ($usernameExists) {
                $formError = 'Username already exists. Please choose another one.';
            } elseif ($emailExists) {
                $formError = 'Email already exists. Please use another email.';
            } else {
                $targetAdmin->set('name', $adminName);
                $targetAdmin->set('email', $adminEmail);
                $targetAdmin->set('username', $adminUsername);
                $targetAdmin->set('gender', in_array($adminGender, ['MAL', 'FML', 'OTH'], true) ? $adminGender : 'OTH');
                $targetAdmin->set('isViewer', $adminMode === '1');

                if ($adminPassword !== '') {
                    $targetAdmin->setPassword($adminPassword);
                }

                $targetAdmin->save(true);
                $_SESSION['admin_users_flash'] = ['type' => 'success', 'message' => 'Admin user updated successfully.'];
                header('Location: ../dashboard/admin_users.php');
                exit;
            }
        } catch (ParseException $e) {
            $formError = $e->getMessage();
        }
    }
}

$adminNameValue = htmlspecialchars((string) ($targetAdmin->get('name') ?? ''), ENT_QUOTES, 'UTF-8');
$adminEmailValue = htmlspecialchars((string) ($targetAdmin->get('email') ?? ''), ENT_QUOTES, 'UTF-8');
$adminUsernameValue = htmlspecialchars((string) ($targetAdmin->get('username') ?? ''), ENT_QUOTES, 'UTF-8');
$adminGenderValue = (string) ($targetAdmin->get('gender') ?? 'OTH');
if (!in_array($adminGenderValue, ['MAL', 'FML', 'OTH'], true)) {
    $adminGenderValue = 'OTH';
}
$adminModeValue = ($targetAdmin->get('isViewer') ?? false) === true ? '1' : '0';

?>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard/admin_users.php">Admin Users</a></li>
                <li class="breadcrumb-item active">Edit Admin</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0">Edit Admin User</h4>
                        <a href="../dashboard/admin_users.php" class="btn btn-sm btn-outline-secondary">Back</a>
                    </div>

                    <?php if ($formError !== ''): ?>
                        <div class="alert alert-danger m-3 mb-0"><?php echo htmlspecialchars($formError); ?></div>
                    <?php endif; ?>

                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update_admin_page">

                            <div class="form-group">
                                <label for="admin_name">Name</label>
                                <input type="text" id="admin_name" name="admin_name" class="form-control" value="<?php echo $adminNameValue; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_email">Email</label>
                                <input type="email" id="admin_email" name="admin_email" class="form-control" value="<?php echo $adminEmailValue; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_username">Username</label>
                                <input type="text" id="admin_username" name="admin_username" class="form-control" value="<?php echo $adminUsernameValue; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_password">Password (optional)</label>
                                <input type="password" id="admin_password" name="admin_password" class="form-control" minlength="6" placeholder="Leave blank to keep current password">
                            </div>

                            <div class="form-group">
                                <label for="admin_gender">Gender</label>
                                <select id="admin_gender" name="admin_gender" class="form-control">
                                    <option value="MAL" <?php echo $adminGenderValue === 'MAL' ? 'selected' : ''; ?>>Male</option>
                                    <option value="FML" <?php echo $adminGenderValue === 'FML' ? 'selected' : ''; ?>>Female</option>
                                    <option value="OTH" <?php echo $adminGenderValue === 'OTH' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="admin_mode">Mode</label>
                                <select id="admin_mode" name="admin_mode" class="form-control">
                                    <option value="0" <?php echo $adminModeValue === '0' ? 'selected' : ''; ?>>Challenger</option>
                                    <option value="1" <?php echo $adminModeValue === '1' ? 'selected' : ''; ?>>Viewer</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Admin</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
