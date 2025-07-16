<?php
namespace App\modules\invoice\controllers;

use PDO;
use Exception;
use PDOExeption;


    // Record payment
    // need the invoice_id and the payment_method_id
    // need the amount paid and the payment date
    // need the payment_number and the payment_status
    // need the payment_notes and the payment_reference
    // need the payment_created_by and the payment_created_at
    // Ensure only authorized users can add payments

    function create_payment(int $document_id, array $data): ?int {
        global $conn;
        // Map $data fields to variables for clarity and validation
        $document_id = $data['document_id'] ?? null;
        $payment_date = $data['payment_date'] ?? null;
        $payment_amount = $data['payment_amount'] ?? null;
        $payment_type = $data['payment_type'] ?? 'payment';
        $related_document_type = $data['related_document_type'] ?? null;

        // Validate required fields
        if (!$document_id || !$payment_date || !$payment_amount) {
            error_log("create_payment: Missing required fields");
            return null;
        }

        // Prepare SQL for inserting into invoice_payment
        $sql = "INSERT INTO invoicing.invoice_payment (
                    document_id,
                    payment_date,
                    payment_amount,
                    payment_type,
                    related_document_type
                ) VALUES (
                    :document_id,
                    :payment_date,
                    :payment_amount,
                    :payment_type,
                    :related_document_type
                ) RETURNING document_payment_id";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $stmt->bindValue(':payment_date', $payment_date);
            $stmt->bindValue(':payment_amount', $payment_amount);
            $stmt->bindValue(':payment_type', $payment_type);
            $stmt->bindValue(':related_document_type', $related_document_type);

            $stmt->execute();
            $result = $stmt->fetchColumn();
            return $result ? (int)$result : null;
        } catch (\PDOException $e) {
            error_log("create_payment error: " . $e->getMessage());
            return null;
        }
    }




// Record refund
// need the invoice_id and the payment_method_id
// need the amount refunded and the refund date
// need the refund_number and the refund_status
// need the refund_notes and the refund_reference
// need the refund_created_by and the refund_created_at




// Get credit note application 





// Create credit note 




// Apply credit note 




// Update balances after payment / refund / credit note 



// Update document status after payment / refund / credit note e.g., to "Paid", "Refunded", "Credited", etc.).




