<!-- Header -->
<header class="header" role="banner">
    <div class="header-left">
        <button class="toggle-sidebar" id="toggleSidebar" aria-label="Toggle sidebar navigation" aria-expanded="true">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
        <h1><?= $pageTitle ?></h1>
    </div>
    <div class="header-right">
        <div class="search-bar" role="search">
            <label for="globalSearch" class="sr-only">Search</label>
            <input type="text" placeholder="Search..." id="globalSearch" aria-label="Search the portal">
            <button class="search-btn" aria-label="Submit search">
                <i class="fas fa-search" aria-hidden="true"></i>
            </button>
        </div>
        <div class="header-actions">
            <a href="notifications.php" class="action-btn notifications" aria-label="Notifications - 5 unread">
                <i class="fas fa-bell" aria-hidden="true"></i>
                <span class="badge" aria-hidden="true">5</span>
            </a>
            <a href="messages.php" class="action-btn messages" aria-label="Messages - 3 unread">
                <i class="fas fa-envelope" aria-hidden="true"></i>
                <span class="badge" aria-hidden="true">3</span>
            </a>
        </div>
    </div>
</header>

<?php if (isset($activePage) && isset($pageTitle) && $activePage !== 'dashboard'): ?>
<!-- Breadcrumb Navigation -->
<nav class="breadcrumb" aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $pageTitle ?></li>
    </ol>
</nav>
<?php endif; ?>
