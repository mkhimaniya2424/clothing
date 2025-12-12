# Implementation Plan - Switch to Cashfree JS SDK

The current integration relies on a `payment_link` which is not being returned by the Cashfree API, causing the application to fallback to an incorrect URL. This results in the "endpoint or method is not valid" error when the user is redirected.

To fix this robustly, we will switch to using the **Cashfree JS SDK** which uses the `payment_session_id` (which we *are* successfully receiving) to initiate the payment.

## User Review Required

> [!IMPORTANT]
> This change modifies the checkout flow to use the Cashfree popup/redirect via their SDK instead of a direct URL redirect. This is the recommended approach by Cashfree.

## Proposed Changes

### 1. Update `checkout.php`
- Include the Cashfree JS SDK script.
- Initialize the `Cashfree` instance.
- Update the payment handling logic to:
    - Receive `payment_session_id` from `cashfree_create_order.php`.
    - Call `cashfree.checkout({ paymentSessionId: ... })`.

### 2. Update `cashfree_config.php`
- Add a constant for the Cashfree SDK URL (Sandbox/Production).

## Verification Plan

### Automated Tests
- None (requires UI interaction).

### Manual Verification
- User to retry checkout.
- Verify that the Cashfree payment UI opens (either popup or redirect) instead of showing an error page.
