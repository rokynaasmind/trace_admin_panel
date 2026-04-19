<?php
require '../vendor/autoload.php';
include '../Configs.php';

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseException;

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$result = ['success' => false, 'data' => 0];

try {
    switch ($action) {
        case 'registered_today':
            $query = new ParseQuery('_User');
            $query->greaterThanOrEqualToRelativeTime('createdAt', '24 hrs ago');
            $result['data'] = $query->count();
            $result['success'] = true;
            break;
            
        case 'total_users':
            $query = new ParseQuery('_User');
            $result['data'] = $query->count();
            $result['success'] = true;
            break;
            
        case 'messages':
            $query = new ParseQuery('Message');
            $query->doesNotExist('call');
            $result['data'] = $query->count();
            $result['success'] = true;
            break;
            
        case 'videos':
            $query = new ParseQuery('Video');
            $result['data'] = $query->count();
            $result['success'] = true;
            break;
            
        case 'streamings':
            $query = new ParseQuery('Streaming');
            $result['data'] = $query->count();
            $result['success'] = true;
            break;
            
        case 'challenges':
            $query = new ParseQuery('Challenge');
            $result['data'] = $query->count();
            $result['success'] = true;
            break;
            
        case 'categories':
            $query = new ParseQuery('Category');
            $result['data'] = $query->count();
            $result['success'] = true;
            break;
            
        case 'stories':
            $query = new ParseQuery('Stories');
            $result['data'] = $query->count();
            $result['success'] = true;
            break;
            
        default:
            $result['error'] = 'Invalid action';
    }
} catch (ParseException $e) {
    $result['error'] = 'Query error: ' . $e->getMessage();
    $result['success'] = false;
} catch (Exception $e) {
    $result['error'] = 'Error: ' . $e->getMessage();
    $result['success'] = false;
}

echo json_encode($result);
?>
