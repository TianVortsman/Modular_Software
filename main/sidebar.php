<nav class="modular-sidebar" id="sidebar">
        <!-- Sidebar Logo as Toggle Button -->
        <div class="modular-logo" id="sidebarToggle">
            <div class="animated-circle">
            <a><img src="/modular1/img/logo.webp" alt="Logo"></a>
            </div>
        </div>
            <div class="modular-user-info">
                <p><strong>User:</strong> <?= htmlspecialchars($userName) ?></p> <!-- Dynamic User Name -->
                <p onclick="checkMultipleAccounts()"><strong  >Account:</strong> <?= htmlspecialchars($account_number)?></p> <!-- Dynamic Account Number -->
            </div>
        <ul class="modular-nav-items">
            <li><a href="/modular1/main/dashboard.php"><i class="material-icons nav-icon" id="home-button" >home</i> <span class="nav-text">Home</span></a></li>
            <li><a href="/modular1/main/settings.php"><i class="material-icons nav-icon" id="settings-button" >settings</i> <span class="nav-text">Settings</span></a></li>
            <li><a href="pages/export.html"><i class="material-icons nav-icon" id="import-button" >upload</i> <span class="nav-text">Exporting</span></a></li>
            <li><a href="pages/import.html"><i class="material-icons nav-icon" id="export-button" >download</i> <span class="nav-text">Importing</span></a></li>
            <li><a href="../php/logout.php"><i class="material-icons nav-icon" id="exit-button" >exit_to_app</i> <span class="nav-text">LogOut</span></a></li> 
        </ul>
    </nav>