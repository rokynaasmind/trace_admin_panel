
<?php
require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseFile;
use Parse\ParseException;

$action = $_GET['action'] ?? '';
$message = '';

// Handle Create
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $image = $_FILES['image'] ?? null;
    if ($title && $image && $image['tmp_name']) {
        try {
            $banner = new ParseObject('Banner');
            $banner->set('title', $title);
            $file = ParseFile::createFromFile($image['tmp_name'], $image['name']);
            $banner->set('image', $file);
            $banner->save();
            $message = 'Banner added successfully!';
        } catch (ParseException $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    } else {
        $message = 'Title and image are required.';
    }
}

// Handle Delete
if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $query = new ParseQuery('Banner');
        $banner = $query->get($_GET['id']);
        $banner->destroy();
        $message = 'Banner deleted.';
    } catch (ParseException $e) {
        $message = 'Error: ' . $e->getMessage();
    }
}

// Handle Edit
if ($action === 'edit' && isset($_GET['id'])) {
    $query = new ParseQuery('Banner');
    $banner = $query->get($_GET['id']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'] ?? '';
        $image = $_FILES['image'] ?? null;
        if ($title) {
            $banner->set('title', $title);
        }
        if ($image && $image['tmp_name']) {
            $file = ParseFile::createFromFile($image['tmp_name'], $image['name']);
            $banner->set('image', $file);
        }
        try {
            $banner->save();
            $message = 'Banner updated!';
        } catch (ParseException $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    }
}

// Fetch all banners
$query = new ParseQuery('Banner');
$query->descending('createdAt');
$banners = $query->find();

// Layout includes
include '../admin/header_admin.php';
include '../admin/left_sidebar_admin.php';
?>
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 col-md-10 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Banners Management</h2>
                        <?php if ($message): ?>
                            <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
                        <?php endif; ?>

                        <?php if ($action === 'edit' && isset($banner)): ?>
                            <h4>Edit Banner</h4>
                            <form method="post" enctype="multipart/form-data" class="form-horizontal">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($banner->get('title')) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Image</label>
                                    <input type="file" name="image" class="form-control">
                                    <?php if ($banner->get('image')): ?>
                                        <br><img src="<?= $banner->get('image')->getURL() ?>" alt="Banner" style="max-width:200px;">
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="banners.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        <?php else: ?>
                            <h4>Add New Banner</h4>
                            <form method="post" enctype="multipart/form-data" action="?action=add" class="form-horizontal">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Image</label>
                                    <input type="file" name="image" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success">Add Banner</button>
                            </form>
                        <?php endif; ?>

                        <h4 class="mt-4">All Banners</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($banners as $b): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($b->get('title')) ?></td>
                                        <td><?php if ($b->get('image')): ?><img src="<?= $b->get('image')->getURL() ?>" alt="Banner" style="max-width:120px;"> <?php endif; ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?= $b->getObjectId() ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="?action=delete&id=<?= $b->getObjectId() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this banner?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../dashboard/footer.php'; ?>
