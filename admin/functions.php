<?php
/**
 * Admin Functions
 * Contains utility functions for the admin panel
 */

/**
 * Get username from user ID
 * @param int $userId - The user ID to lookup
 * @return string - The username or 'Unknown User' if not found
 */
function ausername($userId) {
    global $conn; // Ensure the database connection variable is available
    $query = "SELECT username FROM users WHERE id = ?";
    // Use prepared statements for security
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['username'] ?? 'Unknown User';
}
?>
