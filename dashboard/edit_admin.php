<?php
require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseUser;

session_start();

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
    header('Location: admin_users.php');
    exit;
}
?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/dashboard/images/favicon.png">
    <title><?php echo $app_name; ?> - Edit admin</title>
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
    include '../users/side_edit_admin.php';
    ?>

    <?php include 'footer.php'; ?>
</div>
</body>
