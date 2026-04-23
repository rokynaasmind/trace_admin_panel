# Trace Admin Panel - New Features Implementation Guide

## ✅ All Features Successfully Implemented!

### 1. User Suspension Feature
**What it does:** Allows admins to suspend/activate users directly from the admin panel.

**How to use:**
1. Go to **Users > All users** or **Users > Admin users**
2. You'll see a new "Suspension Status" column showing each user's status
3. Click the Edit button on any user
4. In the edit form, you'll see a new "Suspension Status" dropdown
5. Select "Suspended" or "Active" and save
6. The user table will immediately reflect the change

**Database Field:** Uses `activationStatus` in the _User collection

---

### 2. BD (Business Development) Management
**What it does:** Manage your business development representatives/managers.

**How to access:**
- Navigate to **Users > BD Management** in the sidebar

**Features:**
- **View all BDs:** See list of all BD users with their Parent Admin UID and status
- **Add BD:** Click "Add BD" button to create new BD entry
  - Enter: User ID, Parent Admin UID, Remark (optional)
- **Edit BD:** Click "Edit" to change status (Active/Inactive)
- **Delete BD:** Remove BD from the system

**Database:** Parse collection `BD`

---

### 3. Agency Management System
**What it does:** Complete agency management for admins including creation, member management, and application review.

#### A. Agency List
**How to access:** **Agency > Agency List**

**Features:**
- View all agencies with detailed information
- Create new agencies with full details:
  - Agency ID, Name, President ID
  - Region, Agency Level (1-3)
  - External Admin info
  - Contact details
- Delete agencies

#### B. Agency Members
**How to access:** **Agency > Agency Members**

**Features:**
- View all agency members across all agencies
- Add members with:
  - Agency ID
  - User ID
  - Role (President, VP, Manager, Member)
- Remove members from agencies
- Track member join date

#### C. Applications Review
**How to access:** **Agency > Applications Review**

**Features:**
- **Three tabs:** Pending, Approved, Rejected
- **Pending Applications:**
  - View all pending applications
  - Approve: Click "Approve" button to accept application
  - Reject: Click "Reject" button with rejection reason
  - View: See full application details
- **Approved Tab:** See all approved agencies with approval date
- **Rejected Tab:** See all rejected applications with rejection reasons

**Database Collections:**
- `Agency` - Agency information
- `AgencyMember` - Membership records
- `AgencyApplication` - User applications

---

### 4. User Agency Application (For Regular Users)
**What it does:** Allows regular users to apply to become an agency.

**How to access:** 
- URL: `features/apply_agency.php`
- Add a link in your user dashboard/menu pointing to this page

**User Experience:**
1. User fills out application form:
   - Full Name, Email, Contact Phone
   - Agency Name, Region
   - Detailed agency description
2. Application is submitted to admin review
3. User can view application status anytime:
   - **Pending Review:** Waiting for admin decision
   - **Approved:** Congratulations message
   - **Rejected:** Shows rejection reason from admin

**Database:** Parse collection `AgencyApplication`

---

## Access Paths Summary

### Admin Features (in sidebar)
- **Users Dropdown:**
  - All users → Shows suspension status column
  - Admin users
  - **[NEW] BD Management** → Manage BDs
  
- **[NEW] Agency Section:**
  - Agency List → Create/view agencies
  - Agency Members → Manage members
  - Applications Review → Approve/reject user applications

### User Features
- `features/apply_agency.php` → Apply to become an agency

---

## Database Collections Created

### 1. BD Collection
- **Fields:** userId, parentAdminUid, remark, status, createdAt

### 2. Agency Collection  
- **Fields:** agencyId, agencyName, presidentId, contactAddress, realName, region, agencyLevel, externalAdminUserId, externalAdminNickname, parentAdminOr, remark, status, createdAt

### 3. AgencyMember Collection
- **Fields:** agencyId, userId, role, status, createdAt

### 4. AgencyApplication Collection
- **Fields:** userId, fullName, email, agencyName, description, contactPhone, region, status, approvedAt, approvedBy, rejectedAt, rejectedBy, rejectionReason, createdAt

---

## Tips & Best Practices

1. **User Suspension:** Use to temporarily disable user accounts without deleting them
2. **BD Management:** Keep track of which admin supervises which BD
3. **Agency Level:** 1-3 represent different tiers (1 = highest)
4. **Application Review:** Check pending applications regularly to respond quickly to users
5. **Agency Members:** Always set a President for each agency

---

## Support

For any issues or questions about these features, refer to the implementation files:
- BD: `/dashboard/bd_list.php`
- Agency List: `/dashboard/agency_list.php`
- Agency Members: `/dashboard/agency_members.php`
- Applications: `/dashboard/agency_applications.php`
- User Apply: `/features/apply_agency.php`
