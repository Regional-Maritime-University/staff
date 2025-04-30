document.addEventListener("DOMContentLoaded", () => {
    // Tab functionality
    const tabBtns = document.querySelectorAll(".notifications-tabs .tab-btn");
    const notificationItems = document.querySelectorAll(".notification-item");
    
    tabBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            // Remove active class from all tabs
            tabBtns.forEach(b => b.classList.remove("active"));
            
            // Add active class to clicked tab
            this.classList.add("active");
            
            const tabId = this.getAttribute("data-tab");
            
            // Show/hide notifications based on tab
            notificationItems.forEach(item => {
                if (tabId === "all") {
                    item.style.display = "flex";
                } else if (tabId === "unread" && item.classList.contains("unread")) {
                    item.style.display = "flex";
                } else if (tabId === "important" && item.classList.contains("important")) {
                    item.style.display = "flex";
                } else {
                    item.style.display = "none";
                }
            });
        });
    });
    
    // Mark as read functionality
    const markReadBtns = document.querySelectorAll(".mark-read-btn");
    
    markReadBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            const notificationId = this.getAttribute("data-id");
            const notificationItem = this.closest(".notification-item");
            
            // Remove unread class
            notificationItem.classList.remove("unread");
            
            // Hide the mark as read button
            this.style.visibility = "hidden";
            
            // In a real application, you would send an API request to mark the notification as read
            console.log(`Marking notification ${notificationId} as read`);
            
            // Update notification count
            updateNotificationCount();
        });
    });
    
    // Mark all as read functionality
    const markAllReadBtn = document.getElementById("markAllReadBtn");
    
    markAllReadBtn.addEventListener("click", function() {
        const unreadNotifications = document.querySelectorAll(".notification-item.unread");
        
        unreadNotifications.forEach(notification => {
            // Remove unread class
            notification.classList.remove("unread");
            
            // Hide the mark as read button
            const markReadBtn = notification.querySelector(".mark-read-btn");
            if (markReadBtn) {
                markReadBtn.style.visibility = "hidden";
            }
        });
        
        // In a real application, you would send an API request to mark all notifications as read
        console.log("Marking all notifications as read");
        
        // Update notification count
        updateNotificationCount();
    });
    
    // Notification settings
    const notificationSettingsBtn = document.getElementById("notificationSettingsBtn");
    const notificationSettingsModal = document.getElementById("notificationSettingsModal");
    const closeNotificationSettingsModal = document.getElementById("closeNotificationSettingsModal");
    const cancelNotificationSettings = document.getElementById("cancelNotificationSettings");
    const saveNotificationSettings = document.getElementById("saveNotificationSettings");
    
    notificationSettingsBtn.addEventListener("click", function() {
        openModal("notificationSettingsModal");
    });
    
    closeNotificationSettingsModal.addEventListener("click", function() {
        closeModal("notificationSettingsModal");
    });
    
    cancelNotificationSettings.addEventListener("click", function() {
        closeModal("notificationSettingsModal");
    });
    
    saveNotificationSettings.addEventListener("click", function() {
        // In a real application, you would save the notification settings
        console.log("Saving notification settings");
        
        // Show success message
        alert("Notification settings saved successfully!");
        
        // Close modal
        closeModal("notificationSettingsModal");
    });
    
    // Helper function to update notification count
    function updateNotificationCount() {
        const unreadCount = document.querySelectorAll(".notification-item.unread").length;
        const badges = document.querySelectorAll(".notifications .badge");
        
        badges.forEach(badge => {
            badge.textContent = unreadCount;
            
            // Hide badge if no unread notifications
            if (unreadCount === 0) {
                badge.style.display = "none";
            } else {
                badge.style.display = "flex";
            }
        });
    }
    
    // Modal Functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove("active");
        document.body.style.overflow = "auto";
    }
});
