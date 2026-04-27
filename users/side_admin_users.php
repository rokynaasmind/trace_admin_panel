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

$potentialAdminCandidates = [];
try {
    $userQuery = new ParseQuery("_User");
    $userQuery->limit(1000);
    $allUsers = $userQuery->find(true);
    foreach ($allUsers as $candidate) {
        $candidateRole = strtolower(trim((string) ($candidate->get('role') ?? '')));
        $isCandidateSuperAdmin = ($candidate->get('isSuperAdmin') ?? false) === true;
        if ($candidateRole === 'admin' || $isCandidateSuperAdmin) {
            continue;
        }
        $potentialAdminCandidates[] = $candidate;
    }
} catch (ParseException $e) {
    // fallback to empty candidate list
}

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

                            <?php if (empty($potentialAdminCandidates)): ?>
                                <div class="alert alert-warning">No eligible users found to promote to admin.</div>
                            <?php else: ?>
                                <div class="form-group">
                                    <label for="existing_user_id">Select user to promote</label>
                                    <select id="existing_user_id" name="existing_user_id" class="form-control" required>
                                        <option value="">Choose a user</option>
                                        <?php foreach ($potentialAdminCandidates as $candidate):
                                            $candidateId = htmlspecialchars($candidate->getObjectId(), ENT_QUOTES, 'UTF-8');
                                            $candidateName = htmlspecialchars((string)$candidate->get('name') ?: $candidate->get('username') ?: $candidate->get('email'), ENT_QUOTES, 'UTF-8');
                                            $candidateEmail = htmlspecialchars((string)($candidate->get('email') ?? ''), ENT_QUOTES, 'UTF-8');
                                            $candidateUsername = htmlspecialchars((string)($candidate->get('username') ?? ''), ENT_QUOTES, 'UTF-8');
                                        ?>
                                            <option value="<?php echo $candidateId; ?>"><?php echo $candidateName; ?><?php echo $candidateUsername ? ' (' . $candidateUsername . ')' : ''; ?><?php echo $candidateEmail ? ' - ' . $candidateEmail : ''; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">Promote to Admin</button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!--<h5 class="card-subtitle">Copy or Export CSV, Excel, PDF and Print data</h5> -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example23" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%"> 
                                <thead class="bg-light">
                                <tr>
                                    <th style="color:#65131f ;">ID</th>
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
                                        if ($isProtected) {
                                            $editLink = '<span class="badge badge-secondary mr-1">Protected</span>';
                                        } else {
                                            $editLink = '<a href="../dashboard/edit_admin.php?objectId=' . urlencode($objectId) . '" class="btn btn-sm btn-warning mr-1"><i class="fa fa-edit"></i> Edit</a>';
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
                                            <td>'.$uid.'</td>
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
