<?php
// Test script to verify wallet and PV functionality
// Run this file to check if all components are working

require('../db/config.php');
require('../db/functions.php');

echo "<h2>Wallet and PV System Test</h2>";

// Test 1: Check if tables exist
echo "<h3>1. Checking Database Tables...</h3>";
$tables = ['user_wallet', 'wallet_transactions', 'user_pv', 'pv_transactions'];
foreach ($tables as $table) {
    try {
        $result = QueryDB("SELECT 1 FROM $table LIMIT 1");
        echo "✅ Table '$table' exists<br>";
    } catch (Exception $e) {
        echo "❌ Table '$table' does not exist. Please run the SQL script.<br>";
    }
}

// Test 2: Check wallet functions
echo "<h3>2. Testing Wallet Functions...</h3>";
if (function_exists('get_user_wallet_balance')) {
    echo "✅ get_user_wallet_balance() function exists<br>";
} else {
    echo "❌ get_user_wallet_balance() function missing<br>";
}

if (function_exists('create_user_wallet')) {
    echo "✅ create_user_wallet() function exists<br>";
} else {
    echo "❌ create_user_wallet() function missing<br>";
}

if (function_exists('update_wallet_balance')) {
    echo "✅ update_wallet_balance() function exists<br>";
} else {
    echo "❌ update_wallet_balance() function missing<br>";
}

// Test 3: Check if handler files exist
echo "<h3>3. Checking Handler Files...</h3>";
$files = ['add_wallet_balance.php', 'add_pv_score.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

// Test 4: Test wallet creation for first user
echo "<h3>4. Testing Wallet Creation...</h3>";
try {
    $userQuery = QueryDB("SELECT bmid FROM users LIMIT 1");
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $user_id = $user['bmid'];
        $current_balance = get_user_wallet_balance($user_id);
        echo "✅ User ID $user_id has wallet balance: $" . number_format($current_balance, 2) . "<br>";
    } else {
        echo "❌ No users found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error testing wallet: " . $e->getMessage() . "<br>";
}

// Test 5: Test PV functionality
echo "<h3>5. Testing PV System...</h3>";
try {
    if (isset($user_id)) {
        $pvQuery = QueryDB("SELECT total_pv FROM user_pv WHERE user_id = ?", [$user_id]);
        $pvResult = $pvQuery->fetch(PDO::FETCH_ASSOC);
        $pv_score = $pvResult ? $pvResult['total_pv'] : 0;
        echo "✅ User ID $user_id has PV score: " . number_format($pv_score, 0) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error testing PV: " . $e->getMessage() . "<br>";
}

echo "<h3>Test Complete!</h3>";
echo "<p>If all tests pass, the wallet and PV system is ready to use.</p>";
echo "<p><a href='all_users.php'>Go to All Users</a> to test the interface.</p>";
?>
