<?php
// ============================================================
// EcoConnect - Helper Functions
// ============================================================

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic)
 * @param string $phone
 * @return bool
 */
function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s()]{7,20}$/', $phone);
}

/**
 * Generate a unique code
 * @param int $length
 * @return string
 */
function generateCode($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

/**
 * Generate a unique request code
 * @return string
 */
function generateRequestCode() {
    return 'ECO-' . date('Ymd') . '-' . generateCode(6);
}

/**
 * Generate a unique payment code
 * @return string
 */
function generatePaymentCode() {
    return 'PAY-' . date('Ymd') . '-' . generateCode(6);
}

/**
 * Generate a unique receipt number
 * @return string
 */
function generateReceiptNumber() {
    return 'RCP-' . date('Ymd') . '-' . generateCode(8);
}

/**
 * Format currency
 * @param float $amount
 * @return string
 */
function formatCurrency($amount) {
    return number_format($amount, 0) . ' RWF';
}

/**
 * Get time ago string
 * @param string $datetime
 * @return string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $current = time();
    $diff = $current - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('d M Y', $time);
}

/**
 * Get status badge HTML
 * @param string $status
 * @return string
 */
function getStatusBadge($status) {
    $badges = [
        'active' => 'bg-success',
        'inactive' => 'bg-secondary',
        'pending' => 'bg-warning text-dark',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        'assigned' => 'bg-info',
        'in_progress' => 'bg-primary',
        'paid' => 'bg-success',
        'unpaid' => 'bg-danger',
        'refunded' => 'bg-warning text-dark',
    ];
    
    $class = $badges[$status] ?? 'bg-secondary';
    $label = ucwords(str_replace('_', ' ', $status));
    
    return "<span class='badge {$class}'>{$label}</span>";
}

/**
 * Redirect with message
 * @param string $url
 * @param string $message
 * @param string $type (success/error)
 */
function redirectWith($url, $message, $type = 'success') {
    if ($type === 'success') {
        setSuccess($message);
    } else {
        setError($message);
    }
    header("Location: $url");
    exit();
}

/**
 * Check if cashier has specific permission
 * @param int $user_id
 * @param string $permission
 * @return bool
 */
function hasPermission($user_id, $permission) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT $permission FROM permissions WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        return $result && $result[$permission] == 1;
    } catch (PDOException $e) {
        error_log("Permission check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a cashier is a Super Cashier (has all permissions)
 * @param int $user_id
 * @return bool
 */
function isSuperCashier($user_id) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT is_super_cashier FROM permissions WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        return $result && $result['is_super_cashier'] == 1;
    } catch (PDOException $e) {
        error_log("Super cashier check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Assign a cashier as Super Cashier (grants all permissions)
 * @param int $cashier_id
 * @param int $admin_id
 * @return bool
 */
function assignSuperCashier($cashier_id, $admin_id) {
    global $db;
    
    try {
        // First, remove super cashier from any existing super cashier
        $db->prepare("UPDATE permissions SET is_super_cashier = 0 WHERE is_super_cashier = 1")->execute();
        
        // Set all permissions to 1 and mark as super cashier
        $stmt = $db->prepare("UPDATE permissions SET 
            can_manage_requests = 1, 
            can_process_payments = 1, 
            can_view_reports = 1, 
            can_manage_customers = 1, 
            can_manage_collections = 1, 
            is_super_cashier = 1,
            granted_by = ?
            WHERE user_id = ?");
        $stmt->execute([$admin_id, $cashier_id]);
        
        // Get cashier name for logging
        $stmt = $db->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $stmt->execute([$cashier_id]);
        $cashier = $stmt->fetch();
        
        logActivity('Super Cashier Assigned', "Assigned {$cashier['full_name']} as Super Cashier");
        createNotification($cashier_id, 'Super Cashier Assigned', 'You have been granted Super Cashier status with all permissions.', 'success');
        
        return true;
    } catch (PDOException $e) {
        error_log("Assign super cashier failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove Super Cashier status from a cashier
 * @param int $cashier_id
 * @return bool
 */
function removeSuperCashier($cashier_id) {
    global $db;
    
    try {
        $stmt = $db->prepare("UPDATE permissions SET is_super_cashier = 0 WHERE user_id = ?");
        $stmt->execute([$cashier_id]);
        
        // Get cashier name for logging
        $stmt = $db->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $stmt->execute([$cashier_id]);
        $cashier = $stmt->fetch();
        
        logActivity('Super Cashier Removed', "Removed Super Cashier status from {$cashier['full_name']}");
        createNotification($cashier_id, 'Super Cashier Status Removed', 'Your Super Cashier status has been removed.', 'warning');
        
        return true;
    } catch (PDOException $e) {
        error_log("Remove super cashier failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get the current Super Cashier
 * @return array|null
 */
function getCurrentSuperCashier() {
    global $db;
    
    try {
        $stmt = $db->query("
            SELECT u.user_id, u.full_name, u.email, u.phone, u.last_login, p.is_super_cashier
            FROM users u
            JOIN permissions p ON u.user_id = p.user_id
            WHERE u.role = 'cashier' AND p.is_super_cashier = 1
            LIMIT 1
        ");
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get super cashier failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Get unread notification count for user
 * @param int $user_id
 * @return int
 */
function getUnreadNotificationCount($user_id) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Create a notification
 * @param int|null $user_id
 * @param string $title
 * @param string $message
 * @param string $type
 * @param string|null $link
 */
function createNotification($user_id, $title, $message, $type = 'info', $link = null) {
    global $db;
    
    try {
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $message, $type, $link]);
    } catch (PDOException $e) {
        error_log("Notification creation failed: " . $e->getMessage());
    }
}

/**
 * Get dashboard stats
 * @param string $role
 * @param int $user_id
 * @return array
 */
function getDashboardStats($role, $user_id = null) {
    global $db;
    $stats = [];
    
    try {
        if ($role === 'admin') {
            // Total customers
            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
            $stats['total_customers'] = $stmt->fetch()['count'];
            
            // Total cashiers
            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'cashier'");
            $stats['total_cashiers'] = $stmt->fetch()['count'];
            
            // Total requests
            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests");
            $stats['total_requests'] = $stmt->fetch()['count'];
            
            // Pending requests
            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests WHERE status = 'pending'");
            $stats['pending_requests'] = $stmt->fetch()['count'];
            
            // Completed requests
            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests WHERE status = 'completed'");
            $stats['completed_requests'] = $stmt->fetch()['count'];
            
            // Total revenue
            $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'completed'");
            $stats['total_revenue'] = $stmt->fetch()['total'];
            
            // Total categories
            $stmt = $db->query("SELECT COUNT(*) as count FROM waste_categories WHERE is_active = 1");
            $stats['total_categories'] = $stmt->fetch()['count'];
            
            // Super Cashier info
            $super = getCurrentSuperCashier();
            $stats['super_cashier'] = $super;
            
        } elseif ($role === 'owner') {
            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
            $stats['total_users'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $stats['total_admins'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'cashier'");
            $stats['total_cashiers'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
            $stats['total_customers'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'manager'");
            $stats['total_managers'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'collector'");
            $stats['total_collectors'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests");
            $stats['total_requests'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'completed'");
            $stats['total_revenue'] = $stmt->fetch()['total'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests WHERE status = 'pending'");
            $stats['pending_requests'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests WHERE status = 'completed'");
            $stats['completed_requests'] = $stmt->fetch()['count'];

        } elseif ($role === 'cashier' && $user_id) {
            // Assigned requests
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE assigned_to = ?");
            $stmt->execute([$user_id]);
            $stats['assigned_requests'] = $stmt->fetch()['count'];
            
            // Completed by me
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE assigned_to = ? AND status = 'completed'");
            $stmt->execute([$user_id]);
            $stats['completed_requests'] = $stmt->fetch()['count'];
            
            // Pending assigned
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE assigned_to = ? AND status = 'assigned'");
            $stmt->execute([$user_id]);
            $stats['pending_assigned'] = $stmt->fetch()['count'];
            
        } elseif ($role === 'manager' && $user_id) {
            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests");
            $stats['total_requests'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests WHERE status = 'pending'");
            $stats['pending_requests'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests WHERE status = 'assigned' OR status = 'in_progress'");
            $stats['active_requests'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM collection_requests WHERE status = 'completed'");
            $stats['completed_requests'] = $stmt->fetch()['count'];

            $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'completed'");
            $stats['total_revenue'] = $stmt->fetch()['total'];

            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'collector'");
            $stats['active_collectors'] = $stmt->fetch()['count'];
        } elseif ($role === 'collector' && $user_id) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE assigned_to = ?");
            $stmt->execute([$user_id]);
            $stats['assigned_requests'] = $stmt->fetch()['count'];

            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE assigned_to = ? AND status = 'completed'");
            $stmt->execute([$user_id]);
            $stats['completed_requests'] = $stmt->fetch()['count'];

            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE assigned_to = ? AND status = 'in_progress'");
            $stmt->execute([$user_id]);
            $stats['in_progress'] = $stmt->fetch()['count'];

            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE assigned_to = ? AND status = 'pending'");
            $stmt->execute([$user_id]);
            $stats['pending_requests'] = $stmt->fetch()['count'];
        } elseif ($role === 'customer' && $user_id) {
            // My requests
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE customer_id = ?");
            $stmt->execute([$user_id]);
            $stats['my_requests'] = $stmt->fetch()['count'];
            
            // Pending requests
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE customer_id = ? AND status = 'pending'");
            $stmt->execute([$user_id]);
            $stats['pending_requests'] = $stmt->fetch()['count'];
            
            // Completed requests
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM collection_requests WHERE customer_id = ? AND status = 'completed'");
            $stmt->execute([$user_id]);
            $stats['completed_requests'] = $stmt->fetch()['count'];
            
            // Total spent
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE customer_id = ? AND payment_status = 'completed'");
            $stmt->execute([$user_id]);
            $stats['total_spent'] = $stmt->fetch()['total'];
        }
    } catch (PDOException $e) {
        error_log("Stats fetch failed: " . $e->getMessage());
    }
    
    return $stats;
}
