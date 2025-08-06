<?php
// Include the SidebarManager class
require_once __DIR__ . '/SidebarManager.php';

// Create instance of SidebarManager
$sidebarManager = new App\UI\SidebarManager();

// Get user info
$userName = $sidebarManager->getUserName();
$accountNumber = $sidebarManager->getAccountNumber();
$notificationCount = $sidebarManager->getNotificationCount();
$userRole = $sidebarManager->getUserRole();
$logoutUrl = $sidebarManager->getLogoutUrl();
$accountClickBehavior = $sidebarManager->getAccountClickBehavior();
$accountCssClass = $sidebarManager->getAccountCssClass();
$isAccountClickable = $sidebarManager->isAccountClickable();
?>
<nav class="modular-sidebar" id="sidebar">
        <!-- Sidebar Logo as Toggle Button -->
        <div class="modular-logo" id="sidebarToggle">
            <div class="animated-circle">
            <a><img src="/public/assets/img/logo.webp" alt="Logo"></a>
            </div>
        </div>
            <div class="modular-user-info">
                <p><strong><?= htmlspecialchars($userRole) ?>:</strong> <?= htmlspecialchars($userName) ?></p>
                <?php if ($isAccountClickable): ?>
                    <p class="<?= $accountCssClass ?>">
                        <strong>Account:</strong> <a href="/public/account/choose-account.php" style="text-decoration: none; color: inherit;"><?= htmlspecialchars($accountNumber) ?></a>
                    </p>
                <?php else: ?>
                    <p><strong>Account:</strong> <?= htmlspecialchars($accountNumber) ?></p>
                <?php endif; ?>
                <!-- WhatsApp Button -->
                <div class="whatsapp-button" id="whatsapp-button" title="WhatsApp Status">
                    <i class="material-icons">whatsapp</i>
                    <span class="whatsapp-status-indicator" id="whatsapp-status-indicator"></span>
                </div>
                <!-- Tutorial Button -->
                <div class="tutorial-button" id="tutorial-button">
                    <i class="material-icons">help_outline</i>
                </div>
                <!-- Notifications Bell -->
                <div class="notification-bell" id="notification-bell">
                    <i class="material-icons">notifications</i>
                    <span class="notification-badge" id="notification-count"><?= $notificationCount ?></span>
                </div>
            </div>
        <ul class="modular-nav-items">
            <li><a href="/public/views/dashboard.php"><i class="material-icons nav-icon" id="home-button" >home</i> <span class="nav-text">Home</span></a></li>
            <li><a href="/public/views/settings.php"><i class="material-icons nav-icon" id="settings-button" >settings</i> <span class="nav-text">Settings</span></a></li>
            <li><a href="/public/views/export.php"><i class="material-icons nav-icon" id="import-button" >upload</i> <span class="nav-text">Exporting</span></a></li>
            <li><a href="/public/views/import.php"><i class="material-icons nav-icon" id="export-button" >download</i> <span class="nav-text">Importing</span></a></li>
            <li><a href="#" onclick="startTutorialForCurrentPage()"><i class="material-icons nav-icon" id="tutorial-button">help_outline</i> <span class="nav-text">Tutorial</span></a></li>
            <li><a href="<?= $logoutUrl ?>"><i class="material-icons nav-icon" id="exit-button" >exit_to_app</i> <span class="nav-text">LogOut</span></a></li> 
        </ul>
    </nav>

    <!-- Add CSS for clickable account -->
    <style>
        .clickable-account {
            cursor: pointer;
            text-decoration: underline;
        }
        
        .clickable-account:hover {
            opacity: 0.8;
        }
    </style>

    <!-- Notifications Modal -->
    <div id="notifications-modal" class="notifications-modal hidden">
        <div class="notifications-content">
            <div class="notifications-header">
                <h2>Notifications</h2>
                <div class="notifications-actions">
                    <button id="mark-all-read" class="mark-all-read">Mark All as Read</button>
                    <span class="close-notifications">&times;</span>
                </div>
            </div>
            <div class="notifications-tabs">
                <button class="tab-button active" data-tab="all">All</button>
                <button class="tab-button" data-tab="unread">Unread</button>
                <button class="tab-button" data-tab="system">System</button>
                <button class="tab-button" data-tab="alerts">Alerts</button>
            </div>
            <div class="notifications-body">
                <div id="notifications-list" class="notifications-list">
                    <!-- Notifications will be populated here by JavaScript -->
                    <div class="no-notifications">No notifications to display</div>
                </div>
            </div>
            <div class="notifications-footer">
                <button id="load-more-notifications" class="load-more">Load More</button>
            </div>
        </div>
    </div>

    <!-- WhatsApp QR Code Modal -->
    <div id="whatsapp-modal" class="whatsapp-modal hidden">
        <div class="whatsapp-content">
            <div class="whatsapp-header">
                <h2>WhatsApp Connection</h2>
                <span class="close-whatsapp">&times;</span>
            </div>
            <div class="whatsapp-body">
                <div class="whatsapp-status" id="whatsapp-status">
                    <div class="status-indicator">
                        <span class="status-dot" id="whatsapp-status-dot"></span>
                        <span class="status-text" id="whatsapp-status-text">Checking status...</span>
                    </div>
                </div>
                <div class="qr-container" id="qr-container">
                    <div class="qr-placeholder">
                        <i class="material-icons">qr_code</i>
                        <p>QR Code will appear here</p>
                    </div>
                    <img id="qr-code-image" class="qr-code-image hidden" alt="WhatsApp QR Code">
                </div>
                <div class="whatsapp-actions">
                    <button id="initialize-whatsapp" class="btn btn-primary">Initialize WhatsApp</button>
                    <button id="logout-whatsapp" class="btn btn-secondary hidden">Logout WhatsApp</button>
                    <button id="refresh-qr" class="btn btn-outline hidden">Refresh QR Code</button>
                </div>
                <div class="whatsapp-info">
                    <p><strong>Instructions:</strong></p>
                    <ol>
                        <li>Click "Initialize WhatsApp" to generate a QR code</li>
                        <li>Open WhatsApp on your phone</li>
                        <li>Go to Settings > Linked Devices > Link a Device</li>
                        <li>Scan the QR code with your phone</li>
                        <li>Wait for the connection to be established</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Styles -->
    <style>
        /* Notification Bell Styles */
        .notification-bell {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            color: var(--color-text-light);
            transition: color 0.3s ease;
        }
        
        .notification-bell:hover {
            color: var(--color-primary);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--color-primary);
            color: var(--color-text-dark);
            font-size: 10px;
            font-weight: bold;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* Notifications Modal Styles */
        .notifications-modal {
            position: fixed;
            top: 0;
            right: 0;
            width: 350px;
            height: 100%;
            background-color: var(--color-background);
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.3);
            z-index: 1001;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .notifications-modal.visible {
            transform: translateX(0);
        }
        
        .notifications-modal.hidden {
            transform: translateX(100%);
        }
        
        .notifications-content {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .notifications-header h2 {
            margin: 0;
            color: var(--color-primary);
            font-size: 1.3rem;
        }
        
        .notifications-actions {
            display: flex;
            align-items: center;
        }
        
        .mark-all-read {
            background: none;
            border: none;
            color: var(--color-primary);
            cursor: pointer;
            font-size: 0.8rem;
            margin-right: 10px;
        }
        
        .close-notifications {
            font-size: 1.5rem;
            color: var(--color-text-light);
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .close-notifications:hover {
            color: var(--color-primary);
        }
        
        .notifications-tabs {
            display: flex;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0 15px;
        }
        
        .tab-button {
            background: none;
            border: none;
            color: var(--color-text-light);
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            color: var(--color-text-dark);
            border-bottom: 2px solid var(--color-primary);
        }
        
        .notifications-body {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }
        
        .notifications-list {
            display: flex;
            flex-direction: column;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: flex;
            flex-direction: column;
        }
        
        .notification-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .notification-item.unread {
            background-color: rgba(var(--color-primary-rgb), 0.1);
            border-left: 3px solid var(--color-primary);
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .notification-title {
            font-weight: 500;
            color: var(--color-text-light);
        }
        
        .notification-time {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .notification-message {
            font-size: 0.9rem;
            color: var(--color-text-light);
            opacity: 0.9;
        }
        
        .notification-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
            font-size: 0.8rem;
        }
        
        .notification-source {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
        }
        
        .notification-action {
            color: var(--color-primary);
            cursor: pointer;
        }
        
        .no-notifications {
            padding: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-style: italic;
        }
        
        .notifications-footer {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .load-more {
            background: none;
            border: 1px solid var(--color-primary);
            color: var(--color-primary);
            padding: 8px 15px;
            border-radius: var(--radius-small);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .load-more:hover {
            background-color: rgba(var(--color-primary-rgb), 0.1);
        }
        
        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .notifications-modal {
                width: 100%;
            }
        }
    </style>

    <!-- Add Tutorial Button Styles -->
    <style>
        /* Tutorial Button Styles */
        .tutorial-button {
            position: absolute;
            top: 15px;
            right: 45px; /* Position it to the left of the notification bell */
            cursor: pointer;
            color: var(--color-text-light);
            transition: color 0.3s ease;
        }
        
        .tutorial-button:hover {
            color: var(--color-primary);
        }
        
        /* When tutorial is completed */
        [data-tutorial-completed="true"] .tutorial-button {
            opacity: 0.5;
        }
        
        /* WhatsApp Button Styles */
        .whatsapp-button {
            position: absolute;
            top: 15px;
            right: 75px; /* Position it to the left of the tutorial button */
            cursor: pointer;
            color: var(--color-text-light);
            transition: color 0.3s ease;
        }
        
        .whatsapp-button:hover {
            color: #25D366; /* WhatsApp green */
        }
        
        .whatsapp-status-indicator {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ccc;
            border: 1px solid var(--color-background);
        }
        
        .whatsapp-status-indicator.connected {
            background: #25D366;
        }
        
        .whatsapp-status-indicator.disconnected {
            background: #dc3545;
        }
        
        .whatsapp-status-indicator.qr-ready {
            background: #ffc107;
        }
        
        /* WhatsApp Modal Styles */
        .whatsapp-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1002;
            pointer-events: auto;
            backdrop-filter: blur(2px);
            transition: opacity 0.3s ease-in-out;
        }
        
        .whatsapp-modal.hidden {
            display: none !important;
        }
        
        .whatsapp-content {
            background: var(--color-background);
            color: var(--color-text-dark);
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 450px;
            min-width: 350px;
            animation: slide-down 0.3s ease-in-out;
            position: relative;
            z-index: 1003;
            border: 1px solid #e0e0e0;
        }
        
        .whatsapp-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            background: #f8f9fa;
        }
        
        .whatsapp-header h2 {
            margin: 0;
            color: #25D366;
            font-size: 1.3rem;
        }
        
        .close-whatsapp {
            font-size: 1.5rem;
            color: var(--color-text-light);
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .close-whatsapp:hover {
            color: var(--color-primary);
        }
        
        .whatsapp-body {
            padding: 20px;
        }
        
        .whatsapp-status {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #25D366;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ccc;
        }
        
        .status-dot.connected {
            background: #25D366;
        }
        
        .status-dot.disconnected {
            background: #dc3545;
        }
        
        .status-dot.qr-ready {
            background: #ffc107;
        }
        
        .status-text {
            font-weight: 500;
            color: var(--color-text-dark);
        }
        
        .qr-container {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-placeholder {
            color: #999;
        }
        
        .qr-placeholder i {
            font-size: 3rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .qr-code-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .whatsapp-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: #25D366;
            color: white;
        }
        
        .btn-primary:hover {
            background: #128C7E;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-outline {
            background: transparent;
            color: #25D366;
            border: 1px solid #25D366;
        }
        
        .btn-outline:hover {
            background: #25D366;
            color: white;
        }
        
        .whatsapp-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #2196f3;
        }
        
        .whatsapp-info p {
            margin: 0 0 10px 0;
            font-weight: 500;
        }
        
        .whatsapp-info ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .whatsapp-info li {
            margin-bottom: 5px;
            color: var(--color-text-dark);
        }
        
        .hidden {
            display: none !important;
        }
    </style>

    <!-- Add this script after the tutorial button -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tutorialButton = document.getElementById('tutorial-button');
        if (tutorialButton) {
            tutorialButton.addEventListener('click', function() {
                // If tutorial engine is not ready, wait for it
                if (!window.tutorialEngine) {
                    document.addEventListener('tutorialEngineReady', function() {
                        document.dispatchEvent(new CustomEvent('startTutorial'));
                    }, { once: true });
                } else {
                    document.dispatchEvent(new CustomEvent('startTutorial'));
                }
            });
        }
    });
    </script>