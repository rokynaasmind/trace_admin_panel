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

// Handle Create Coin Plan
if (isset($_POST['action']) && $_POST['action'] === 'create_plan') {
    $coins = (int)($_POST['coins'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $productKey = $_POST['product_key'] ?? '';

    if ($coins > 0 && $amount > 0) {
        try {
            $plan = ParseObject::create("CoinPlans");
            $plan->set("coins", $coins);
            $plan->set("amount", $amount);
            $plan->set("productKey", $productKey);
            $plan->set("isActive", true);
            $plan->save(true);

            echo '<script>window.location.href = "../dashboard/coin_plans.php?success=1";</script>';
            exit;
        } catch (ParseException $e) {
            $errorMsg = $e->getMessage();
        }
    }
}

// Handle Edit Coin Plan
if (isset($_POST['action']) && $_POST['action'] === 'edit_plan') {
    $planId = $_POST['plan_id'] ?? '';
    $coins = (int)($_POST['coins'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $productKey = $_POST['product_key'] ?? '';

    if ($planId && $coins > 0 && $amount > 0) {
        try {
            $query = new ParseQuery("CoinPlans");
            $plan = $query->get($planId, true);
            $plan->set("coins", $coins);
            $plan->set("amount", $amount);
            $plan->set("productKey", $productKey);
            $plan->save(true);

            echo '<script>window.location.href = "../dashboard/coin_plans.php?updated=1";</script>';
            exit;
        } catch (ParseException $e) {
            $errorMsg = $e->getMessage();
        }
    }
}

// Handle Toggle Active
if (isset($_POST['action']) && $_POST['action'] === 'toggle_active') {
    $planId = $_POST['plan_id'] ?? '';
    $newStatus = $_POST['new_status'] === '1';
    if ($planId) {
        try {
            $query = new ParseQuery("CoinPlans");
            $plan = $query->get($planId, true);
            $plan->set("isActive", $newStatus);
            $plan->save(true);
        } catch (ParseException $e) {
            $errorMsg = $e->getMessage();
        }
    }
}

// Handle Delete Plan
if (isset($_POST['action']) && $_POST['action'] === 'delete_plan') {
    $planId = $_POST['plan_id'] ?? '';
    if ($planId) {
        try {
            $query = new ParseQuery("CoinPlans");
            $plan = $query->get($planId, true);
            $plan->destroy(true);
        } catch (ParseException $e) {
            $errorMsg = $e->getMessage();
        }
    }
}

?>

<style>
    .coin-plans-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 0; flex-wrap: wrap; gap: 10px;
    }
    .coin-plans-header h2 { font-size: 22px; font-weight: 600; color: #fff; margin: 0; }
    .coin-plans-header p { color: #636e72; margin: 2px 0 0 0; font-size: 14px; }
    .btn-create-plan {
        background: #6c5ce7; color: #fff; border: none; padding: 10px 20px;
        border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500;
    }
    .btn-create-plan:hover { background: #5a4bd1; color: #fff; text-decoration: none; }
    .coin-badge { color: #f5a623; font-weight: bold; }
    .plans-table th {
        background: #f8f9fa; text-transform: uppercase; font-size: 11px;
        letter-spacing: 1px; color: #636e72; border-bottom: 2px solid #eee;
    }
    .plans-table td { vertical-align: middle; padding: 16px 12px; }
    .action-btn {
        border: none; background: none; cursor: pointer; font-size: 18px; padding: 4px 6px;
    }
    .action-btn.edit { color: #6c5ce7; }
    .action-btn.edit:hover { color: #5a4bd1; }
    .action-btn.delete { color: #e74c3c; }
    .action-btn.delete:hover { color: #c0392b; }

    .switch { position: relative; display: inline-block; width: 44px; height: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: -3px; background-color: white; transition: .3s; }
    input:checked + .slider { background-color: #6c5ce7; }
    input:checked + .slider:before { transform: translateX(20px); }
    .slider.round { border-radius: 24px; }
    .slider.round:before { border-radius: 50%; }

    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;
        color: #000;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: #fff; border-radius: 12px; padding: 30px; width: 500px; max-width: 95%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3); position: relative;
    }
    .modal-box h3 { margin-bottom: 20px; font-size: 20px; }
    .modal-box .close-btn {
        position: absolute; top: 15px; right: 20px; font-size: 24px;
        cursor: pointer; color: #999; border: none; background: none;
    }
    .modal-box .form-group { margin-bottom: 15px; }
    .modal-box label { font-weight: 500; margin-bottom: 5px; display: block; color: #555; }
    .modal-box input, .modal-box select {
        width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px;
        font-size: 14px; outline: none;
        color: #333;
    }
    .modal-box input:focus { border-color: #6c5ce7; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .modal-actions .btn-cancel {
        padding: 8px 24px; border: 1px solid #ddd; border-radius: 8px;
        background: #fff; cursor: pointer; font-size: 14px;
    }
    .modal-actions .btn-save {
        padding: 8px 24px; border: none; border-radius: 8px;
        background: #6c5ce7; color: #fff; cursor: pointer; font-size: 14px;
    }
    .checkbox-row { display: flex; align-items: center; gap: 8px; }
    .checkbox-row input[type="checkbox"] { width: auto; }
</style>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Coin System</a></li>
                <li class="breadcrumb-item active">Coin Plans</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg">
                <div class="card" style="border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                    <div class="card-body">

                        <div class="coin-plans-header">
                            <div>
                                <h2>Coin Plans</h2>
                                <p>Manage and configure coin purchase plans</p>
                            </div>
                            <button class="btn-create-plan" onclick="openCreatePlanModal()">+ Create Coin Plan</button>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Coin Plan created successfully!
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['updated'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Coin Plan updated successfully!
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($errorMsg)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table plans-table" width="100%">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Coins</th>
                                    <th>Amount ($)</th>
                                    <th>Product Key</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                try {
                                    $query = new ParseQuery("CoinPlans");
                                    $query->ascending('coins');
                                    $query->limit(500);
                                    $plans = $query->find(true);

                                    $index = 1;
                                    foreach ($plans as $plan) {
                                        $planId = $plan->getObjectId();
                                        $coins = $plan->get("coins") ?? 0;
                                        $amount = $plan->get("amount") ?? 0;
                                        $productKey = htmlspecialchars($plan->get("productKey") ?? '');
                                        $isActive = $plan->get("isActive") ?? false;
                                        $activeToggle = $isActive ? '0' : '1';

                                        echo '
                                        <tr>
                                            <td>'.$index.'</td>
                                            <td><span class="coin-badge">🪙</span> '.number_format($coins).'</td>
                                            <td>'.number_format($amount).' $</td>
                                            <td>'.$productKey.'</td>
                                            <td>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="toggle_active">
                                                    <input type="hidden" name="plan_id" value="'.$planId.'">
                                                    <input type="hidden" name="new_status" value="'.$activeToggle.'">
                                                    <label class="switch">
                                                        <input type="checkbox" '.($isActive ? 'checked' : '').' onchange="this.form.submit()">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </form>
                                            </td>
                                            <td>
                                                <button class="action-btn edit" onclick="openEditPlanModal(\''.$planId.'\', '.$coins.', '.$amount.', \''.$productKey.'\')" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <form method="post" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this plan?\')">
                                                    <input type="hidden" name="action" value="delete_plan">
                                                    <input type="hidden" name="plan_id" value="'.$planId.'">
                                                    <button type="submit" class="action-btn delete" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>';
                                        $index++;
                                    }
                                } catch (ParseException $e) {
                                    echo '<tr><td colspan="6" class="text-center text-danger">' . htmlspecialchars($e->getMessage()) . '</td></tr>';
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

<!-- Create Coin Plan Modal -->
<div class="modal-overlay" id="createPlanModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeCreatePlanModal()">&times;</button>
        <h3>Create Coin Plan</h3>
        <form method="post" action="">
            <input type="hidden" name="action" value="create_plan">
            <div class="form-group">
                <label>Coins</label>
                <input type="number" name="coins" placeholder="Number of coins" min="1" required>
            </div>
            <div class="form-group">
                <label>Amount ($)</label>
                <input type="number" name="amount" placeholder="Price in USD" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Product Key</label>
                <input type="text" name="product_key" placeholder="In-app purchase product key">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeCreatePlanModal()">Cancel</button>
                <button type="submit" class="btn-save">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Coin Plan Modal -->
<div class="modal-overlay" id="editPlanModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeEditPlanModal()">&times;</button>
        <h3>Edit Coin Plan</h3>
        <form method="post" action="">
            <input type="hidden" name="action" value="edit_plan">
            <input type="hidden" name="plan_id" id="edit_plan_id">
            <div class="form-group">
                <label>Coins</label>
                <input type="number" name="coins" id="edit_coins" min="1" required>
            </div>
            <div class="form-group">
                <label>Amount ($)</label>
                <input type="number" name="amount" id="edit_amount" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Product Key</label>
                <input type="text" name="product_key" id="edit_product_key">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditPlanModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreatePlanModal() {
    document.getElementById('createPlanModal').classList.add('active');
}
function closeCreatePlanModal() {
    document.getElementById('createPlanModal').classList.remove('active');
}
function openEditPlanModal(id, coins, amount, productKey) {
    document.getElementById('edit_plan_id').value = id;
    document.getElementById('edit_coins').value = coins;
    document.getElementById('edit_amount').value = amount;
    document.getElementById('edit_product_key').value = productKey;
    document.getElementById('editPlanModal').classList.add('active');
}
function closeEditPlanModal() {
    document.getElementById('editPlanModal').classList.remove('active');
}
</script>
