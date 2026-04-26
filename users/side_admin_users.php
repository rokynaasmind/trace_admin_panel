<?php

require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseUser;


session_start();

$currUser = ParseUser::getCurrentUser();
if ($currUser){

    // Store current user session token, to restore in case we create new user
    $_SESSION['token'] = $currUser -> getSessionToken();
} else {

    header("Refresh:0; url=../index.php");
}

$createAdminError = '';
$createAdminSuccess = '';

if (isset($_POST['action']) && $_POST['action'] === 'create_admin') {
    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminUsername = trim($_POST['admin_username'] ?? '');
    $adminPassword = trim($_POST['admin_password'] ?? '');
    $adminGender = trim($_POST['admin_gender'] ?? 'OTH');

    if ($adminName === '' || $adminEmail === '' || $adminUsername === '' || $adminPassword === '') {
        $createAdminError = 'All fields are required to create an admin user.';
    } elseif (strlen($adminPassword) < 6) {
        $createAdminError = 'Password must be at least 6 characters.';
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
                $createAdminError = 'Username already exists. Please choose another one.';
            } elseif ($emailExists) {
                $createAdminError = 'Email already exists. Please use another email.';
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

                // signUp changes current user in Parse SDK, so restore the admin session.
                ParseUser::logOut();
                ParseUser::become($sessionToken, true);
                $_SESSION['token'] = $sessionToken;

                $createAdminSuccess = 'Admin user created successfully and is now visible in the list.';
            }
        } catch (ParseException $e) {
            $createAdminError = $e->getMessage();
        }
    }
}

?>

<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <!-- <div class="col-md-5 align-self-center">
            <h3 class="text-white">Admin Users </h3> </div> -->
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Users</a></li>
                <li class="breadcrumb-item active">Admin Users </li>
            </ol>
        </div>
    </div>

    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start Page Content -->
        <div class="row bg-white m-l-0 m-r-0 box-shadow ">

        </div>
        <div class="row">
            <div class="col-lg">
               <div class="card">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <h4 class="m-0">Admin Users</h4>
                        <button type="button" class="btn btn-sm btn-primary" onclick="toggleCreateAdminPanel()">
                            <i class="fa fa-plus"></i> Create Admin
                        </button>
                    </div>

                    <?php if (!empty($createAdminError)): ?>
                        <div class="alert alert-danger m-3 mb-0"><?php echo htmlspecialchars($createAdminError); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($createAdminSuccess)): ?>
                        <div class="alert alert-success m-3 mb-0"><?php echo htmlspecialchars($createAdminSuccess); ?></div>
                    <?php endif; ?>

                    <div id="createAdminPanel" class="m-3 p-3 border rounded" style="display:none; background:#fafafa;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="m-0">Create Admin User</h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCreateAdminPanel(false)">Close</button>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="action" value="create_admin">

                            <div class="form-group">
                                <label for="admin_name">Name</label>
                                <input type="text" id="admin_name" name="admin_name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_email">Email</label>
                                <input type="email" id="admin_email" name="admin_email" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_username">Username</label>
                                <input type="text" id="admin_username" name="admin_username" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_password">Password</label>
                                <input type="password" id="admin_password" name="admin_password" minlength="6" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_gender">Gender</label>
                                <select id="admin_gender" name="admin_gender" class="form-control">
                                    <option value="OTH">Other</option>
                                    <option value="MAL">Male</option>
                                    <option value="FML">Female</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Create Admin</button>
                        </form>
                    </div>

                    <!--<h5 class="card-subtitle">Copy or Export CSV, Excel, PDF and Print data</h5> -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%"> 
                                <thead class="bg-light">
                                <tr>
                                    <th style="color:#65131f ;">ObjectId</th>
                                    <th style="color:#65131f ;">Name</th>
                                    <th style="color:#65131f ;">Username</th>
                                    <th style="color:#65131f ;">Avatar</th>
                                    <th style="color:#65131f ;">Gender</th>
                                    <th style="color:#65131f ;">Bithday</th>
                                    <!-- <th style="color:#65131f ;">Age</th>  -->
                                    <th style="color:#65131f ;">Mode</th>
                                    <!-- <th style="color:#65131f ;">Activation</th> -->
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                try {

                                    $currUser = ParseUser::getCurrentUser();
                                    $cuObjectID = $currUser->getObjectId();

                                    $query = new ParseQuery("_User");
                                    $query->descending('createdAt');
                                    $query->equalTo('role', 'admin');
                                    $catArray = $query->find(false);

                                    foreach ($catArray as $iValue) {
                                        // Get Parse Object
                                        $cObj = $iValue;

                                        $objectId = $cObj->getObjectId();

                                        $name = $cObj->get('name');
                                        $username = $cObj->get('username');
                                        $email = $cObj->get('email');

                                        if ($cObj->get("avatar") !== null) {

                                            $photos = $cObj->get('avatar');

                                            $profilePhotoUrl = $photos->getURL();
                                                
                                            $avatar = "<span><a href='#' onclick='showImage(\"$profilePhotoUrl\")' class=\"badge badge-info\" style=\"background:#5d0375;\">View</a></span>";

                                        } else {
                                            $avatar = "<span/><a class=\"text-warning font-weight-bold\">No Avatar</a></span>";
                                        }

                                        $gender = $cObj->get('gender');

                                        if ($gender === "MAL"){
                                            $UserGender = "Male";
                                        } else if ($gender === "FML"){
                                            $UserGender = "Female";
                                        } else {
                                            $UserGender = "Other";
                                        }

                                        $birthday= $cObj->get('birthday');
                                        if($birthday == null || $birthday == ""){
                                            $birthDate = '<span class="text-warning font-weight-bold p-5">Undefined</span>';
                                        }else{
                                            $birthDate = date_format($birthday,"d/m/Y");
                                        }

                                        // $age = $cObj->get('age');

                                        $verified = $cObj->get('emailVerified');
                                        if ($verified == false){
                                            $verification = "<span class=\"text-warning font-weight-bold\">UNVERIFIED</span>";
                                        } else {
                                            $verification = "<span class=\"text-success font-weight-bold\">VERIFED</span>";
                                        }

                                        $locaton = $cObj->get('location');
                                        if ($locaton == null){
                                            $city_location = "<span class=\"text-warning font-weight-bold\">Unavailable</span>";
                                        } else{
                                            $city_location = "<span class=\"text-info font-weight-bold\">$locaton</span>";
                                        }
                                        
                                        $mode = $cObj->get('isViewer') == false? 'Challenger' : 'Viewer';
                                        
                                        echo '
		            	
		            	        <tr>
                                    <td>'.$objectId.'</td>
                                    <td>'.$name.'</td>
                                    <td>'.$username.'</td>
                                    <td>'.$avatar.'</td>
                                    <td><span>'.$UserGender.'</span></td>
                                    <td><span>'.$birthDate.'</span></td>
                                    <td>'.$mode.'</td>
                                </tr>
                                
                                ';
                                    }
                                    // error in query
                                } catch (ParseException $e){ echo $e->getMessage(); }
                                ?>

                                </tbody>
                            </table>
                        </div>
                    </div>



                </div>
            </div>
        </div>

        <!-- End PAge Content -->
    </div>
    <!-- End Container fluid  -->
    <!-- footer -->

    <!-- End footer -->
</div>

<script>
function toggleCreateAdminPanel(forceShow) {
    var panel = document.getElementById('createAdminPanel');
    if (!panel) {
        return;
    }
    var shouldShow = typeof forceShow === 'boolean' ? forceShow : panel.style.display === 'none' || panel.style.display === '';
    panel.style.display = shouldShow ? 'block' : 'none';
}
</script>
