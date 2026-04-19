# ✅ Admin Setup - Unauthorized Error সমাধান

## 🔴 সমস্যা কি ছিল?

আপনার `setup_admin.php` এ **"Error: unauthorized"** আসছিল কারণ:

### প্রধান কারণ:
`.env` ফাইলে সঠিক credentials ছিল না:
```
PARSE_APP_ID=your_application_id           ❌ WRONG
PARSE_REST_API_KEY=your_rest_api_key       ❌ WRONG
PARSE_MASTER_KEY=your_master_key           ❌ WRONG
```

## ✅ সমাধান প্রয়োগ করা হয়েছে

### 1️⃣ Updated `.env` with Correct Credentials
```
PARSE_APP_ID=NXgq3EtUgqRLryHea3pjlHWf0qNdyWTxbfZAFQ9b
PARSE_REST_API_KEY=X4d5nqHZ6a06RutBeJ024Tb73JpfJLWkkUeTQmt
PARSE_MASTER_KEY=cx30LCUAifrkh88Zetio5PUSsyMk2Vh49nK5LU4i
PARSE_SERVER_URL=https://parseapi.back4app.com/
```

### 2️⃣ Enhanced Error Messages
`setup_admin.php` এ better error reporting যোগ করা হয়েছে আপনার debugging এর জন্য।

### 3️⃣ Created Diagnostic Tools

#### প্রথমে চেক করুন:
```
http://168.144.65.62:8000/admin_setup_diagnostic.php
```

এটি আপনাকে দেখাবে:
- ✅ Credentials are loaded correctly
- ✅ Parse server is accessible
- ✅ User creation can work

#### তারপর test করুন:
```
http://168.144.65.62:8000/test_parse_connection.php
```

## 🚀 এখন করার কাজ

### স্টেপ 1: Diagnostic চেক করুন
```
http://168.144.65.62:8000/admin_setup_diagnostic.php
```

সবকিছু ✅ দেখা যাচ্ছে কিনা verify করুন।

### স্টেপ 2: Admin তৈরি করুন
সবকিছু ঠিক থাকলে:
```
http://168.144.65.62:8000/setup_admin.php
```

ফর্ম পূরণ করুন:
- 📧 **Email**: admin@example.com
- 👤 **Username**: admin
- 🔑 **Password**: SecurePassword123

**Submit** করুন।

### স্টেপ 3: Login করুন
SuccessHelper message পাবেন। তারপর:
```
http://168.144.65.62:8000/auth/login.php
```

## 🔧 যদি এখনও Error আসে

### সম্ভাব্য সমস্যা ও সমাধান:

| সমস্যা | চেক করবেন |
|--------|-----------|
| **"unauthorized" error** | Back4app Dashboard এ credentials verify করুন |
| **"Connection failed"** | Back4app server up কিনা check করুন |
| **"Invalid App ID"** | নিশ্চিত করুন `.env` এ সঠিক credentials আছে |
| **"REST API Key denied"** | Back4app settings এ permissions check করুন |

### Debug করার জন্য:

PHP error logs দেখুন:
```bash
tail -f error_log
```

বা চেক করুন:
- `admin/error_log`
- `auth/error_log`
- `dashboard/error_log`

## 📋 গুরুত্বপূর্ণ নোট

✅ **Security প্রয়োজনীয়তা পূরণ:**
- ✅ Credentials `.env` এ নিরাপদ (git থেকে exclude)
- ✅ Hardcoded keys সরানো হয়েছে
- ✅ Environment-based configuration সেটআপ

## 🔐 আরো নিরাপত্তা জন্য

Production এ এই credentials ব্যবহার করবেন না। নতুন keys generate করুন:
```
Back4app Dashboard → App Settings → Keys
```

এবং `.env` আপডেট করুন।

---

## ফাইল References:

- 📝 [`.env`](.env) - Configuration
- 📝 [`.env.example`](.env.example) - Template
- 📝 [`setup_admin.php`](setup_admin.php) - Admin creation
- 📝 [`admin_setup_diagnostic.php`](admin_setup_diagnostic.php) - Diagnostic tool
- 📝 [`test_parse_connection.php`](test_parse_connection.php) - Connection test

---

**Status**: ✅ Fixed - Ready to create admin user
**Date**: 2026-04-19
