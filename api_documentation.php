<?php
/**
 * API Documentation & Configuration
 * Shows all available APIs and how to connect
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>📡 API Documentation & Connection Guide</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 16px; }
        .section { margin-bottom: 40px; padding: 25px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; }
        h2 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; margin-bottom: 15px; }
        h3 { color: #333; margin-top: 15px; margin-bottom: 10px; }
        .api-endpoint {
            background: white;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin: 15px 0;
            border-radius: 3px;
        }
        .method { display: inline-block; padding: 3px 8px; border-radius: 3px; font-weight: bold; margin-right: 10px; font-size: 12px; }
        .get { background: #61affe; color: white; }
        .post { background: #49cc90; color: white; }
        .put { background: #fca130; color: white; }
        .delete { background: #f93e3e; color: white; }
        .code-block {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 10px 0;
            border-left: 3px solid #667eea;
        }
        .credential { background: white; padding: 12px; margin: 10px 0; border-left: 4px solid #667eea; border-radius: 3px; }
        .label { font-weight: bold; color: #333; }
        .value { color: #666; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #667eea; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f9f9f9; }
        .example { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #0c5460; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #155724; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #856404; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #721c24; }
        button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 5px; }
        button:hover { background: #764ba2; }
        .copy-btn { padding: 5px 10px; font-size: 12px; }
        hr { border: none; border-top: 1px solid #ddd; margin: 30px 0; }
        .note { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #2196F3; }
    </style>
</head>
<body>

<div class="container">
    <h1>📡 API Documentation & Connection Guide</h1>
    <p class="subtitle">Complete guide to connect your mobile/web apps to Trace Admin Panel backend</p>

    <!-- ==================== SECTION 1: SERVER & CREDENTIALS ==================== -->
    <div class="section">
        <h2>🔐 Server & Credentials</h2>
        <p>এই credentials ব্যবহার করে আপনার app কে backend এর সাথে connect করুন:</p>

        <div class="credential">
            <div class="label">📡 API Server URL:</div>
            <div class="value">https://parseapi.back4app.com/</div>
        </div>

        <div class="credential">
            <div class="label">🆔 Application ID:</div>
            <div class="value">NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b</div>
        </div>

        <div class="credential">
            <div class="label">🔑 REST API Key (Client-side):</div>
            <div class="value">X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt</div>
        </div>

        <div class="credential">
            <div class="label">🔐 Master Key (Server-side only - SECRET!):</div>
            <div class="value">cx30LCUA8mfrKhS88Zetjo5PU5syyMk2Vh49n54u</div>
        </div>

        <div class="warning">
            <strong>⚠️ গুরুত্বপূর্ণ:</strong> Master Key কখনো client-side apps এ use করবেন না! শুধু সার্ভার-side এ ব্যবহার করুন।
        </div>
    </div>

    <!-- ==================== SECTION 2: AVAILABLE APIs ==================== -->
    <div class="section">
        <h2>🔌 Available APIs</h2>

        <!-- User APIs -->
        <h3>👤 User Management</h3>
        <div class="api-endpoint">
            <span class="method post">POST</span> <strong>/parse/classes/_User</strong>
            <p>নতুন user create করুন (Sign Up)</p>
            <div class="code-block">
{
  "username": "john_doe",
  "password": "SecurePass123",
  "email": "john@example.com",
  "role": "user"
}
            </div>
        </div>

        <div class="api-endpoint">
            <span class="method post">POST</span> <strong>/parse/login</strong>
            <p>User login করুন</p>
            <div class="code-block">
{
  "username": "john_doe",
  "password": "SecurePass123"
}
            </div>
        </div>

        <div class="api-endpoint">
            <span class="method get">GET</span> <strong>/parse/classes/_User/{objectId}</strong>
            <p>Specific user এর details পান</p>
        </div>

        <div class="api-endpoint">
            <span class="method put">PUT</span> <strong>/parse/classes/_User/{objectId}</strong>
            <p>User profile update করুন</p>
            <div class="code-block">
{
  "email": "newemail@example.com",
  "profile": { "bio": "Hello world" }
}
            </div>
        </div>

        <hr>

        <!-- Posts/Content APIs -->
        <h3>📝 Posts & Content</h3>
        <div class="api-endpoint">
            <span class="method post">POST</span> <strong>/parse/classes/Posts</strong>
            <p>নতুন post create করুন</p>
            <div class="code-block">
{
  "content": "Hello everyone!",
  "author": "pointer to _User",
  "likes": 0,
  "comments": 0
}
            </div>
        </div>

        <div class="api-endpoint">
            <span class="method get">GET</span> <strong>/parse/classes/Posts</strong>
            <p>সব posts fetch করুন (pagination support)</p>
        </div>

        <div class="api-endpoint">
            <span class="method get">GET</span> <strong>/parse/classes/Posts?where={"author":"..."}</strong>
            <p>Specific user এর সব posts</p>
        </div>

        <hr>

        <!-- Comments API -->
        <h3>💬 Comments</h3>
        <div class="api-endpoint">
            <span class="method post">POST</span> <strong>/parse/classes/Comments</strong>
            <p>Post এ comment করুন</p>
            <div class="code-block">
{
  "content": "Great post!",
  "author": "pointer to _User",
  "post": "pointer to Posts"
}
            </div>
        </div>

        <hr>

        <!-- Followers API -->
        <h3>👥 Followers/Following</h3>
        <div class="api-endpoint">
            <span class="method post">POST</span> <strong>/parse/classes/Followers</strong>
            <p>কাউকে follow করুন</p>
            <div class="code-block">
{
  "follower": "pointer to _User",
  "following": "pointer to _User"
}
            </div>
        </div>

        <hr>

        <!-- Likes API -->
        <h3>❤️ Likes</h3>
        <div class="api-endpoint">
            <span class="method post">POST</span> <strong>/parse/classes/Likes</strong>
            <p>Post কে like করুন</p>
            <div class="code-block">
{
  "user": "pointer to _User",
  "post": "pointer to Posts"
}
            </div>
        </div>

        <div class="api-endpoint">
            <span class="method delete">DELETE</span> <strong>/parse/classes/Likes/{objectId}</strong>
            <p>Like remove করুন</p>
        </div>

        <hr>

        <!-- Messages API -->
        <h3>💌 Direct Messages</h3>
        <div class="api-endpoint">
            <span class="method post">POST</span> <strong>/parse/classes/Messages</strong>
            <p>নতুন message পাঠান</p>
            <div class="code-block">
{
  "sender": "pointer to _User",
  "receiver": "pointer to _User",
  "content": "Hello!",
  "read": false
}
            </div>
        </div>

        <hr>

        <!-- Notifications API -->
        <h3>🔔 Notifications</h3>
        <div class="api-endpoint">
            <span class="method get">GET</span> <strong>/parse/classes/Notifications</strong>
            <p>সব notifications fetch করুন</p>
        </div>

        <div class="api-endpoint">
            <span class="method put">PUT</span> <strong>/parse/classes/Notifications/{objectId}</strong>
            <p>Notification mark as read</p>
            <div class="code-block">
{
  "read": true
}
            </div>
        </div>

    </div>

    <!-- ==================== SECTION 3: HTTP HEADERS ==================== -->
    <div class="section">
        <h2>📋 Required HTTP Headers</h2>
        <p>প্রতিটি API request এর সাথে এই headers include করুন:</p>

        <table>
            <tr>
                <th>Header Name</th>
                <th>Value</th>
                <th>Required</th>
            </tr>
            <tr>
                <td>X-Parse-Application-Id</td>
                <td>NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b</td>
                <td>✅ Always</td>
            </tr>
            <tr>
                <td>X-Parse-REST-API-Key</td>
                <td>X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt</td>
                <td>✅ Client-side</td>
            </tr>
            <tr>
                <td>X-Parse-Master-Key</td>
                <td>cx30LCUA8mfrKhS88Zetjo5PU5syyMk2Vh49n54u</td>
                <td>✅ Server-side only</td>
            </tr>
            <tr>
                <td>Content-Type</td>
                <td>application/json</td>
                <td>✅ For POST/PUT</td>
            </tr>
            <tr>
                <td>X-Parse-Session-Token</td>
                <td>User session token</td>
                <td>For authenticated requests</td>
            </tr>
        </table>
    </div>

    <!-- ==================== SECTION 4: EXAMPLE REQUESTS ==================== -->
    <div class="section">
        <h2>📌 Example Requests</h2>

        <h3>1️⃣ User Sign Up</h3>
        <div class="code-block">
curl -X POST https://parseapi.back4app.com/parse/classes/_User \
  -H "X-Parse-Application-Id: NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b" \
  -H "X-Parse-REST-API-Key: X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "password": "SecurePass123",
    "email": "john@example.com"
  }'
        </div>

        <h3>2️⃣ User Login</h3>
        <div class="code-block">
curl -X POST https://parseapi.back4app.com/parse/login \
  -H "X-Parse-Application-Id: NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b" \
  -H "X-Parse-REST-API-Key: X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "password": "SecurePass123"
  }'
        </div>

        <h3>3️⃣ Create Post</h3>
        <div class="code-block">
curl -X POST https://parseapi.back4app.com/parse/classes/Posts \
  -H "X-Parse-Application-Id: NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b" \
  -H "X-Parse-REST-API-Key: X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt" \
  -H "X-Parse-Session-Token: YOUR_SESSION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Hello world!",
    "author": {"__type":"Pointer","className":"_User","objectId":"AUTHOR_ID"}
  }'
        </div>

        <h3>4️⃣ Get All Posts</h3>
        <div class="code-block">
curl -X GET "https://parseapi.back4app.com/parse/classes/Posts?limit=10&skip=0" \
  -H "X-Parse-Application-Id: NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b" \
  -H "X-Parse-REST-API-Key: X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt"
        </div>

    </div>

    <!-- ==================== SECTION 5: SDK USAGE ==================== -->
    <div class="section">
        <h2>⚙️ Using Parse SDKs</h2>

        <h3>JavaScript/Node.js</h3>
        <div class="code-block">
const Parse = require('parse/node');

Parse.initialize('NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b', 'X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt');
Parse.serverURL = 'https://parseapi.back4app.com/';

// Create user
const user = new Parse.User();
user.set('username', 'john_doe');
user.set('password', 'SecurePass123');
user.set('email', 'john@example.com');
await user.signUp();
        </div>

        <h3>iOS (Swift)</h3>
        <div class="code-block">
import Parse

Parse.initialize(with: ParseClientConfiguration(block: { (configuration: inout ParseClientConfiguration) in
    configuration.applicationId = "NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b"
    configuration.clientKey = "X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt"
    configuration.server = "https://parseapi.back4app.com"
}))

let user = PFUser()
user.username = "john_doe"
user.password = "SecurePass123"
user.email = "john@example.com"
user.signUpInBackground { (succeeded: Bool, error: Error?) in
    // Handle signup
}
        </div>

        <h3>Android (Java)</h3>
        <div class="code-block">
Parse.initialize(new Parse.Configuration.Builder(this)
    .applicationId("NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b")
    .clientKey("X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt")
    .server("https://parseapi.back4app.com/parse/")
    .build()
);

ParseUser user = new ParseUser();
user.setUsername("john_doe");
user.setPassword("SecurePass123");
user.setEmail("john@example.com");
user.signUpInBackground(e -> {
    // Handle signup
});
        </div>

        <h3>Flutter</h3>
        <div class="code-block">
import 'package:parse_server_sdk_flutter/parse_server_sdk.dart';

await Parse().initialize(
  'NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b',
  'https://parseapi.back4app.com/',
  clientKey: 'X4d5nqHZGa06RutBeJ024Tb73JpfJLWKkKUeTQmt',
);

var user = ParseUser('john_doe', 'SecurePass123', 'john@example.com');
var response = await user.save();
        </div>

    </div>

    <!-- ==================== SECTION 6: COMMON OPERATIONS ==================== -->
    <div class="section">
        <h2>🔧 Common Operations</h2>

        <h3>Query with Filters</h3>
        <div class="code-block">
# Users যাদের role = 'admin'
GET /parse/classes/_User?where={"role":"admin"}

# Last 10 posts
GET /parse/classes/Posts?limit=10&order=-createdAt

# Search by name
GET /parse/classes/_User?where={"username":{"$regex":"john"}}
        </div>

        <h3>Pagination</h3>
        <div class="code-block">
# Get 20 items, skip first 40
GET /parse/classes/Posts?limit=20&skip=40
        </div>

        <h3>Update Object</h3>
        <div class="code-block">
curl -X PUT https://parseapi.back4app.com/parse/classes/Posts/OBJECT_ID \
  -H "X-Parse-Application-Id: NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b" \
  -H "X-Parse-Master-Key: cx30LCUA8mfrKhS88Zetjo5PU5syyMk2Vh49n54u" \
  -H "Content-Type: application/json" \
  -d '{"likes": 100}'
        </div>

        <h3>Delete Object</h3>
        <div class="code-block">
curl -X DELETE https://parseapi.back4app.com/parse/classes/Posts/OBJECT_ID \
  -H "X-Parse-Application-Id: NXgg3EtUgqRLryHea3pjIHWf0qNdyWTxbfZAFQ9b" \
  -H "X-Parse-Master-Key: cx30LCUA8mfrKhS88Zetjo5PU5syyMk2Vh49n54u"
        </div>

    </div>

    <!-- ==================== SECTION 7: RESPONSE FORMAT ==================== -->
    <div class="section">
        <h2>📦 Response Format</h2>

        <h3>Successful Response (201 Created)</h3>
        <div class="code-block">
{
  "objectId": "EdFuqeEz4z",
  "createdAt": "2026-04-19T12:00:00.000Z",
  "username": "john_doe",
  "email": "john@example.com"
}
        </div>

        <h3>Error Response (401 Unauthorized)</h3>
        <div class="code-block">
{
  "code": 0,
  "error": "unauthorized"
}
        </div>

    </div>

    <!-- ==================== SECTION 8: SECURITY NOTES ==================== -->
    <div class="section">
        <h2>🔒 Security Best Practices</h2>

        <div class="warning">
            <strong>⚠️ NEVER expose Master Key in:</strong>
            <ul>
                <li>❌ Mobile apps (iOS, Android, Flutter)</li>
                <li>❌ Web browser (JavaScript)</li>
                <li>❌ Public repositories (GitHub)</li>
            </ul>
        </div>

        <div class="note">
            <strong>✅ DO use Master Key for:</strong>
            <ul>
                <li>✓ Server-side operations (Node.js, PHP)</li>
                <li>✓ Admin panels</li>
                <li>✓ Batch operations</li>
            </ul>
        </div>

        <div class="note">
            <strong>✅ DO use REST API Key for:</strong>
            <ul>
                <li>✓ Mobile apps</li>
                <li>✓ Web apps (JavaScript)</li>
                <li>✓ Client-side requests</li>
            </ul>
        </div>

    </div>

    <!-- ==================== SECTION 9: QUICK LINKS ==================== -->
    <div class="section">
        <h2>🔗 Quick Links & Resources</h2>
        <a href="verify_credentials.php"><button>🧪 Test API Connection</button></a>
        <a href="admin_import_verify.php"><button>👥 Admin Import Tool</button></a>
        <a href="https://parseplatform.org/docs/rest/guide/" target="_blank"><button>📖 Parse REST API Docs</button></a>
        <a href="https://dashboard.back4app.com" target="_blank"><button>📊 Back4app Dashboard</button></a>
    </div>

</div>

</body>
</html>
