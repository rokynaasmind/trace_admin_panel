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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Banners Management</title>
    <link rel="stylesheet" href="../assets/dashboard/css/style.css">
</head>
<body>
<div class="container">
    <h2>Banners Management</h2>
    <?php if ($message): ?>
        <div style="color:green; font-weight:bold;"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>

    <?php if ($action === 'edit' && isset($banner)): ?>
        <h3>Edit Banner</h3>
        <form method="post" enctype="multipart/form-data">
            <label>Title:<br><input type="text" name="title" value="<?= htmlspecialchars($banner->get('title')) ?>" required></label><br><br>
            <label>Image:<br><input type="file" name="image"></label>
            <?php if ($banner->get('image')): ?>
                <br><img src="<?= $banner->get('image')->getURL() ?>" alt="Banner" style="max-width:200px;">
            <?php endif; ?><br><br>
            <button type="submit">Update</button>
            <a href="banners.php">Cancel</a>
        </form>
    <?php else: ?>
        <h3>Add New Banner</h3>
        <form method="post" enctype="multipart/form-data" action="?action=add">
            <label>Title:<br><input type="text" name="title" required></label><br><br>
            <label>Image:<br><input type="file" name="image" required></label><br><br>
            <button type="submit">Add Banner</button>
        </form>
    <?php endif; ?>

    <h3>All Banners</h3>
    <table border="1" cellpadding="8" style="width:100%; max-width:800px;">
        <tr>
            <th>Title</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($banners as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b->get('title')) ?></td>
                <td><?php if ($b->get('image')): ?><img src="<?= $b->get('image')->getURL() ?>" alt="Banner" style="max-width:120px;"> <?php endif; ?></td>
                <td>
                    <a href="?action=edit&id=<?= $b->getObjectId() ?>">Edit</a> |
                    <a href="?action=delete&id=<?= $b->getObjectId() ?>" onclick="return confirm('Delete this banner?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
