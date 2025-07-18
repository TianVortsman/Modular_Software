<?php
namespace App\modules\invoice\controllers;

use PDO;
use Exception;

require_once __DIR__ . '/../../../src/Helpers/helpers.php';

// CREDIT REASONS
function list_credit_reasons(): array {
    global $conn;
    try {
        $stmt = $conn->query('SELECT * FROM settings.credit_reasons ORDER BY credit_reason_id');
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'message' => 'Credit reasons loaded', 'data' => $data ];
    } catch (Exception $e) {
        error_log('[list_credit_reasons] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to load credit reasons', 'data' => null ];
    }
}

function add_credit_reason($data): array {
    global $conn;
    $reason = trim($data['reason'] ?? '');
    if ($reason === '') {
        return [ 'success' => false, 'message' => 'Reason is required', 'data' => null ];
    }
    try {
        $stmt = $conn->prepare('INSERT INTO settings.credit_reasons (reason) VALUES (:reason) RETURNING credit_reason_id');
        $stmt->bindValue(':reason', $reason);
        $stmt->execute();
        $id = $stmt->fetchColumn();
        return [ 'success' => true, 'message' => 'Credit reason added', 'data' => [ 'credit_reason_id' => $id ] ];
    } catch (Exception $e) {
        error_log('[add_credit_reason] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to add credit reason', 'data' => null ];
    }
}

function update_credit_reason($id, $data): array {
    global $conn;
    $reason = trim($data['reason'] ?? '');
    if ($reason === '') {
        return [ 'success' => false, 'message' => 'Reason is required', 'data' => null ];
    }
    try {
        $stmt = $conn->prepare('UPDATE settings.credit_reasons SET reason = :reason, updated_at = NOW() WHERE credit_reason_id = :id');
        $stmt->bindValue(':reason', $reason);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return [ 'success' => true, 'message' => 'Credit reason updated', 'data' => [ 'credit_reason_id' => $id ] ];
    } catch (Exception $e) {
        error_log('[update_credit_reason] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to update credit reason', 'data' => null ];
    }
}

function delete_credit_reason($id): array {
    global $conn;
    try {
        $stmt = $conn->prepare('DELETE FROM settings.credit_reasons WHERE credit_reason_id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return [ 'success' => true, 'message' => 'Credit reason deleted', 'data' => [ 'credit_reason_id' => $id ] ];
    } catch (Exception $e) {
        error_log('[delete_credit_reason] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to delete credit reason', 'data' => null ];
    }
}

// PAYMENT TERMS
function list_payment_terms(): array {
    global $conn;
    try {
        $stmt = $conn->query('SELECT * FROM settings.payment_terms ORDER BY payment_term_id');
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'message' => 'Payment terms loaded', 'data' => $data ];
    } catch (Exception $e) {
        error_log('[list_payment_terms] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to load payment terms', 'data' => null ];
    }
}

function add_payment_term($data): array {
    global $conn;
    $term_name = trim($data['term_name'] ?? '');
    $days_due = isset($data['days_due']) ? (int)$data['days_due'] : null;
    if ($term_name === '' || !$days_due) {
        return [ 'success' => false, 'message' => 'Term name and days due are required', 'data' => null ];
    }
    $is_default = !empty($data['is_default']);
    try {
        if ($is_default) {
            $conn->exec('UPDATE settings.payment_terms SET is_default = FALSE');
        }
        $stmt = $conn->prepare('INSERT INTO settings.payment_terms (term_name, days_due, is_default) VALUES (:term_name, :days_due, :is_default) RETURNING payment_term_id');
        $stmt->bindValue(':term_name', $term_name);
        $stmt->bindValue(':days_due', $days_due, PDO::PARAM_INT);
        $stmt->bindValue(':is_default', $is_default, PDO::PARAM_BOOL);
        $stmt->execute();
        $id = $stmt->fetchColumn();
        return [ 'success' => true, 'message' => 'Payment term added', 'data' => [ 'payment_term_id' => $id ] ];
    } catch (Exception $e) {
        error_log('[add_payment_term] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to add payment term', 'data' => null ];
    }
}

function update_payment_term($id, $data): array {
    global $conn;
    $term_name = trim($data['term_name'] ?? '');
    $days_due = isset($data['days_due']) ? (int)$data['days_due'] : null;
    if ($term_name === '' || !$days_due) {
        return [ 'success' => false, 'message' => 'Term name and days due are required', 'data' => null ];
    }
    $is_default = !empty($data['is_default']);
    try {
        if ($is_default) {
            $conn->exec('UPDATE settings.payment_terms SET is_default = FALSE');
        }
        $stmt = $conn->prepare('UPDATE settings.payment_terms SET term_name = :term_name, days_due = :days_due, is_default = :is_default, updated_at = NOW() WHERE payment_term_id = :id');
        $stmt->bindValue(':term_name', $term_name);
        $stmt->bindValue(':days_due', $days_due, PDO::PARAM_INT);
        $stmt->bindValue(':is_default', $is_default, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return [ 'success' => true, 'message' => 'Payment term updated', 'data' => [ 'payment_term_id' => $id ] ];
    } catch (Exception $e) {
        error_log('[update_payment_term] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to update payment term', 'data' => null ];
    }
}

function delete_payment_term($id): array {
    global $conn;
    try {
        $stmt = $conn->prepare('DELETE FROM settings.payment_terms WHERE payment_term_id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return [ 'success' => true, 'message' => 'Payment term deleted', 'data' => [ 'payment_term_id' => $id ] ];
    } catch (Exception $e) {
        error_log('[delete_payment_term] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to delete payment term', 'data' => null ];
    }
}

// CREDIT POLICY
function get_credit_policy(): array {
    global $conn;
    try {
        $stmt = $conn->query('SELECT allow_credit_notes, require_approval FROM settings.invoice_settings LIMIT 1');
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'message' => 'Credit policy loaded', 'data' => $data ];
    } catch (Exception $e) {
        error_log('[get_credit_policy] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to load credit policy', 'data' => null ];
    }
}

function save_credit_policy($data): array {
    global $conn;
    $allow = isset($data['allow_credit_notes']) && ($data['allow_credit_notes'] === 'on' || $data['allow_credit_notes'] === 'true' || $data['allow_credit_notes'] == 1) ? 1 : 0;
    $approval = isset($data['require_approval']) && ($data['require_approval'] === 'on' || $data['require_approval'] === 'true' || $data['require_approval'] == 1) ? 1 : 0;
    try {
        $stmt = $conn->prepare('UPDATE settings.invoice_settings SET allow_credit_notes = :allow, require_approval = :approval, updated_at = NOW() WHERE id = (SELECT id FROM settings.invoice_settings LIMIT 1)');
        $stmt->bindValue(':allow', $allow, PDO::PARAM_BOOL);
        $stmt->bindValue(':approval', $approval, PDO::PARAM_BOOL);
        $stmt->execute();
        return [ 'success' => true, 'message' => 'Credit policy saved', 'data' => [ 'allow_credit_notes' => $allow, 'require_approval' => $approval ] ];
    } catch (Exception $e) {
        error_log('[save_credit_policy] ' . $e->getMessage());
        return [ 'success' => false, 'message' => 'Failed to save credit policy', 'data' => null ];
    }
} 