<!-- Header -->
<div class="header">
    <div class="header-left">
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h1><?= $pageTitle ?></h1>
    </div>
    <div class="header-right">
        <div class="search-bar">
            <input type="text" placeholder="Search..." id="globalSearch">
            <button class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div class="header-actions">
            <a href="notifications.php" class="action-btn notifications">
                <i class="fas fa-bell"></i>
                <span class="badge">5</span>
            </a>
            <a href="messages.php" class="action-btn messages">
                <i class="fas fa-envelope"></i>
                <span class="badge">3</span>
            </a>
        </div>
    </div>
</div>