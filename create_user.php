<?php
/**
 * CLI Tool: Create User / Admin Account
 * RACE FINANCE - Billing & Inventory System
 * 
 * Usage:
 *   Interactive:
 *     php create_user.php
 * 
 *   Command line flags:
 *     php create_user.php --phone=9876543210 --name="Admin Name" --password=MySecretPassword123 --role=admin
 *     php create_user.php --phone=9123456789 --name="Client Name" --password=Secret123 --role=user --days=365
 */

if (php_sapi_name() !== 'cli') {
    die("This script can only be run via the CLI.\n");
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/User.php';

echo "====================================================\n";
echo " RACE FINANCE - Account Provisioning CLI Tool\n";
echo "====================================================\n\n";

$options = getopt('', ['phone:', 'name:', 'password:', 'role:', 'email::', 'days::']);

$phone = $options['phone'] ?? null;
$name = $options['name'] ?? null;
$password = $options['password'] ?? null;
$role = $options['role'] ?? null;
$email = $options['email'] ?? null;
$days = isset($options['days']) ? (int)$options['days'] : 365;

// Interactive input if not provided via arguments
if (!$phone) {
    echo "Enter Mobile Phone (10 digits, Login ID): ";
    $phone = trim(fgets(STDIN));
}
$cleanedPhone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($cleanedPhone) < 10) {
    die("Error: A valid 10-digit mobile phone number is required.\n");
}

if (!$name) {
    echo "Enter Full Name / Business Owner: ";
    $name = trim(fgets(STDIN));
}
if (empty($name)) {
    die("Error: Name cannot be empty.\n");
}

if (!$password) {
    echo "Enter Password (min 8 chars, letters & numbers): ";
    $password = trim(fgets(STDIN));
}
if (strlen($password) < 8 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
    die("Error: Password must be at least 8 characters long and contain both letters and numbers.\n");
}

if (!$role) {
    echo "Account Role (1 for 'user', 2 for 'admin') [Default 1]: ";
    $choice = trim(fgets(STDIN));
    $role = ($choice === '2' || strtolower($choice) === 'admin') ? 'admin' : 'user';
} else {
    $role = (strtolower($role) === 'admin') ? 'admin' : 'user';
}

if ($email === null) {
    echo "Enter Email (Optional, press Enter to skip): ";
    $email = trim(fgets(STDIN));
    if ($email === '') $email = null;
}

// Check if user already exists
$existing = User::findByPhone($cleanedPhone);
if ($existing) {
    echo "\n[!] Notice: A user with phone {$cleanedPhone} already exists (ID: {$existing['id']}, Current Role: {$existing['role']}).\n";
    echo "Do you want to update this user to role '{$role}' and reset password? (y/N): ";
    $confirm = trim(fgets(STDIN));
    if (strtolower($confirm) === 'y') {
        $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        User::updatePassword((int)$existing['id'], $hashed);
        User::updateRole((int)$existing['id'], $role);
        User::updateStatus((int)$existing['id'], 'active');
        if ($role === 'user') {
            User::extendSubscription((int)$existing['id'], $days);
        }
        echo "\n[✓] User #{$existing['id']} updated successfully!\n";
        echo "Role: " . strtoupper($role) . "\n";
        echo "Phone ID: {$cleanedPhone}\n";
        exit(0);
    } else {
        die("Operation aborted.\n");
    }
}

// Expiry
$subscriptionExpiresAt = null;
if ($role !== 'admin') {
    $subscriptionExpiresAt = date('Y-m-d H:i:s', time() + ($days * 86400));
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

$user = User::create([
    'name' => $name,
    'email' => $email,
    'phone' => $cleanedPhone,
    'password' => $hashedPassword,
    'role' => $role,
    'status' => 'active',
    'subscription_expires_at' => $subscriptionExpiresAt
]);

if ($user) {
    echo "\n====================================================\n";
    echo " [✓] SUCCESS: Account Created Successfully!\n";
    echo "====================================================\n";
    echo "ID:            #" . $user['id'] . "\n";
    echo "Name:          " . $user['name'] . "\n";
    echo "Phone (Login): " . $user['phone'] . "\n";
    echo "Role:          " . strtoupper($user['role']) . "\n";
    echo "Status:        " . strtoupper($user['status']) . "\n";
    echo "Valid Until:   " . ($subscriptionExpiresAt ? date('d M Y', strtotime($subscriptionExpiresAt)) : 'Lifetime (Admin)') . "\n";
    echo "====================================================\n";
} else {
    echo "\n[x] Error: Failed to create user in database.\n";
    exit(1);
}
