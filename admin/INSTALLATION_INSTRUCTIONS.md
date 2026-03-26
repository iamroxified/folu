# Wallet and PV Score Management Installation

## Overview
This implementation adds the ability for admins to manage user wallet balances and PV (Point Value) scores directly from the admin panel.

## Files Created:
1. `add_wallet_balance.php` - Backend handler for wallet balance additions
2. `add_pv_score.php` - Backend handler for PV score additions  
3. `create_wallet_pv_tables.sql` - Database schema for wallet and PV tables
4. `all_users.php` - Updated with wallet/PV management interface

## Installation Steps:

### Step 1: Create Database Tables
1. Open your MySQL database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Select your `spg` database
3. Run the SQL script from `create_wallet_pv_tables.sql`

### Step 2: Verify File Permissions
Ensure all PHP files have proper read/write permissions:
- `add_wallet_balance.php`
- `add_pv_score.php`
- `all_users.php` (updated)

### Step 3: Test the Functionality
1. Login as an admin
2. Navigate to `all_users.php`
3. You should see new "Add Wallet" and "Add PV" buttons in the Action column
4. The Balance column should show current wallet balance and PV score

## Usage:

### Adding Wallet Balance:
1. Click "Add Wallet" button for any user
2. Enter the amount (minimum $0.01)
3. Add an optional description
4. Click "Add Balance"
5. Success notification will appear and page will refresh

### Adding PV Score:
1. Click "Add PV" button for any user
2. Enter PV points (minimum 1)
3. Add an optional description
4. Click "Add PV Score"
5. Success notification will appear and page will refresh

## Features:

### Security:
- Admin authentication required
- Input validation and sanitization
- SQL injection prevention using prepared statements

### User Experience:
- Clean modal interfaces
- Loading states during submission
- Success/error notifications
- Automatic page refresh after updates

### Database Management:
- Transaction logging for all wallet activities
- Transaction logging for all PV activities
- Balance history tracking
- Audit trail for admin actions

## Troubleshooting:

### Common Issues:

1. **Database Connection Error**:
   - Verify database credentials in `../db/config.php`
   - Ensure database server is running

2. **Permission Denied**:
   - Check file permissions on PHP files
   - Verify admin user has proper access level

3. **Tables Don't Exist**:
   - Run the SQL script from `create_wallet_pv_tables.sql`
   - Verify tables were created successfully

4. **AJAX Errors**:
   - Check browser console for JavaScript errors
   - Verify SweetAlert library is loaded

5. **Balance Not Updating**:
   - Check if wallet functions exist in `../db/functions.php`
   - Verify user ID is correct in database

## Database Schema:

### user_wallet
- `id` (Primary Key)
- `user_id` (Foreign Key to users.bmid)
- `balance` (Decimal 15,2)
- `created_at`, `updated_at` (Timestamps)

### wallet_transactions
- `id` (Primary Key)
- `user_id` (Foreign Key)
- `transaction_type` (credit/debit)
- `amount` (Decimal 15,2)
- `previous_balance`, `new_balance` (Decimal 15,2)
- `reference`, `description` (Text)
- `created_at` (Timestamp)

### user_pv
- `id` (Primary Key)
- `user_id` (Foreign Key to users.bmid)
- `total_pv` (Decimal 15,2)
- `created_at`, `updated_at` (Timestamps)

### pv_transactions
- `id` (Primary Key)
- `user_id` (Foreign Key)
- `transaction_type` (credit/debit)
- `pv_amount` (Decimal 15,2)
- `description` (Text)
- `created_at` (Timestamp)

## Support:
For issues or questions, check:
1. Browser console for JavaScript errors
2. PHP error logs for backend issues
3. Database logs for SQL errors
4. Network tab in browser dev tools for AJAX issues
