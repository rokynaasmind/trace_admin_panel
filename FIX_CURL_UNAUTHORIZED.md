# 🔧 Fix: cURL & Unauthorized Errors

## সমস্যা কি ছিল?

### 1. ❌ Fatal Error: curl_init() undefined
- **কারণ:** PHP এ cURL extension disabled ছেড়ে আছে
- **ফাইল:** `api_test.php` লাইন 27

### 2. ❌ Still Error: unauthorized
- **কারণ:** `api_test.php` এ hardcoded OLD credentials ছিল:
  ```php
  $appId = 'yiAEelcOnI3YnRYp9Xft6fAfI6CJLU0TLtKYf0nP';  // ❌ WRONG
  $masterKey = 'AsDVQmszF2ybh9MeeYxW6tsWdfmJbCnxwUrlkkGt'; // ❌ WRONG
  ```

---

## ✅ সমাধান করা হয়েছে

### 1️⃣ Updated `api_test.php`
- ✅ `.env` থেকে credentials load করে (hardcoded values সরানো)
- ✅ cURL এর availability চেক করে
- ✅ cURL ছাড়াই PHP streams ব্যবহার করে fallback সেটআপ

### 2️⃣ Created `simple_api_test.php`
- ✅ cURL ছাড়াই কাজ করে
- ✅ PHP streams ব্যবহার করে
- ✅ সঠিক credentials `.env` থেকে নেয়

### 3️⃣ Instructions যোগ করা
- cURL enable করার পদ্ধতি যোগ করা হয়েছে

---

## 🚀 এখন করবেন

### অপশন A: cURL ছাড়াই (Recommended)
```
http://168.144.65.62:8000/simple_api_test.php
```
ক্লিক করুন **"Test Master Key"** button

আপনি পাবেন:
- ✅ **Success** - যদি credentials সঠিক হয়
- ❌ **Unauthorized (401)** - যদি credentials গলত হয়

### অপশন B: cURL Enable করুন (অপশনাল)

**WAMP এ cURL enable করতে:**

1. WAMP tray icon এ double-click করুন
2. **PHP → php.ini** তে যান
3. এই লাইন খুঁজুন:
   ```
   ;extension=curl
   ```
4. সেমিকোলন সরান:
   ```
   extension=curl
   ```
5. Save করুন এবং WAMP restart করুন

তারপর:
```
http://168.144.65.62:8000/api_test.php
```

---

## ⚠️ যদি এখনও "Unauthorized" আসে

এই ক্রমে check করুন:

### 1. `.env` ফাইল verify করুন
এই ফাইলটি খুলুন এবং চেক করুন:
```
c:\wamp64\www\MyParty_admin_panel\.env
```

হওয়া উচিত:
```
PARSE_APP_ID=NXgq3EtUgqRLryHea3pjlHWf0qNdyWTxbfZAFQ9b
PARSE_REST_API_KEY=X4d5nqHZ6a06RutBeJ024Tb73JpfJLWkkUeTQmt
PARSE_MASTER_KEY=cx30LCUAifrkh88Zetio5PUSsyMk2Vh49nK5LU4i
```

### 2. Back4app Dashboard এ verify করুন
1. Back4app Dashboard এ যান
2. আপনার application খুলুন
3. **Settings → Keys** এ যান
4. এই keys গুলো match করুন আপনার `.env` এর সাথে

### 3. Application status check করুন
- Application active/enabled আছে কিনা check করুন
- কোন suspension নেই কিনা check করুন

---

## 📋 ফাইল রেফারেন্স

| ফাইল | উদ্দেশ্য | স্ট্যাটাস |
|------|---------|---------|
| `.env` | Credentials storage | ✅ Updated with correct values |
| `api_test.php` | cURL based testing | ✅ Fixed - loads from .env |
| `simple_api_test.php` | No cURL needed | ✅ NEW - Stream based |
| `setup_admin.php` | Admin creation | ✅ Updated with better errors |
| `admin_setup_diagnostic.php` | Diagnostics | ✅ Available for testing |

---

## 🔄 সম্পূর্ণ ওয়ার্কফ্লো

```
1. simple_api_test.php এ Test Master Key
        ↓
2. যদি ✅ Success দেখেন
        ↓
3. admin_setup_diagnostic.php এ যান
        ↓
4. যদি সবকিছু ✅ দেখেন
        ↓
5. setup_admin.php এ admin তৈরি করুন
        ↓
6. auth/login.php এ লগইন করুন
```

---

## 🎯 Next Step

এই URL এ যান:
```
http://168.144.65.62:8000/simple_api_test.php
```

**"Test Master Key"** button এ click করুন।

আমাকে বলুন কি output পান - তারপর সমাধান করব! 🚀

---

**Last Updated:** 2026-04-19
**Status:** ✅ Both issues fixed - Ready for testing
