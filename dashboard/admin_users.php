<?php
require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseACL;
use Parse\ParsePush;
use Parse\ParseUser;
use Parse\ParseInstallation;
use Parse\ParseException;
use Parse\ParseAnalytics;
use Parse\ParseFile;
use Parse\ParseCloud;
use Parse\ParseClient;
use Parse\ParseSessionStorage;
use Parse\ParseGeoPoint;
session_start();

$adminUsersFlash = $_SESSION['admin_users_flash'] ?? null;
unset($_SESSION['admin_users_flash']);

function setAdminUsersFlash(string $type, string $message): void
{
    $_SESSION['admin_users_flash'] = ['type' => $type, 'message' => $message];
}

$currUser = ParseUser::getCurrentUser();
if (!$currUser) {

    header("Refresh:0; url=../index.php");
    exit;

} elseif ($currUser->get("role") !== "admin"){
    // check if the current user is an admin
    header("Refresh:0; url=../auth/logout.php");
    exit;

}

$isSuperAdmin = ($currUser->get('isSuperAdmin') ?? false) === true;
if (!$isSuperAdmin) {
    setAdminUsersFlash('danger', 'Only super admin can manage admin users.');
    header('Location: panel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'create_admin') {
        $adminName = trim($_POST['admin_name'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminUsername = trim($_POST['admin_username'] ?? '');
        $adminPassword = trim($_POST['admin_password'] ?? '');
        $adminGender = trim($_POST['admin_gender'] ?? 'OTH');
        $adminMode = trim($_POST['admin_mode'] ?? '0');

        if ($adminName === '' || $adminEmail === '' || $adminUsername === '' || $adminPassword === '') {
            setAdminUsersFlash('danger', 'All fields are required to create an admin user.');
        } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            setAdminUsersFlash('danger', 'Please provide a valid email address.');
        } elseif (strlen($adminPassword) < 6) {
            setAdminUsersFlash('danger', 'Password must be at least 6 characters.');
        } else {
            try {
                $checkUsername = new ParseQuery("_User");
                $checkUsername->equalTo('username', $adminUsername);
                $checkUsername->limit(1);
                $usernameExists = count($checkUsername->find(true)) > 0;

                $checkEmail = new ParseQuery("_User");
                $checkEmail->equalTo('email', $adminEmail);
                $checkEmail->limit(1);
                $emailExists = count($checkEmail->find(true)) > 0;

                if ($usernameExists) {
                    setAdminUsersFlash('danger', 'Username already exists. Please choose another one.');
                } elseif ($emailExists) {
                    setAdminUsersFlash('danger', 'Email already exists. Please use another email.');
                } else {
                    $sessionToken = $currUser->getSessionToken();

                    $newAdmin = new ParseUser();
                    $newAdmin->set('name', $adminName);
                    $newAdmin->setUsername($adminUsername);
                    $newAdmin->setEmail($adminEmail);
                    $newAdmin->setPassword($adminPassword);
                    $newAdmin->set('gender', in_array($adminGender, ['MAL', 'FML', 'OTH'], true) ? $adminGender : 'OTH');
                    $newAdmin->set('role', 'admin');
                    $newAdmin->set('isViewer', $adminMode === '1');
                    $newAdmin->set('isSuperAdmin', false);
                    $newAdmin->signUp(true);

                    // Parse SDK switches current user on signUp; restore the active super admin.
                    ParseUser::logOut();
                    ParseUser::become($sessionToken, true);

                    setAdminUsersFlash('success', 'Admin user created successfully.');
                }
            } catch (ParseException $e) {
                setAdminUsersFlash('danger', $e->getMessage());
            }
        }
    } elseif ($action === 'update_admin') {
        $adminId = trim($_POST['admin_id'] ?? '');
        $adminName = trim($_POST['admin_name'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminUsername = trim($_POST['admin_username'] ?? '');
        $adminPassword = trim($_POST['admin_password'] ?? '');
        $adminGender = trim($_POST['admin_gender'] ?? 'OTH');
        $adminMode = trim($_POST['admin_mode'] ?? '0');

        if ($adminId === '' || $adminName === '' || $adminEmail === '' || $adminUsername === '') {
            setAdminUsersFlash('danger', 'Name, email, and username are required to update an admin user.');
        } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            setAdminUsersFlash('danger', 'Please provide a valid email address.');
        } elseif ($adminPassword !== '' && strlen($adminPassword) < 6) {
            setAdminUsersFlash('danger', 'Password must be at least 6 characters if provided.');
        } else {
            try {
                $query = new ParseQuery("_User");
                $targetAdmin = $query->get($adminId, true);

                if (($targetAdmin->get('role') ?? '') !== 'admin') {
                    setAdminUsersFlash('danger', 'Selected user is not an admin account.');
                } elseif (($targetAdmin->get('isSuperAdmin') ?? false) === true) {
                    setAdminUsersFlash('danger', 'Super admin account cannot be edited from this page.');
                } else {
                    $checkUsername = new ParseQuery("_User");
                    $checkUsername->equalTo('username', $adminUsername);
                    $checkUsername->notEqualTo('objectId', $adminId);
                    $checkUsername->limit(1);
                    $usernameExists = count($checkUsername->find(true)) > 0;

                    $checkEmail = new ParseQuery("_User");
                    $checkEmail->equalTo('email', $adminEmail);
                    $checkEmail->notEqualTo('objectId', $adminId);
                    $checkEmail->limit(1);
                    $emailExists = count($checkEmail->find(true)) > 0;

                    if ($usernameExists) {
                        setAdminUsersFlash('danger', 'Username already exists. Please choose another one.');
                    } elseif ($emailExists) {
                        setAdminUsersFlash('danger', 'Email already exists. Please use another email.');
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
                        setAdminUsersFlash('success', 'Admin user updated successfully.');
                    }
                }
            } catch (ParseException $e) {
                setAdminUsersFlash('danger', $e->getMessage());
            }
        }
    } elseif ($action === 'delete_admin') {
        $adminId = trim($_POST['admin_id'] ?? '');

        if ($adminId === '') {
            setAdminUsersFlash('danger', 'Invalid admin selected for deletion.');
        } else {
            try {
                $query = new ParseQuery("_User");
                $targetAdmin = $query->get($adminId, true);
                $isTargetSuperAdmin = ($targetAdmin->get('isSuperAdmin') ?? false) === true;

                if (($targetAdmin->get('role') ?? '') !== 'admin') {
                    setAdminUsersFlash('danger', 'Selected user is not an admin account.');
                } elseif ($adminId === $currUser->getObjectId() || $isTargetSuperAdmin) {
                    setAdminUsersFlash('danger', 'This admin user cannot be deleted.');
                } else {
                    $targetAdmin->destroy(true);
                    setAdminUsersFlash('success', 'Admin user deleted successfully.');
                }
            } catch (ParseException $e) {
                setAdminUsersFlash('danger', $e->getMessage());
            }
        }
    }

    header('Location: admin_users.php');
    exit;

}


?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/dashboard/images/favicon.png">
    <title><?php echo $app_name;?> - Admin users</title>
    <!-- Bootstrap Core CSS -->
    <link href="../assets/dashboard/css/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->

    <link href="../assets/dashboard/css/lib/calendar2/semantic.ui.min.css" rel="stylesheet">
    <link href="../assets/dashboard/css/lib/calendar2/pignose.calendar.min.css" rel="stylesheet">
    <link href="../assets/dashboard/css/lib/owl.carousel.min.css" rel="stylesheet" />
    <link href="../assets/dashboard/css/lib/owl.theme.default.min.css" rel="stylesheet" />
    <link href="../assets/dashboard/css/helper.css" rel="stylesheet">
    <link href="../assets/dashboard/css/style.css" rel="stylesheet">
    <link href="../assets/dashboard/css/aliki.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:** -->
    <!--[if lt IE 9]>
    <script src="https:**oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https:**oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="fix-header fix-sidebar">
    <!-- Preloader - style you can find in spinners.css -->
    <!-- <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" ></circle>
        </svg>
    </div> -->
    <!-- Main wrapper  -->
    <div id="main-wrapper">

        <?php

        include '../admin/header_admin.php';
        include '../admin/left_sidebar_admin.php';
        include '../users/side_admin_users.php';

        ?>

        <!-- footer -->
        <?php include 'footer.php' ?>
        <!-- End footer -->
    </div>
    <!-- End Wrapper -->


</body>