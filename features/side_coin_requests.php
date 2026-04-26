<?php

require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseException;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;

session_start();

$currUser = ParseUser::getCurrentUser();
if ($currUser) {
    $_SESSION['token'] = $currUser->getSessionToken();
} else {
    header("Refresh:0; url=../index.php");
}

// Handle Approve Request
if (isset($_POST['action']) && $_POST['action'] === 'approve_request') {
    $requestId = $_POST['request_id'] ?? '';
    if ($requestId) {
        try {
            $query = new ParseQuery("CoinRequests");
            $query->includeKey("trader");
            $query->includeKey("user");
            $query->includeKey("plan");
            $request = $query->get($requestId, true);

            $currentStatus = $request->get("status") ?? 'pending';
            if ($currentStatus !== 'pending') {
                $errorMsg = "This request is already " . $currentStatus . ".";
            } else {
                $coinAmount = $request->get("coinAmount") ?? 0;
                $trader = $request->get("trader");
                $user = $request->get("user");

                if ($trader && $user && $coinAmount > 0) {
                    $traderBalance = $trader->get("coinBalance") ?? 0;

                    if ($traderBalance >= $coinAmount) {
                        // Deduct from trader
                        $trader->set("coinBalance", $traderBalance - $coinAmount);
                        $trader->set("spentCoins", ($trader->get("spentCoins") ?? 0) + $coinAmount);
                        $trader->save(true);

                        // Add coins to user
                        $userObj = $request->get("user");
                        $userCoins = $userObj->get("coins") ?? 0;
                        $userObj->set("coins", $userCoins + $coinAmount);
                        $userObj->save(true);

                        // Update request status
                        $request->set("status", "approved");
                        $request->set("approvedAt", new DateTime());
                        $request->set("approvedBy", $currUser);
                        $request->save(true);

                        $successMsg = "Request approved! " . number_format($coinAmount) . " coins transferred to user.";
                    } else {
                        $errorMsg = "Trader does not have enough coins. Balance: " . number_format($traderBalance);
                    }
                } else {
                    $errorMsg = "Invalid request data. Unable to approve this request.";
                }
            }
        } catch (ParseException $e) {
            $errorMsg = $e->getMessage();
        }
    }
}

// Handle Reject Request
if (isset($_POST['action']) && $_POST['action'] === 'reject_request') {
    $requestId = $_POST['request_id'] ?? '';
    if ($requestId) {
        try {
            $query = new ParseQuery("CoinRequests");
            $request = $query->get($requestId, true);
            $currentStatus = $request->get("status") ?? 'pending';
            if ($currentStatus !== 'pending') {
                $errorMsg = "This request is already " . $currentStatus . ".";
            } else {
                $request->set("status", "rejected");
                $request->set("rejectedAt", new DateTime());
                $request->set("rejectedBy", $currUser);
                $request->save(true);
                $successMsg = "Request has been rejected.";
            }
        } catch (ParseException $e) {
            $errorMsg = $e->getMessage();
        }
    }
}

$filterStatus = $_GET['status'] ?? 'all';
$filterTraderId = $_GET['trader_id'] ?? '';

?>

<style>
    .requests-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 0; flex-wrap: wrap; gap: 10px;
    }
    .requests-header h2 { font-size: 22px; font-weight: 600; color: #fff; margin: 0; }
    .requests-header p { color: #636e72; margin: 2px 0 0 0; font-size: 14px; }
    .filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-tab {
        padding: 6px 16px; border-radius: 20px; font-size: 13px;
        border: 1px solid #ddd; background: #fff; color: #636e72;
        cursor: pointer; text-decoration: none;
    }
    .filter-tab:hover { border-color: #6c5ce7; color: #6c5ce7; text-decoration: none; }
    .filter-tab.active { background: #6c5ce7; color: #fff; border-color: #6c5ce7; }
    .status-badge {
        padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;
    }
    .status-pending { background: #ffeaa7; color: #d68910; }
    .status-approved { background: #d5f5e3; color: #27ae60; }
    .status-rejected { background: #fadbd8; color: #e74c3c; }
    .coin-icon { color: #f5a623; font-weight: bold; }
    .user-cell { display: flex; align-items: center; gap: 10px; }
    .user-cell img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
    .user-cell .user-info { display: flex; flex-direction: column; }
    .user-cell .user-name { font-weight: 600; font-size: 13px; }
    .user-cell .user-handle { color: #636e72; font-size: 11px; }
    .btn-approve {
        background: #00b894; color: #fff; border: none; padding: 6px 14px;
        border-radius: 6px; cursor: pointer; font-size: 12px; margin-right: 4px;
    }
    .btn-approve:hover { background: #00a381; }
    .btn-reject {
        background: #e74c3c; color: #fff; border: none; padding: 6px 14px;
        border-radius: 6px; cursor: pointer; font-size: 12px;
    }
    .btn-reject:hover { background: #c0392b; }
    .search-box-custom {
        border: 1px solid #ddd; border-radius: 8px; padding: 8px 14px;
        font-size: 14px; outline: none; width: 200px;
    }
</style>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Coin System</a></li>
                <li class="breadcrumb-item active">Coin Requests</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg">
                <div class="card" style="border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                    <div class="card-body">

                        <div class="requests-header">
                            <div>
                                <h2>Coin Requests</h2>
                                <p>Users request coins from traders. Manage approval and rejection here.</p>
                            </div>
                            <input type="text" class="search-box-custom" id="searchRequests" placeholder="Search requests...">
                        </div>

                        <div class="filter-tabs" style="margin-bottom: 20px;">
                            <?php
                            $baseUrl = "../dashboard/coin_requests.php";
                            if ($filterTraderId) $baseUrl .= "?trader_id=" . urlencode($filterTraderId) . "&";
                            else $baseUrl .= "?";
                            ?>
                            <a href="<?php echo $baseUrl; ?>status=all" class="filter-tab <?php echo $filterStatus === 'all' ? 'active' : ''; ?>">All</a>
                            <a href="<?php echo $baseUrl; ?>status=pending" class="filter-tab <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">Pending</a>
                            <a href="<?php echo $baseUrl; ?>status=approved" class="filter-tab <?php echo $filterStatus === 'approved' ? 'active' : ''; ?>">Approved</a>
                            <a href="<?php echo $baseUrl; ?>status=rejected" class="filter-tab <?php echo $filterStatus === 'rejected' ? 'active' : ''; ?>">Rejected</a>
                        </div>

                        <?php if (isset($successMsg)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($successMsg); ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($errorMsg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($errorMsg); ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table" id="coinRequestsTable" width="100%">
                                <thead>
                                <tr style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; color: #636e72;">
                                    <th>Request ID</th>
                                    <th>User (Requester)</th>
                                    <th>Trader</th>
                                    <th>Coin Plan</th>
                                    <th>Coins Requested</th>
                                    <th>Amount ($)</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                try {
                                    $query = new ParseQuery("CoinRequests");
                                    $query->descending('createdAt');
                                    $query->includeKey("user");
                                    $query->includeKey("trader");
                                    $query->includeKey("trader.user");
                                    $query->includeKey("plan");
                                    $query->limit(1500);

                                    if ($filterStatus !== 'all') {
                                        $query->equalTo("status", $filterStatus);
                                    }

                                    $requests = $query->find(true);

                                    foreach ($requests as $req) {
                                        $traderObj = $req->get("trader");
                                        if ($filterTraderId && (!$traderObj || $traderObj->getObjectId() !== $filterTraderId)) {
                                            continue;
                                        }

                                        $reqId = $req->getObjectId();

                                        // Requester (user)
                                        $reqUser = $req->get("user");
                                        $reqUserName = $reqUser ? htmlspecialchars($reqUser->get("name") ?? 'Unknown') : 'Unknown';
                                        $reqUserHandle = $reqUser ? htmlspecialchars($reqUser->get("username") ?? '') : '';
                                        $reqUserAvatar = '';
                                        if ($reqUser && $reqUser->get("avatar")) {
                                            $avatarFile = $reqUser->get("avatar");
                                            if (is_object($avatarFile) && method_exists($avatarFile, 'getURL')) {
                                                $reqUserAvatar = $avatarFile->getURL();
                                            } else {
                                                $reqUserAvatar = (string)$avatarFile;
                                            }
                                        }

                                        // Trader
                                        $traderUser = null;
                                        $traderName = 'Unknown';
                                        $traderHandle = '';
                                        if ($traderObj) {
                                            $traderUser = $traderObj->get("user");
                                            if ($traderUser) {
                                                $traderName = htmlspecialchars($traderUser->get("name") ?? 'Trader');
                                                $traderHandle = htmlspecialchars($traderUser->get("username") ?? '');
                                            }
                                        }

                                        // Coin Plan
                                        $plan = $req->get("plan");
                                        $planCoins = $plan ? $plan->get("coins") : ($req->get("coinAmount") ?? 0);
                                        $planAmount = $plan ? $plan->get("amount") : ($req->get("amount") ?? 0);
                                        $coinAmount = $req->get("coinAmount") ?? $planCoins;

                                        // Status
                                        $status = $req->get("status") ?? 'pending';
                                        $statusClass = 'status-' . $status;

                                        // Date
                                        $createdAt = $req->getCreatedAt();
                                        $createdDate = $createdAt ? $createdAt->format("M d, Y h:i A") : '';

                                        $reqUserAvatarHtml = $reqUserAvatar
                                            ? '<img src="'.htmlspecialchars($reqUserAvatar).'" alt="" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">'
                                            : '<div style="width: 36px; height: 36px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 16px;"><i class="fa fa-user"></i></div>';

                                        echo '
                                        <tr>
                                            <td style="font-size:12px; color:#636e72;">'.htmlspecialchars(substr($reqId, 0, 10)).'</td>
                                            <td>
                                                <div class="user-cell">
                                                    '.$reqUserAvatarHtml.'
                                                    <div class="user-info">
                                                        <span class="user-name">'.$reqUserName.'</span>
                                                        <span class="user-handle">@'.$reqUserHandle.'</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <span class="user-name">'.$traderName.'</span>
                                                    <span class="user-handle" style="color:#636e72; font-size:11px;">@'.$traderHandle.'</span>
                                                </div>
                                            </td>
                                            <td><span class="coin-icon">🪙</span> '.number_format($planCoins).'</td>
                                            <td><span class="coin-icon">🪙</span> '.number_format($coinAmount).'</td>
                                            <td>'.number_format($planAmount).' $</td>
                                            <td><span class="status-badge '.$statusClass.'">'.ucfirst($status).'</span></td>
                                            <td style="font-size:12px;">'.$createdDate.'</td>
                                            <td>';

                                        if ($status === 'pending') {
                                            echo '
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="approve_request">
                                                    <input type="hidden" name="request_id" value="'.$reqId.'">
                                                    <button type="submit" class="btn-approve" onclick="return confirm(\'Approve this request? Coins will be transferred.\')">
                                                        <i class="fa fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="reject_request">
                                                    <input type="hidden" name="request_id" value="'.$reqId.'">
                                                    <button type="submit" class="btn-reject" onclick="return confirm(\'Reject this request?\')">
                                                        <i class="fa fa-times"></i> Reject
                                                    </button>
                                                </form>';
                                        } else {
                                            echo '<span style="color:#b2bec3; font-size:12px;">Completed</span>';
                                        }

                                        echo '</td></tr>';
                                    }

                                    if (empty($requests)) {
                                        echo '<tr><td colspan="9" class="text-center" style="padding:40px; color:#b2bec3;">No coin requests found</td></tr>';
                                    }

                                } catch (ParseException $e) {
                                    echo '<tr><td colspan="9" class="text-center text-danger">' . htmlspecialchars($e->getMessage()) . '</td></tr>';
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

<script>
document.getElementById('searchRequests').addEventListener('keyup', function() {
    var value = this.value.toLowerCase();
    var rows = document.querySelectorAll('#coinRequestsTable tbody tr');
    rows.forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>
