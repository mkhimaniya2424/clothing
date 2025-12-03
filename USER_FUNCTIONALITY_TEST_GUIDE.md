# User-Side Functionality Test Guide

## Test the following features:

### 1. **Change Password** (`change_password.php`)
- **Access**: Login → Profile → Change Password
- **Test Cases**:
  - ✅ Try with incorrect current password (should show error)
  - ✅ Try with mismatched new passwords (should show error)
  - ✅ Try with valid current password and matching new passwords (should succeed)
  - ✅ Verify minimum 6 character requirement
  - ✅ Check that success/error messages display correctly with proper colors

### 2. **Edit Profile** (`profile_edit.php`)
- **Access**: Login → Profile → Edit Profile
- **Test Cases**:
  - ✅ Update phone, gender, date of birth
  - ✅ Update address fields (line 1, line 2, city, state, postal code, country)
  - ✅ Verify required fields validation
  - ✅ Check that success/error messages display correctly
  - ✅ Verify data persists after update

### 3. **Forgot Password** (`forgot_password.php`)
- **Access**: Login page → Forgot Password link
- **Test Cases**:
  - ✅ Try with non-existent email (should show error)
  - ✅ Try with valid email (should send reset link)
  - ✅ Check email for reset link
  - ✅ Verify success/error messages display correctly

### 4. **Reset Password** (`reset_password.php`)
- **Access**: Click link from forgot password email
- **Test Cases**:
  - ✅ Try with invalid/expired token (should show error)
  - ✅ Try with mismatched passwords (should show error)
  - ✅ Try with valid token and matching passwords (should succeed)
  - ✅ Verify minimum 6 character requirement
  - ✅ Verify you can login with new password

### 5. **Offers Page** (`offers.php`)
- **Access**: Navigation → Offers
- **Test Cases**:
  - ✅ Verify active offers display correctly
  - ✅ Verify active promotions display correctly
  - ✅ Check discount percentages/amounts show properly
  - ✅ Verify promo codes display
  - ✅ Check "Shop Now" buttons work
  - ✅ Verify message when no offers available

## Fixed Issues:

### Session Consistency
- ✅ All files now use `$_SESSION['user']['id']` instead of mixed `$_SESSION['user_id']`
- ✅ Consistent with login.php session structure

### Layout Integration
- ✅ All pages now use proper output buffering with `ob_start()` and `ob_get_clean()`
- ✅ All pages include `$title_page` variable for proper page titles
- ✅ Consistent styling across all pages

### User Feedback
- ✅ Dynamic message types (success/danger/info) for better visual feedback
- ✅ Success messages show in green
- ✅ Error messages show in red
- ✅ Info messages show in blue

### Form Validation
- ✅ Password fields now have minimum 6 character requirement
- ✅ Required fields properly marked
- ✅ Better user guidance with helper text

### Navigation
- ✅ Added "Back to Profile" and "Back to Login" links
- ✅ Improved button layout and spacing
- ✅ Consistent card styling across all forms

## Database Requirements:

Ensure these tables exist:
- `users` (id, username, email, password_hash, phone, gender, dob, status)
- `user_address` (user_id, address_line1, address_line2, city, state, postal_code, country)
- `user_verification` (user_id, reset_token, reset_token_expiry)
- `offers` (id, title, description, discount_percentage, start_date, end_date, status)
- `promotions` (id, title, description, code, discount_percentage, discount_amount, start_date, end_date, status)

## Email Configuration:

Make sure `email_helper.php` is properly configured for sending password reset emails.
