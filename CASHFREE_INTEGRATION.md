# Cashfree Payment Gateway Integration

## Overview
This project now includes Cashfree Payment Gateway integration for secure online payments.

## Files Added

### 1. **cashfree_config.php**
Configuration file containing:
- Cashfree API credentials (App ID and Secret Key)
- API endpoints (Sandbox/Production)
- Return and webhook URLs
- Helper functions for API headers

### 2. **cashfree_create_order.php**
Handles order creation and Cashfree payment session initialization:
- Validates user and cart data
- Calculates order total with discounts
- Creates order in database
- Initiates Cashfree payment session
- Returns payment link to frontend

### 3. **cashfree_return.php**
Return URL handler after payment completion:
- Verifies payment status with Cashfree API
- Updates order and payment status in database
- Clears user cart on successful payment
- Redirects to appropriate page based on payment status

### 4. **cashfree_webhook.php**
Webhook endpoint for Cashfree payment notifications:
- Receives real-time payment status updates
- Updates database automatically
- Logs webhook data for debugging

### 5. **migrate_cashfree.php**
Database migration script:
- Updates payment_details table to support 'online' payment method
- Adds necessary indexes for performance

## Database Changes

### Updated Tables:
- **payment_details**: Added 'online' to payment_method enum
- **payment_details**: Added index on transaction_id for faster lookups

## Configuration

### Current Settings (TEST Environment):
```php
App ID: TEST1091646204f8537d4cf62aca3a0126461901
Secret Key: cfsk_ma_test_f28f93f1cb62c067eac24eee28eea070_9671140b
API URL: https://sandbox.cashfree.com/pg
```

### For Production:
1. Get production credentials from Cashfree dashboard
2. Update `cashfree_config.php`:
   - Change CASHFREE_APP_ID to production App ID
   - Change CASHFREE_SECRET_KEY to production Secret Key
   - Change CASHFREE_API_URL to: `https://api.cashfree.com/pg`
3. Update return and webhook URLs to your production domain

## Payment Flow

1. **User selects "Pay Online (Cashfree)" at checkout**
2. **JavaScript intercepts form submission** (checkout.php)
3. **AJAX call to cashfree_create_order.php**:
   - Creates order in database
   - Calls Cashfree API to create payment session
   - Returns payment link
4. **User redirected to Cashfree payment page**
5. **User completes payment** (Card/UPI/Netbanking/Wallet)
6. **Cashfree redirects to cashfree_return.php**:
   - Verifies payment status
   - Updates database
   - Redirects to success/failure page
7. **Cashfree sends webhook to cashfree_webhook.php** (background):
   - Confirms payment status
   - Updates database automatically

## Testing

### Test Cards (Sandbox):
- **Success**: 4111 1111 1111 1111
- **Failure**: 4007 0000 0027 8403
- CVV: Any 3 digits
- Expiry: Any future date

### Test UPI:
- UPI ID: success@payu
- Status: Will show success

## Security Features

1. **Server-side validation**: All amounts calculated on server
2. **Payment verification**: Status verified with Cashfree API
3. **Transaction tracking**: All transactions logged in database
4. **Webhook validation**: Can add signature verification
5. **User authentication**: Only logged-in users can make payments

## Troubleshooting

### Common Issues:

1. **"Failed to create payment order"**
   - Check API credentials in cashfree_config.php
   - Verify internet connection
   - Check Cashfree API status

2. **Payment successful but order not updated**
   - Check webhook URL is accessible
   - Verify webhook logs in cashfree_webhook_log.txt
   - Manually verify payment in Cashfree dashboard

3. **"Unable to verify payment status"**
   - Check return URL configuration
   - Verify API credentials
   - Check order ID in database

## Webhook Setup in Cashfree Dashboard

1. Login to Cashfree Dashboard
2. Go to Developers > Webhooks
3. Add webhook URL: `http://yourdomain.com/clothing/cashfree_webhook.php`
4. Select events: ORDER_PAID, PAYMENT_FAILED
5. Save configuration

## Support

For Cashfree API documentation: https://docs.cashfree.com/
For support: Contact Cashfree support team

## Notes

- Always test in sandbox environment before going live
- Keep API credentials secure and never commit to version control
- Monitor webhook logs regularly
- Implement proper error handling and logging in production
