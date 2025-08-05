<?php
namespace App\modules\invoice\controllers;

use PDO;
use Exception;
use PDOException;
use Throwable;

require_once __DIR__ . '/../../../src/Helpers/helpers.php';

/**
 * Adds a new supplier
 */
function add_supplier(array $data, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'create_product')) {
        $msg = 'You do not have permission to add suppliers.';
        error_log('[add_supplier] ' . $msg . ' User: ' . $user_id);
        log_user_action($user_id, 'add_supplier', null, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "INSERT INTO inventory.supplier (supplier_name, supplier_email, supplier_address, supplier_contact, website_url) VALUES (:name, :email, :address, :contact, :website) RETURNING supplier_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'name' => $data['supplier_name'] ?? '',
            'email' => $data['supplier_email'] ?? null,
            'address' => $data['supplier_address'] ?? null,
            'contact' => $data['supplier_contact'] ?? null,
            'website' => $data['website_url'] ?? null
        ]);
        $supplier_id = $stmt->fetchColumn();
        log_user_action($user_id, 'add_supplier', $supplier_id, json_encode($data));
        return ['success' => true, 'message' => 'Supplier added', 'data' => ['supplier_id' => $supplier_id]];
    } catch (Throwable $e) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = $e->getMessage();
        $friendly = get_friendly_error($msg);
        $session_info = json_encode([
            'user_id' => $_SESSION['user_id'] ?? null,
            'tech_logged_in' => $_SESSION['tech_logged_in'] ?? null,
            'account_number' => $_SESSION['account_number'] ?? null
        ]);
        error_log('[add_supplier] ' . $msg . ' | SESSION: ' . $session_info);
        log_user_action($user_id, 'add_supplier', null, $msg . ' | SESSION: ' . $session_info);
        return [
            'success' => false,
            'message' => $friendly,
            'error' => $msg,
            'data' => null,
            'error_code' => 'SUPPLIER_ADD_ERROR'
        ];
    }
}

/**
 * Updates a supplier
 */
function update_supplier(int $supplier_id, array $data, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'update_product')) {
        $msg = 'You do not have permission to update suppliers.';
        error_log('[update_supplier] ' . $msg . ' User: ' . $user_id);
        log_user_action($user_id, 'update_supplier', $supplier_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "UPDATE inventory.supplier SET supplier_name = :name, supplier_email = :email, supplier_address = :address, supplier_contact = :contact, website_url = :website, updated_at = CURRENT_TIMESTAMP WHERE supplier_id = :supplier_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'supplier_id' => $supplier_id,
            'name' => $data['supplier_name'] ?? '',
            'email' => $data['supplier_email'] ?? null,
            'address' => $data['supplier_address'] ?? null,
            'contact' => $data['supplier_contact'] ?? null,
            'website' => $data['website_url'] ?? null
        ]);
        log_user_action($user_id, 'update_supplier', $supplier_id, json_encode($data));
        return ['success' => true, 'message' => 'Supplier updated', 'data' => ['supplier_id' => $supplier_id]];
    } catch (Throwable $e) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = $e->getMessage();
        $friendly = get_friendly_error($msg);
        $session_info = json_encode([
            'user_id' => $_SESSION['user_id'] ?? null,
            'tech_logged_in' => $_SESSION['tech_logged_in'] ?? null,
            'account_number' => $_SESSION['account_number'] ?? null
        ]);
        error_log('[update_supplier] ' . $msg . ' | SESSION: ' . $session_info);
        log_user_action($user_id, 'update_supplier', $supplier_id, $msg . ' | SESSION: ' . $session_info);
        return [
            'success' => false,
            'message' => $friendly,
            'error' => $msg,
            'data' => null,
            'error_code' => 'SUPPLIER_UPDATE_ERROR'
        ];
    }
}

/**
 * Deletes a supplier (soft delete)
 */
function delete_supplier(int $supplier_id, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'delete_product')) {
        $msg = 'You do not have permission to delete suppliers.';
        error_log('[delete_supplier] ' . $msg . ' User: ' . $user_id);
        log_user_action($user_id, 'delete_supplier', $supplier_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "UPDATE inventory.supplier SET deleted_at = CURRENT_TIMESTAMP WHERE supplier_id = :supplier_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['supplier_id' => $supplier_id]);
        log_user_action($user_id, 'delete_supplier', $supplier_id);
        return ['success' => true, 'message' => 'Supplier deleted', 'data' => ['supplier_id' => $supplier_id]];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        error_log('[delete_supplier] ' . $msg);
        log_user_action($user_id, 'delete_supplier', $supplier_id, $msg);
        return ['success' => false, 'message' => 'Failed to delete supplier', 'data' => null, 'error_code' => 'SUPPLIER_DELETE_ERROR'];
    }
}

/**
 * Gets a single supplier
 */
function get_supplier(int $supplier_id): array {
    global $conn;
    try {
        $sql = "SELECT supplier_id, supplier_name, supplier_email, supplier_address, supplier_contact, website_url FROM inventory.supplier WHERE supplier_id = :supplier_id AND deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['supplier_id' => $supplier_id]);
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$supplier) {
            $msg = 'Supplier not found';
            error_log('[get_supplier] ' . $msg);
            log_user_action(null, 'get_supplier', $supplier_id, $msg);
            return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'SUPPLIER_NOT_FOUND'];
        }
        return ['success' => true, 'message' => 'Supplier retrieved', 'data' => $supplier];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        $session_info = json_encode([
            'user_id' => $_SESSION['user_id'] ?? null,
            'tech_logged_in' => $_SESSION['tech_logged_in'] ?? null,
            'account_number' => $_SESSION['account_number'] ?? null
        ]);
        error_log('[get_supplier] ' . $msg . ' | SESSION: ' . $session_info);
        log_user_action(null, 'get_supplier', $supplier_id, $msg . ' | SESSION: ' . $session_info);
        return ['success' => false, 'message' => 'Failed to get supplier: ' . $msg, 'data' => null, 'error_code' => 'SUPPLIER_GET_ERROR'];
    }
}

/**
 * Lists all suppliers
 */
function list_suppliers(): array {
    global $conn;
    try {
        $sql = "SELECT supplier_id, supplier_name, supplier_email, supplier_address, supplier_contact, website_url FROM inventory.supplier WHERE deleted_at IS NULL ORDER BY supplier_name";
        $stmt = $conn->query($sql);
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['success' => true, 'message' => 'Suppliers retrieved', 'data' => $suppliers];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        $session_info = json_encode([
            'user_id' => $_SESSION['user_id'] ?? null,
            'tech_logged_in' => $_SESSION['tech_logged_in'] ?? null,
            'account_number' => $_SESSION['account_number'] ?? null
        ]);
        error_log('[list_suppliers] ' . $msg . ' | SESSION: ' . $session_info);
        log_user_action(null, 'list_suppliers', null, $msg . ' | SESSION: ' . $session_info);
        return ['success' => false, 'message' => 'Failed to list suppliers: ' . $msg, 'data' => null, 'error_code' => 'SUPPLIER_LIST_ERROR'];
    }
}

/**
 * Links a product to a supplier
 */
function link_product_to_supplier(int $product_id, int $supplier_id, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'update_product')) {
        $msg = 'Permission denied';
        error_log('[link_product_to_supplier] ' . $msg);
        log_user_action($user_id, 'link_product_to_supplier', $product_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "INSERT INTO inventory.product_supplier (product_id, supplier_id) VALUES (:product_id, :supplier_id) ON CONFLICT DO NOTHING";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['product_id' => $product_id, 'supplier_id' => $supplier_id]);
        log_user_action($user_id, 'link_product_to_supplier', $product_id, $supplier_id);
        return ['success' => true, 'message' => 'Product linked to supplier', 'data' => ['product_id' => $product_id, 'supplier_id' => $supplier_id]];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        error_log('[link_product_to_supplier] ' . $msg);
        log_user_action($user_id, 'link_product_to_supplier', $product_id, $msg);
        return ['success' => false, 'message' => 'Failed to link product to supplier', 'data' => null, 'error_code' => 'PRODUCT_SUPPLIER_LINK_ERROR'];
    }
}

/**
 * Unlinks a product from a supplier
 */
function unlink_product_from_supplier(int $product_id, int $supplier_id, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'update_product')) {
        $msg = 'Permission denied';
        error_log('[unlink_product_from_supplier] ' . $msg);
        log_user_action($user_id, 'unlink_product_from_supplier', $product_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "DELETE FROM inventory.product_supplier WHERE product_id = :product_id AND supplier_id = :supplier_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['product_id' => $product_id, 'supplier_id' => $supplier_id]);
        log_user_action($user_id, 'unlink_product_from_supplier', $product_id, $supplier_id);
        return ['success' => true, 'message' => 'Product unlinked from supplier', 'data' => ['product_id' => $product_id, 'supplier_id' => $supplier_id]];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        error_log('[unlink_product_from_supplier] ' . $msg);
        log_user_action($user_id, 'unlink_product_from_supplier', $product_id, $msg);
        return ['success' => false, 'message' => 'Failed to unlink product from supplier', 'data' => null, 'error_code' => 'PRODUCT_SUPPLIER_UNLINK_ERROR'];
    }
}

/**
 * Gets all suppliers for a product
 */
function get_product_suppliers(int $product_id): array {
    global $conn;
    try {
        $sql = "SELECT s.* FROM inventory.supplier s JOIN inventory.product_supplier ps ON s.supplier_id = ps.supplier_id WHERE ps.product_id = :product_id AND s.deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['product_id' => $product_id]);
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['success' => true, 'message' => 'Product suppliers retrieved', 'data' => $suppliers];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        error_log('[get_product_suppliers] ' . $msg);
        log_user_action(null, 'get_product_suppliers', $product_id, $msg);
        return ['success' => false, 'message' => 'Failed to get product suppliers', 'data' => null, 'error_code' => 'PRODUCT_SUPPLIERS_GET_ERROR'];
    }
}

/**
 * Adds a price history record for a product-supplier
 */
function add_supplier_price_history(int $product_id, int $supplier_id, float $purchase_price, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'update_product')) {
        $msg = 'Permission denied';
        error_log('[add_supplier_price_history] ' . $msg);
        log_user_action($user_id, 'add_supplier_price_history', $product_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "INSERT INTO inventory.product_supplier_price_history (product_id, supplier_id, purchase_price) VALUES (:product_id, :supplier_id, :purchase_price)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'product_id' => $product_id,
            'supplier_id' => $supplier_id,
            'purchase_price' => $purchase_price
        ]);
        log_user_action($user_id, 'add_supplier_price_history', $product_id, ['supplier_id' => $supplier_id, 'purchase_price' => $purchase_price]);
        return ['success' => true, 'message' => 'Supplier price history added', 'data' => null];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        error_log('[add_supplier_price_history] ' . $msg);
        log_user_action($user_id, 'add_supplier_price_history', $product_id, $msg);
        return ['success' => false, 'message' => 'Failed to add supplier price history', 'data' => null, 'error_code' => 'SUPPLIER_PRICE_HISTORY_ERROR'];
    }
}

/**
 * Adds a contact person for a supplier
 */
function add_supplier_contact(array $data, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'create_product')) {
        $msg = 'You do not have permission to add supplier contacts.';
        error_log('[add_supplier_contact] ' . $msg . ' User: ' . $user_id);
        log_user_action($user_id, 'add_supplier_contact', null, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    $supplier_id = $data['supplier_id'] ?? null;
    $full_name = trim($data['full_name'] ?? '');
    if (!$supplier_id || $full_name === '') {
        return ['success' => false, 'message' => 'Supplier and full name required'];
    }
    try {
        $sql = "INSERT INTO inventory.supplier_contact_person (supplier_id, full_name, position, email, phone, notes) VALUES (:supplier_id, :full_name, :position, :email, :phone, :notes) RETURNING contact_person_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'supplier_id' => $supplier_id,
            'full_name' => $full_name,
            'position' => $data['position'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
        $contact_id = $stmt->fetchColumn();
        return ['success' => true, 'message' => 'Contact added', 'data' => ['contact_person_id' => $contact_id]];
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => 'Failed to add contact', 'error' => $e->getMessage()];
    }
}

/**
 * Updates a contact person for a supplier
 */
function update_supplier_contact(int $contact_id, array $data, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'update_product')) {
        $msg = 'You do not have permission to update supplier contacts.';
        error_log('[update_supplier_contact] ' . $msg . ' User: ' . $user_id);
        log_user_action($user_id, 'update_supplier_contact', $contact_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    $full_name = trim($data['full_name'] ?? '');
    if ($full_name === '') {
        return ['success' => false, 'message' => 'Full name required'];
    }
    try {
        $sql = "UPDATE inventory.supplier_contact_person SET full_name = :full_name, position = :position, email = :email, phone = :phone, notes = :notes, updated_at = CURRENT_TIMESTAMP WHERE contact_person_id = :contact_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'contact_id' => $contact_id,
            'full_name' => $full_name,
            'position' => $data['position'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
        return ['success' => true, 'message' => 'Contact updated', 'data' => ['contact_person_id' => $contact_id]];
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => 'Failed to update contact', 'error' => $e->getMessage()];
    }
}

/**
 * Deletes a contact person for a supplier
 */
function delete_supplier_contact(int $contact_id, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'delete_product')) {
        $msg = 'You do not have permission to delete supplier contacts.';
        error_log('[delete_supplier_contact] ' . $msg . ' User: ' . $user_id);
        log_user_action($user_id, 'delete_supplier_contact', $contact_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "DELETE FROM inventory.supplier_contact_person WHERE contact_person_id = :contact_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['contact_id' => $contact_id]);
        return ['success' => true, 'message' => 'Contact deleted'];
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => 'Failed to delete contact', 'error' => $e->getMessage()];
    }
}

/**
 * Gets all contact persons for a supplier
 */
function get_supplier_contacts(int $supplier_id): array {
    global $conn;
    try {
        $sql = "SELECT * FROM inventory.supplier_contact_person WHERE supplier_id = :supplier_id ORDER BY full_name";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['supplier_id' => $supplier_id]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['success' => true, 'data' => $contacts];
    } catch (\Throwable $e) {
        return ['success' => false, 'message' => 'Failed to get contacts', 'error' => $e->getMessage()];
    }
} 