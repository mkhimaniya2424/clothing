# Test Credentials & URLs

## Quick Access URLs

### User Side
- **Home**: http://localhost/clothing/home.php
- **Shop**: http://localhost/clothing/shop.php
- **Login**: http://localhost/clothing/login.php
- **Register**: http://localhost/clothing/register.php
- **Cart**: http://localhost/clothing/cart.php
- **Wishlist**: http://localhost/clothing/wishlist.php
- **Orders**: http://localhost/clothing/orders.php
- **Profile**: http://localhost/clothing/profile.php

### Admin Side
- **Admin Login**: http://localhost/clothing/admin/admin_login.php
- **Admin Dashboard**: http://localhost/clothing/admin/admin_dashboard.php

---

## Test User Credentials

### Existing User (from database)
- **Username**: megha
- **Email**: meghanaahir1@gmail.com
- **Password**: (check database - password is hashed)

### Admin Credentials
- **Username**: admin
- **Password**: admin123
  - MySQL Password Hash: `*01A6717B58FF5C7EAFFF6CB7C96F7428EA65FE4C`
  - This is PASSWORD('admin123')

---

## Creating Test User

### Option 1: Register via UI
1. Go to http://localhost/clothing/register.php
2. Fill in details:
   - Username: testuser
   - Email: test@example.com
   - Password: Test@123
   - Phone: 1234567890
   - Gender: Male
   - DOB: 2000-01-01
   - Address: Test Address, Test City, Test State, 123456

### Option 2: Direct SQL Insert
```sql
-- Insert test user
INSERT INTO users (username, email, password_hash, phone, gender, dob, status) 
VALUES (
    'testuser',
    'test@example.com',
    '$2y$10$YisDaXHw0O3cS3K/2onEo.4BtoMn1gRx27HrVOcAgOvlLz.OStldO', -- password: Test@123
    '1234567890',
    'male',
    '2000-01-01',
    'active'
);

-- Get the user ID
SET @user_id = LAST_INSERT_ID();

-- Insert test address
INSERT INTO user_address (user_id, address_line1, city, state, postal_code, country, address_type)
VALUES (
    @user_id,
    'Test Address Line 1',
    'Test City',
    'Test State',
    '123456',
    'India',
    'home'
);
```

---

## Test Product Data

### Existing Product
- **ID**: 1
- **Title**: Carbon Heavyweight T-Shirt – Sorona
- **Price**: ₹1000.00
- **Category**: Men
- **Brand**: Adidas

### Add More Test Products (SQL)
```sql
INSERT INTO products (title, price, category_main, category_sub, category_type, category_brand, sizes, fabric, description, status)
VALUES 
('Classic Cotton Shirt', 799.00, '1', 'shirts', 'Casual', 'Adidas', 's,m,l,xl', 'Cotton', 'Comfortable cotton shirt for everyday wear', 'active'),
('Premium Denim Jeans', 1499.00, '1', 'bottom wear', 'Jeans', 'gucci', '28,30,32,34,36', 'Denim', 'High-quality denim jeans', 'active'),
('Sports T-Shirt', 599.00, '2', 'top wear', 'T-Shirts', 'Adidas', 's,m,l,xl', 'Polyester', 'Breathable sports t-shirt', 'active');

-- Add stock for these products
INSERT INTO product_stock (product_id, stock) VALUES
(2, 50),
(3, 30),
(4, 40);
```

---

## Testing Scenarios

### 1. Complete Purchase Flow
```
1. Register/Login
2. Browse shop
3. Add 2-3 products to cart
4. Update quantities
5. Proceed to checkout
6. Place order
7. View order in "My Orders"
8. Cancel order (if pending)
```

### 2. Wishlist Flow
```
1. Login
2. Browse products
3. Add to wishlist
4. View wishlist
5. Move item to cart
6. Remove from wishlist
```

### 3. Filter & Sort
```
1. Go to shop
2. Filter by category (Men/Women)
3. Filter by brand
4. Set price range
5. Sort by price/name
6. Clear filters
```

### 4. Product Reviews
```
1. View product details
2. See existing reviews
3. Check average rating
```

---

## Database Quick Checks

### Check User Session
```sql
SELECT * FROM users WHERE email = 'test@example.com';
```

### Check Cart Items
```sql
SELECT c.*, p.title, p.price 
FROM cart c 
JOIN products p ON c.product_id = p.id 
WHERE c.user_id = 1;
```

### Check Orders
```sql
SELECT o.*, u.username 
FROM orders o 
JOIN users u ON o.user_id = u.id 
ORDER BY o.created_at DESC;
```

### Check Order Items
```sql
SELECT oi.*, p.title 
FROM order_items oi 
JOIN products p ON oi.product_id = p.id 
WHERE oi.order_id = 1;
```

---

## Troubleshooting

### Clear Cart
```sql
DELETE FROM cart WHERE user_id = 1;
```

### Reset Orders
```sql
DELETE FROM order_items;
DELETE FROM payment_details;
DELETE FROM orders;
ALTER TABLE orders AUTO_INCREMENT = 1;
```

### Clear Session (PHP)
```php
session_start();
session_destroy();
```

---

## Admin Testing

### Admin Login
1. Go to: http://localhost/clothing/admin/admin_login.php
2. Username: admin
3. Password: admin123

### Admin Features to Test
- [ ] View dashboard
- [ ] Manage products
- [ ] Manage orders
- [ ] Update order status
- [ ] Manage categories
- [ ] Manage brands
- [ ] Manage users
- [ ] View cart activity

---

## Notes

- All passwords are hashed using bcrypt
- Session timeout: Default PHP session timeout
- Cart persists in database
- Orders are transaction-based
- Stock is tracked but not enforced (can order out of stock items)

---

**Last Updated**: December 3, 2025
