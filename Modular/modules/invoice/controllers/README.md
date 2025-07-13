# Invoice Module Controllers: Standards & Best Practices

This README documents the standards and conventions for all controllers in the `Modular/modules/invoice/controllers/` directory. All controllers should follow these guidelines to ensure maintainability, security, and a consistent developer and user experience.

---

## 1. **Response Structure**
- All controller functions must return an associative array with at least:
  - `success` (bool): Operation result
  - `message` (string): User-friendly message
  - `data` (mixed): Result data or `null`
  - `error_code` (string|null): Machine-readable error code (for frontend modals)
  - `errors` (array, optional): For bulk/multi operations, array of error details

## 2. **Error Handling**
- All errors must be logged using `error_log()` and `log_user_action()`.
- Use try/catch blocks for all DB and critical logic.
- Roll back DB transactions on error, commit only on full success.
- Return user-friendly error messages, never raw exception traces.
- All error/success responses must be compatible with frontend modals:
  - Use `response-modal.php` for single errors/success
  - Use `error-table-modal.php` for bulk/multi errors

## 3. **Permissions**
- Use `check_user_permission($user_id, $action)` for all sensitive actions.
- Technicians (`$_SESSION['tech_logged_in']`) have master permissions.
- Map product/document actions to the correct permission keys (see `Permissions.invoicing` schema).

## 4. **Input Validation**
- Validate all required fields before DB operations.
- Use a shared validation helper (e.g., `validate_product_data`).
- Return clear validation errors to the frontend.

## 5. **Logging & Notifications**
- Log all user actions with `log_user_action()`.
- Send user notifications with `send_notification()` after successful actions.
- Log all errors to both file and DB for traceability.

## 6. **Bulk Operations**
- Bulk actions (e.g., `bulk_delete_products`) must:
  - Return both a summary and a detailed error array
  - Handle partial failures gracefully
  - Roll back on total failure

## 7. **Inventory & Supplier Integration**
- Product add/update flows must integrate inventory and supplier logic as per schema.
- Use helper functions for inventory/supplier DB operations.

## 8. **General Coding Practices**
- Use async/await or Promises for API calls (frontend JS).
- Keep code modular and DRY (no duplication).
- Use clear, explicit variable and function names.
- Always sanitize and validate user input.

---

## 9. **List Functions & $options Parameter**
- All list/retrieve functions (e.g., `list_products`, `list_documents`) should accept a single `$options` associative array parameter.
- This `$options` array is built on the frontend using the `buildQueryParams` helper in `public/assets/js/helpers.js`.
- Typical keys in `$options` include:
  - `search` (string): Search term
  - `filters` (array): Key-value pairs for filtering (e.g., `status`, `type`)
  - `pagination` (array): `page`, `limit`
  - `sorting` (array): `sort_by`, `sort_dir`
- The backend should extract and sanitize these options, applying them to SQL queries for filtering, searching, sorting, and pagination.
- **Example usage:**
  ```php
  function list_products(array $options = []): array {
      $search = $options['search'] ?? null;
      $status = $options['status'] ?? null;
      $page   = (int)($options['page'] ?? 1);
      $limit  = (int)($options['limit'] ?? 20);
      // ... build SQL ...
  }
  ```
- **Best Practices:**
  - Always whitelist allowed sort/filter fields.
  - Validate and sanitize all option values before using in queries.
  - Return paginated results with `data`, `total`, `page`, and `limit` keys.

---

**All new and refactored controllers must adhere to these standards.**

For questions or to propose changes, contact the lead developer or open a PR with your suggested update. 