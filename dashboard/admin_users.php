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

$currUser = ParseUser::getCurrentUser();
if (!$currUser) {

    header("Refresh:0; url=../index.php");

} elseif ($currUser->get("role") !== "admin"){
    // check if the current user is an admin
    header("Refresh:0; url=../auth/logout.php");

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_admin') {
    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminUsername = trim($_POST['admin_username'] ?? '');
    $adminPassword = trim($_POST['admin_password'] ?? '');
    $adminGender = trim($_POST['admin_gender'] ?? 'OTH');

    if ($adminName === '' || $adminEmail === '' || $adminUsername === '' || $adminPassword === '') {
        $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'All fields are required to create an admin user.'];
    } elseif (strlen($adminPassword) < 6) {
        $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Password must be at least 6 characters.'];
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
                $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Username already exists. Please choose another one.'];
            } elseif ($emailExists) {
                $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => 'Email already exists. Please use another email.'];
            } else {
                $sessionToken = $currUser->getSessionToken();

                $newAdmin = new ParseUser();
                $newAdmin->set('name', $adminName);
                $newAdmin->setUsername($adminUsername);
                $newAdmin->setEmail($adminEmail);
                $newAdmin->setPassword($adminPassword);
                $newAdmin->set('gender', in_array($adminGender, ['MAL', 'FML', 'OTH']) ? $adminGender : 'OTH');
                $newAdmin->set('role', 'admin');
                $newAdmin->set('isViewer', false);
                $newAdmin->signUp(true);

                ParseUser::logOut();
                ParseUser::become($sessionToken, true);
                $_SESSION['token'] = $sessionToken;

                $_SESSION['admin_users_flash'] = ['type' => 'success', 'message' => 'Admin user created successfully and is now visible in the list.'];
            }
        } catch (ParseException $e) {
            $_SESSION['admin_users_flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
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