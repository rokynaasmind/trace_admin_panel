<?php

use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseUser;

$createAdminError = '';
$createAdminSuccess = '';

if (!empty($adminUsersFlash) && is_array($adminUsersFlash)) {
    $createAdminError = ($adminUsersFlash['type'] ?? '') === 'danger' ? ($adminUsersFlash['message'] ?? '') : '';
    $createAdminSuccess = ($adminUsersFlash['type'] ?? '') === 'success' ? ($adminUsersFlash['message'] ?? '') : '';
}

$currUser = ParseUser::getCurrentUser();
$cuObjectID = $currUser ? $currUser->getObjectId() : '';

function normalize_user_role($role): string
{
    return strtolower(trim((string) ($role ?? '')));
}

?>

<style>
    .admin-create-form label {
        color: #333;
    }
</style>

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
                        <form method="post" action="" class="admin-create-form">
                            <input type="hidden" name="action" value="create_admin">

                            <div class="form-group">
                                <label for="admin_name">Name</label>
                                <input type="text" placeholder="full name" id="admin_name" name="admin_name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_email">Email</label>
                                <input type="email" placeholder="valid email" id="admin_email" name="admin_email" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_username">Username</label>
                                <input type="text" placeholder="username" id="admin_username" name="admin_username" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_password">Password</label>
                                <input type="password" placeholder="password" id="admin_password" name="admin_password" minlength="6" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="admin_gender">Gender</label>
                                <select id="admin_gender" name="admin_gender" class="form-control">
                                    <option value="OTH">Other</option>
                                    <option value="MAL">Male</option>
                                    <option value="FML">Female</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="admin_mode">Mode</label>
                                <select id="admin_mode" name="admin_mode" class="form-control">
                                    <option value="0">Challenger</option>
                                    <option value="1">Viewer</option>
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
                                    <th style="color:#65131f ;">Email</th>
                                    <th style="color:#65131f ;">Avatar</th>
                                    <th style="color:#65131f ;">Gender</th>
                                    <th style="color:#65131f ;">Bithday</th>
                                    <!-- <th style="color:#65131f ;">Age</th>  -->
                                    <th style="color:#65131f ;">Mode</th>
                                    <th style="color:#65131f ;">Actions</th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                try {

                                    $query = new ParseQuery("_User");
                                    $query->descending('createdAt');
                                    $query->limit(1500);
                                    $catArray = $query->find(true);

                                    foreach ($catArray as $iValue) {
                                        // Get Parse Object
                                        $cObj = $iValue;

                                        if (normalize_user_role($cObj->get('role')) !== 'admin') {
                                            continue;
                                        }

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
                                        
                                        $mode = $cObj->get('isViewer') == false ? 'Challenger' : 'Viewer';
                                        $isProtected = $cObj->getObjectId() === $cuObjectID || ($cObj->get('isSuperAdmin') ?? false) === true;

                                        $safeObjectId = htmlspecialchars($objectId, ENT_QUOTES, 'UTF-8');
                                        $safeName = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
                                        $safeUsername = htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8');
                                        $safeEmail = htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8');
                                        $genderValue = in_array($gender, ['MAL', 'FML', 'OTH'], true) ? $gender : 'OTH';
                                        $safeGender = htmlspecialchars((string) $genderValue, ENT_QUOTES, 'UTF-8');
                                        $modeValue = $cObj->get('isViewer') == true ? '1' : '0';

                                        if ($isProtected) {
                                            $editLink = '<span class="badge badge-secondary mr-1">Protected</span>';
                                        } else {
                                            $editLink = '<button type="button" class="btn btn-sm btn-warning mr-1" '
                                                . 'data-admin-id="' . $safeObjectId . '" '
                                                . 'data-admin-name="' . $safeName . '" '
                                                . 'data-admin-username="' . $safeUsername . '" '
                                                . 'data-admin-email="' . $safeEmail . '" '
                                                . 'data-admin-gender="' . $safeGender . '" '
                                                . 'data-admin-mode="' . $modeValue . '" '
                                                . 'onclick="openEditAdminModal(this)"><i class="fa fa-edit"></i> Edit</button>';
                                        }

                                        $deleteAction = '';
                                        if ($isProtected) {
                                            $deleteAction = '';
                                        } else {
                                            $deleteAction = '<form method="post" action="" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this admin user?\')">'
                                                . '<input type="hidden" name="action" value="delete_admin">'
                                                . '<input type="hidden" name="admin_id" value="' . $safeObjectId . '">'
                                                . '<button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Delete</button>'
                                                . '</form>';
                                        }
                                        $actions = $editLink . $deleteAction;
                                        
                                        echo '
		            	
		            	        <tr>
                                            <td>'.$safeObjectId.'</td>
                                            <td>'.$safeName.'</td>
                                            <td>'.$safeUsername.'</td>
                                            <td>'.$safeEmail.'</td>
                                    <td>'.$avatar.'</td>
                                    <td><span>'.$UserGender.'</span></td>
                                    <td><span>'.$birthDate.'</span></td>
                                    <td>'.$mode.'</td>
                                    <td>'.$actions.'</td>
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

<div class="modal fade" id="editAdminModal" tabindex="-1" role="dialog" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="update_admin">
                <input type="hidden" id="edit_admin_id" name="admin_id" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="editAdminModalLabel">Edit Admin User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_admin_name">Name</label>
                        <input type="text" id="edit_admin_name" name="admin_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_admin_email">Email</label>
                        <input type="email" id="edit_admin_email" name="admin_email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_admin_username">Username</label>
                        <input type="text" id="edit_admin_username" name="admin_username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_admin_password">Password (optional)</label>
                        <input type="password" id="edit_admin_password" name="admin_password" class="form-control" minlength="6" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="form-group">
                        <label for="edit_admin_gender">Gender</label>
                        <select id="edit_admin_gender" name="admin_gender" class="form-control">
                            <option value="OTH">Other</option>
                            <option value="MAL">Male</option>
                            <option value="FML">Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_admin_mode">Mode</label>
                        <select id="edit_admin_mode" name="admin_mode" class="form-control">
                            <option value="0">Challenger</option>
                            <option value="1">Viewer</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
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

function openEditAdminModal(button) {
    document.getElementById('edit_admin_id').value = button.getAttribute('data-admin-id') || '';
    document.getElementById('edit_admin_name').value = button.getAttribute('data-admin-name') || '';
    document.getElementById('edit_admin_username').value = button.getAttribute('data-admin-username') || '';
    document.getElementById('edit_admin_email').value = button.getAttribute('data-admin-email') || '';
    document.getElementById('edit_admin_gender').value = button.getAttribute('data-admin-gender') || 'OTH';
    document.getElementById('edit_admin_mode').value = button.getAttribute('data-admin-mode') || '0';
    document.getElementById('edit_admin_password').value = '';

    if (window.jQuery && window.jQuery('#editAdminModal').modal) {
        window.jQuery('#editAdminModal').modal('show');
    }
}
</script>
