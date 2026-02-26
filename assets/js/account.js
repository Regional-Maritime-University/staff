document.addEventListener("DOMContentLoaded", () => {
    // Tab functionality
    const accountTabs = document.querySelectorAll(".account-tab");
    const accountTabContents = document.querySelectorAll(".account-tab-content");
    
    accountTabs.forEach(tab => {
        tab.addEventListener("click", function() {
            // Remove active class from all tabs
            accountTabs.forEach(t => t.classList.remove("active"));
            
            // Add active class to clicked tab
            this.classList.add("active");
            
            // Hide all tab contents
            accountTabContents.forEach(content => content.classList.remove("active"));
            
            // Show the corresponding tab content
            const tabId = this.getAttribute("data-tab");
            document.getElementById(tabId).classList.add("active");
        });
    });
    
    // Profile form submission
    const saveProfileBtn = document.getElementById("saveProfileBtn");
    
    saveProfileBtn.addEventListener("click", function() {
        // In a real application, you would validate the form and send an API request
        console.log("Saving profile changes");
        
        // Simulate successful save
        setTimeout(() => {
            alert("Profile updated successfully!");
        }, 1000);
    });
    
    // Password strength meter
    const newPassword = document.getElementById("newPassword");
    const confirmPassword = document.getElementById("confirmPassword");
    const strengthBar = document.querySelector(".strength-bar");
    const strengthText = document.querySelector(".strength-text");
    
    // Password requirement checks
    const requirements = {
        length:   { regex: /.{8,}/,        el: null },
        upper:    { regex: /[A-Z]/,        el: null },
        lower:    { regex: /[a-z]/,        el: null },
        number:   { regex: /[0-9]/,        el: null },
        special:  { regex: /[^A-Za-z0-9]/, el: null }
    };

    // Map requirement list items by their text content
    const reqItems = document.querySelectorAll(".password-requirements li");
    reqItems.forEach(function(li) {
        var text = li.textContent.toLowerCase();
        if (text.includes("8 characters")) requirements.length.el = li;
        else if (text.includes("uppercase")) requirements.upper.el = li;
        else if (text.includes("lowercase")) requirements.lower.el = li;
        else if (text.includes("number")) requirements.number.el = li;
        else if (text.includes("special")) requirements.special.el = li;
    });

    newPassword.addEventListener("input", function() {
        const password = this.value;
        let strength = 0;
        let status = "";

        if (password.length > 0) {
            // Check each requirement and update UI
            Object.keys(requirements).forEach(function(key) {
                var req = requirements[key];
                var passed = req.regex.test(password);
                if (passed) strength += 20;
                if (req.el) {
                    req.el.classList.toggle("met", passed);
                }
            });

            // Set status based on strength
            if (strength <= 20) {
                status = "Very Weak";
                strengthBar.style.backgroundColor = "#e74c3c";
            } else if (strength <= 40) {
                status = "Weak";
                strengthBar.style.backgroundColor = "#e67e22";
            } else if (strength <= 60) {
                status = "Medium";
                strengthBar.style.backgroundColor = "#f39c12";
            } else if (strength <= 80) {
                status = "Strong";
                strengthBar.style.backgroundColor = "#27ae60";
            } else {
                status = "Very Strong";
                strengthBar.style.backgroundColor = "#2ecc71";
            }
        } else {
            status = "Not set";
            strength = 0;
            // Reset all requirement indicators
            Object.keys(requirements).forEach(function(key) {
                if (requirements[key].el) requirements[key].el.classList.remove("met");
            });
        }

        // Update UI
        strengthBar.style.width = `${strength}%`;
        strengthText.textContent = `Password strength: ${status}`;
        strengthBar.setAttribute("aria-valuenow", strength);
    });
    
    // Password confirmation validation
    confirmPassword.addEventListener("input", function() {
        if (this.value !== newPassword.value) {
            this.setCustomValidity("Passwords do not match");
        } else {
            this.setCustomValidity("");
        }
    });
    
    // Change password functionality
    const changePasswordBtn = document.getElementById("changePasswordBtn");
    
    changePasswordBtn.addEventListener("click", function() {
        const currentPassword = document.getElementById("currentPassword").value;
        const newPasswordValue = newPassword.value;
        const confirmPasswordValue = confirmPassword.value;
        
        if (!currentPassword || !newPasswordValue || !confirmPasswordValue) {
            alert("Please fill in all password fields");
            return;
        }
        
        if (newPasswordValue !== confirmPasswordValue) {
            alert("New passwords do not match");
            return;
        }
        
        // In a real application, you would send an API request to change the password
        console.log("Changing password");
        
        // Simulate successful password change
        setTimeout(() => {
            alert("Password changed successfully!");
            document.getElementById("currentPassword").value = "";
            newPassword.value = "";
            confirmPassword.value = "";
            strengthBar.style.width = "0%";
            strengthText.textContent = "Password strength: Not set";
        }, 1000);
    });
    
    // Two-factor authentication toggle
    const twoFactorToggle = document.getElementById("twoFactorToggle");
    const twoFactorSetup = document.querySelector(".two-factor-setup");
    
    twoFactorToggle.addEventListener("change", function() {
        if (this.checked) {
            twoFactorSetup.style.display = "block";
        } else {
            twoFactorSetup.style.display = "none";
        }
    });
    
    // Two-factor authentication setup
    const setupTwoFactorBtn = document.getElementById("setupTwoFactorBtn");
    const twoFactorModal = document.getElementById("twoFactorModal");
    const closeTwoFactorModal = document.getElementById("closeTwoFactorModal");
    const cancelTwoFactor = document.getElementById("cancelTwoFactor");
    const verifyTwoFactor = document.getElementById("verifyTwoFactor");
    
    setupTwoFactorBtn.addEventListener("click", function() {
        openModal("twoFactorModal");
    });
    
    closeTwoFactorModal.addEventListener("click", function() {
        closeModal("twoFactorModal");
    });
    
    cancelTwoFactor.addEventListener("click", function() {
        closeModal("twoFactorModal");
        twoFactorToggle.checked = false;
        twoFactorSetup.style.display = "none";
    });
    
    verifyTwoFactor.addEventListener("click", function() {
        const verificationCode = document.querySelector(".verification-code-input input").value;
        
        if (!verificationCode || verificationCode.length !== 6) {
            alert("Please enter a valid 6-digit verification code");
            return;
        }
        
        // In a real application, you would send an API request to verify the code
        console.log("Verifying two-factor authentication code:", verificationCode);
        
        // Simulate successful verification
        setTimeout(() => {
            alert("Two-factor authentication enabled successfully!");
            closeModal("twoFactorModal");
            twoFactorSetup.style.display = "none";
        }, 1000);
    });
    
    // End session functionality
    const endSessionBtns = document.querySelectorAll(".end-session-btn");
    
    endSessionBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            const sessionItem = this.closest(".session-item");
            
            // In a real application, you would send an API request to end the session
            console.log("Ending session");
            
            // Simulate successful session end
            setTimeout(() => {
                sessionItem.remove();
                alert("Session ended successfully!");
            }, 1000);
        });
    });
    
    // End all sessions functionality
    const endAllSessionsBtn = document.getElementById("endAllSessionsBtn");
    
    endAllSessionsBtn.addEventListener("click", function() {
        if (confirm("Are you sure you want to end all other sessions? You will remain logged in on this device.")) {
            // In a real application, you would send an API request to end all other sessions
            console.log("Ending all other sessions");
            
            // Simulate successful operation
            setTimeout(() => {
                const otherSessions = document.querySelectorAll(".session-item:not(.current)");
                otherSessions.forEach(session => session.remove());
                alert("All other sessions ended successfully!");
            }, 1000);
        }
    });
    
    // Theme selection
    const themeOptions = document.querySelectorAll(".theme-option");
    
    themeOptions.forEach(option => {
        option.addEventListener("click", function() {
            // Remove active class from all options
            themeOptions.forEach(o => o.classList.remove("active"));
            
            // Add active class to clicked option
            this.classList.add("active");
            
            // In a real application, you would apply the selected theme
            const theme = this.querySelector("span").textContent.toLowerCase();
            console.log("Selected theme:", theme);
        });
    });
    
    // Save preferences functionality
    const savePreferencesBtn = document.getElementById("savePreferencesBtn");
    
    savePreferencesBtn.addEventListener("click", function() {
        // In a real application, you would collect and save all preferences
        const fontSize = document.getElementById("fontSize").value;
        const language = document.getElementById("language").value;
        const timeZone = document.getElementById("timeZone").value;
        const dateFormat = document.getElementById("dateFormat").value;
        
        console.log("Saving preferences:", {
            fontSize,
            language,
            timeZone,
            dateFormat
        });
        
        // Simulate successful save
        setTimeout(() => {
            alert("Preferences saved successfully!");
        }, 1000);
    });
    
    // Activity log filtering
    const activityType = document.getElementById("activityType");
    const activityDate = document.getElementById("activityDate");
    
    [activityType, activityDate].forEach(filter => {
        filter.addEventListener("change", function() {
            // In a real application, you would filter the activity log based on the selected filters
            console.log("Filtering activity log:", {
                type: activityType.value,
                date: activityDate.value
            });
        });
    });
    
    // Load more activities
    const loadMoreBtn = document.querySelector(".load-more-btn");
    
    loadMoreBtn.addEventListener("click", function() {
        // In a real application, you would load more activities from the server
        console.log("Loading more activities");
        
        // Simulate loading more activities
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-sync"></i> Load More';
            alert("No more activities to load");
        }, 1500);
    });
    
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
