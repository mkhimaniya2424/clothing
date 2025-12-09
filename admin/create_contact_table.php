<?php
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Create contact_messages table
$sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('pending', 'replied', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($con->query($sql)) {
    echo "Table 'contact_messages' created successfully!<br>";
    
    // Insert some sample data
    $sampleData = "INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES
        ('John Doe', 'john@example.com', '9876543210', 'Product Inquiry', 'I would like to know more about your products.', 'pending'),
        ('Jane Smith', 'jane@example.com', '9876543211', 'Order Issue', 'My order has not arrived yet.', 'replied'),
        ('Mike Johnson', 'mike@example.com', '9876543212', 'Return Request', 'I want to return my recent purchase.', 'resolved')";
    
    if ($con->query($sampleData)) {
        echo "Sample data inserted successfully!<br>";
    }
    
    echo "<br><a href='admin_manage-contact.php'>Go to Customer Care Page</a>";
} else {
    echo "Error: " . $con->error;
}
?>
