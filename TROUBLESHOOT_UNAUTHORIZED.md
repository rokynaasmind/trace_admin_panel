# 🔴 Fix: "Parse Error (0): unauthorized" Issue

## Problem Summary

You're getting this error when trying to create an admin user:
```
Parse Error (0): unauthorized
```

This error means the Parse SDK is failing to authenticate with Back4app servers.

---

## 🔍 Root Causes (in order of likelihood)

### 1. **Credentials in .env are WRONG** ⚠️ (Most Common)
- Backend may have rotated your API keys
- You copied the wrong credentials
- The credentials don't match your Back4app app

### 2. **Back4app Application Issue**
- Your application is suspended/inactive
- The _User class has ACL restrictions
- Master Key doesn't have write permissions

### 3. **PHP SDK Caching Issue** 
- ParseSessionStorage might be corrupted
- Leftover sessions from failed attempts

---

## 🚀 Diagnostic Steps (Follow in Order)

### Step 1: Test API Directly (Most Reliable)

Go to:
```
http://168.144.65.62:8000/direct_api_test.php
```

Click these buttons in order:
1. **🏥 Test 1: Server Health** - Checks if Parse is reachable
2. **📋 Test 2: Query Users** - Tests with Master Key
3. **👤 Test 3: Create User** - Actually creates a test user

**Expected Results:**
- If Test 3 shows `✅ User created successfully!` → Your API is working ✅
- If Test 3 shows `❌ Unauthorized (401)` → Your credentials are WRONG ❌

---

### Step 2: Verify Credentials Match

1. Open your `.env` file:
   ```
   c:\wamp64\www\trace_admin_panel\.env
   ```

2. Go to **Back4app Dashboard** → Your Application → **Settings → Keys**

3. **Compare these 3 values:**
   ```
   .env                          Back4app Dashboard
   ---                           ---
   PARSE_APP_ID          =       Application ID
   PARSE_REST_API_KEY    =       REST API Key
   PARSE_MASTER_KEY      =       Master Key
   ```

   If they DON'T match → Copy from Back4app and update .env

---

### Step 3: Check Back4app Settings

**Go to Back4app Dashboard:**

1. Select your application
2. **Browser → _User class**
3. Click on **_User class name → Settings**
4. Check **Class Permissions:**
   - Master Key should have: ✅ Find, ✅ Get, ✅ Create, ✅ Update, ✅ Delete
5. **If restricted:**
   - Click **Edit** 
   - Grant all permissions to Master Key
   - **Save**

---

### Step 4: Clear PHP Sessions Cache

Sometimes old session data causes issues.

Delete these files (they'll be recreated):
```
c:\wamp64\www\trace_admin_panel
  → Delete: error_log (if present)
  → auth/error_log (if present)
  → dashboard/error_log (if present)
```

Or run in PowerShell:
```powershell
Remove-Item 'c:\wamp64\www\trace_admin_panel\error_log' -ErrorAction SilentlyContinue
Remove-Item 'c:\wamp64\www\trace_admin_panel\auth\error_log' -ErrorAction SilentlyContinue
```

---

### Step 5: Regenerate API Keys (Last Resort)

**⚠️ Only if Steps 1-4 don't work**

1. Go to **Back4app Dashboard → Settings → Keys**
2. For each key, click **Change**:
   - Application ID
   - REST API Key  
   - Master Key
3. **Copy the NEW values**
4. Update your `.env` file with new credentials
5. Clear session cache (Step 4)
6. Try admin creation again

---

## 📋 Quick Diagnostics Checklist

Check off as you go:

```
□ Direct API Test (direct_api_test.php?test=3) works
□ .env credentials match Back4app Dashboard
□ Back4app _User class has Master Key permissions
□ Session/error_log files cleared
□ WAMP is running and responding
□ .env file is in correct location: c:\wamp64\www\trace_admin_panel\.env
□ File permissions allow .env to be read (usually not an issue on Windows)
```

---

## 🎯 Recommended Action Flow

### If Tests Pass (✅ Success on direct_api_test.php):
```
1. Go to: direct_api_test.php
2. Click: "Test 3: Create User"
3. See: ✅ User created successfully
4. Go to: setup_admin.php
5. Fill form and submit
6. Should work now!
```

### If Tests Fail (❌ Unauthorized):
```
1. Copy new credentials from Back4app
2. Update .env file
3. Clear cache (delete error_log files)
4. Retry tests
5. If still fails: Regenerate keys in Back4app
6. Update .env with new keys
7. Try again
```

---

## 🔐 Testing Tools Created

| File | Purpose | When to Use |
|------|---------|----------|
| `direct_api_test.php` | Test API directly | When getting 401 errors |
| `debug_credentials.php` | Deep debugging | To see exact credential loading |
| `simple_api_test.php` | No cURL required | If cURL not available |
| `setup_admin.php` | Admin creation | Main form (now with better errors) |

---

## 📞 If Still Not Working

Share these details:

1. **Result from** `direct_api_test.php?test=3`:
   - What status code do you get? (200, 401, 500, etc.)
   - What error message?

2. **Your .env values** (masked):
   - `PARSE_APP_ID`: First 10 chars + ... + last 5 chars
   - Example: `NXgq3EtUgq...ZAFq9b`

3. **Has your Back4app app been inactive?**
   - Did you receive any emails from Back4app?
   - Is your account still active?

---

## ✅ Once It Works

After successfully creating admin user:

1. Go to: `http://168.144.65.62:8000/auth/login.php`
2. Use credentials you just created
3. Should see dashboard

---

## 🎓 Root Cause for Future Reference

This usually happens because:
- **Development → Production shift**: Your dev API keys were regenerated in Back4app
- **Account activity**: Back4app deactivated or reset your app
- **File sync issue**: `.env` wasn't synchronized properly
- **Team collaboration**: Someone else regenerated keys

Always keep `.env` synchronized with your Back4app Dashboard keys!

---

**Next Step:** Go to `direct_api_test.php` and share the result with me!

**Last Updated:** 2026-04-19
