# Add User for Client – Implementation Notes

## 1. Frontend (techlogin.php)
- **Customer Modal:**
  - The "Add New User" button in the Users Management tab should open a new modal for adding a user to the selected client.
  - Modal fields: Name, Email, (optional) Role, and any other required fields.
  - On submit, send data via AJAX/fetch to a new API endpoint (see Backend section).
  - After success, refresh the users list in the modal.

## 2. Backend/API
- **API Endpoint:**
  - Create a new endpoint (e.g., `src/api/customer.php?action=add_user` or similar) to handle user creation for a client.
  - This endpoint should:
    1. Insert the user into the main DB (`public.users` table in `modular_system`):
        - Fields: name, email, role (not technician), customer_id, password (hashed, standard default), status, created_at.
        - Set a flag or status so the user must change their password on first login (see below).
    2. Insert the user into the client DB (`core.users` table in the client's database):
        - Use the client DB connection (see `DatabaseService::getClientDatabase`).
        - Insert user_id (from main DB), user_name, role, created_at.
    3. Assign all active modules for the client (from `customer_modules` in main DB) to the new user in the client DB (`core.user_modules`).
        - For each active module, insert a row for the new user in `core.user_modules`.
    4. Optionally, send a password reset email or notification.

- **Password/First Login:**
  - Set a standard default password (e.g., `changeme123!`).
  - User must change password on first login:
    -     is_first_login boolean DEFAULT true, will be true untill user logs in and changes password flag changes to false.
    - On login, if this flag is set, redirect to password change flow.

## 3. Database
- **Main DB (`modular_system`):**
  - Table: `public.users`
    - Insert new user with all required fields.
    - Link to customer via `customer_id`.
  - Table: `public.account_number`
    - Link user to account number if needed.
  - Table: `public.customer_modules`
    - Query for all active modules for the customer.

- **Client DB (per client, e.g., `ACC001`):**
  - Table: `core.users`
    - Insert user_id (from main DB), user_name, role, created_at.
  - Table: `core.user_modules`
    - For each active module, insert (user_id, module_name, enabled=true).

## 4. Integration Points
- **Frontend:**
  - Add modal HTML and JS logic for opening, submitting, and closing the add user modal.
  - Validate input before sending.
  - Show errors/success using ResponseModal/LoadingModal.

- **Backend:**
  - New API endpoint for user creation.
  - Use `DatabaseService` to connect to both main and client DBs.
  - Use secure password hashing.
  - Handle errors gracefully and return clear JSON responses.

- **Password Reset/First Login:**
  - Ensure login flow checks for first login status and enforces password change "    is_first_login boolean DEFAULT true,"
  - Optionally, send email with reset link or instructions.

## 5. Files/Classes to Update or Reference
- `Modular/public/admin/techlogin.php` (modal UI, JS event)
- `Modular/public/assets/js/techlogin.js` (modal logic, API call)
- `Modular/src/api/customer.php` (new action for add_user)
- `Modular/src/Controllers/CustomerController.php` (add method for user creation)
- `Modular/src/Services/DatabaseService.php` (for DB connections)
- `Modular/src/Core/Database/ClientDatabase.php` (client DB logic)
- `db-init/Main-db/modular_system.sql` (main DB schema)
- `db-init/Client-db/Core.sql` (client DB schema)

## 6. Security/Validation
- Validate all input (name, email, etc.) on both frontend and backend.
- Ensure email is unique per customer.
- Use strong password hashing (bcrypt).
- Do not allow technician role to be assigned from this modal.

## 7. User Experience
- After adding, show success or error using ResponseModal.
- Refresh users list in modal after successful add.
- If error, show error modal with details.

---

**Summary:**
- Add user modal in techlogin.php customer modal.
- On submit, create user in both main and client DBs, assign all active modules, set default password, enforce password change on first login.
- Update all relevant files and ensure smooth, secure, and user-friendly flow. 