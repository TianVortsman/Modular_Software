    const addUserModal = document.getElementById('add-user-modal');
    const closeUserModalButton = document.getElementById('close-user-modal');

    function openAddUserModal(accountNumber) {
        addUserModal.style.display = 'flex';
        document.getElementById("account_number").value = accountNumber;
    }

    function closeAddUserModal(){
        addUserModal.style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == addUserModal) {
            closeAddUserModal();
        }
    }