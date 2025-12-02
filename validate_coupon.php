<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $total = floatval($_POST['total']);
    
    $stmt = $con->prepare("SELECT * FROM coupons WHERE code=? AND status='active' AND valid_from <= CURDATE() AND valid_until >= CURDATE()");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $coupon = $res->fetch_assoc();
        
        // Check Min Purchase
        if ($total < $coupon['min_purchase_amount']) {
            echo json_encode(['valid' => false, 'msg' => "Min purchase of ₹{$coupon['min_purchase_amount']} required."]);
            exit;
        }
        
        // Calculate Discount
        $discount = 0;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = ($total * $coupon['discount_value']) / 100;
            if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
                $discount = $coupon['max_discount'];
            }
        } else {
            $discount = $coupon['discount_value'];
        }
        
        $new_total = $total - $discount;
        if ($new_total < 0) $new_total = 0;
        
        echo json_encode([
            'valid' => true, 
            'msg' => "Saved ₹" . number_format($discount, 2),
            'discount' => $discount,
            'new_total' => $new_total
        ]);
    } else {
        echo json_encode(['valid' => false, 'msg' => "Invalid or expired coupon."]);
    }
}
?>
