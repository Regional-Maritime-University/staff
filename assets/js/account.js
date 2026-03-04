document.addEventListener("DOMContentLoaded", function() {
    // Tab functionality
    var accountTabs = document.querySelectorAll(".account-tab");
    var accountTabContents = document.querySelectorAll(".account-tab-content");

    accountTabs.forEach(function(tab) {
        tab.addEventListener("click", function() {
            accountTabs.forEach(function(t) { t.classList.remove("active"); });
            this.classList.add("active");
            accountTabContents.forEach(function(content) { content.classList.remove("active"); });
            var tabId = this.getAttribute("data-tab");
            document.getElementById(tabId).classList.add("active");
        });
    });

    // Profile form submission
    var saveProfileBtn = document.getElementById("saveProfileBtn");
    if (saveProfileBtn) {
        saveProfileBtn.addEventListener("click", function() {
            var firstName = document.getElementById("firstName");
            var lastName = document.getElementById("lastName");
            var email = document.getElementById("email");
            var isValid = true;

            // Validate required fields
            [firstName, lastName, email].forEach(function(field) {
                if (field && !field.value.trim()) {
                    field.classList.add("field-error");
                    isValid = false;
                } else if (field) {
                    field.classList.remove("field-error");
                }
            });

            // Validate email format
            if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                email.classList.add("field-error");
                showToast("Please enter a valid email address.", "error");
                return;
            }

            if (!isValid) {
                showToast("Please fill in all required fields.", "error");
                return;
            }

            // Show saving state
            var originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            this.disabled = true;

            var btn = this;
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast("Profile updated successfully!", "success");
            }, 1000);
        });
    }

    // Cancel button
    var cancelProfileBtn = document.getElementById("cancelProfileBtn");
    if (cancelProfileBtn) {
        cancelProfileBtn.addEventListener("click", function() {
            showToast("Changes discarded.", "info");
        });
    }

    // Password strength meter
    var newPassword = document.getElementById("newPassword");
    var confirmPassword = document.getElementById("confirmPassword");
    var strengthBar = document.querySelector(".strength-bar");
    var strengthText = document.querySelector(".strength-text");

    var requirements = {
        length:  { regex: /.{8,}/,        el: null },
        upper:   { regex: /[A-Z]/,        el: null },
        lower:   { regex: /[a-z]/,        el: null },
        number:  { regex: /[0-9]/,        el: null },
        special: { regex: /[^A-Za-z0-9]/, el: null }
    };

    var reqItems = document.querySelectorAll(".password-requirements li");
    reqItems.forEach(function(li) {
        var text = li.textContent.toLowerCase();
        if (text.includes("8 characters")) requirements.length.el = li;
        else if (text.includes("uppercase")) requirements.upper.el = li;
        else if (text.includes("lowercase")) requirements.lower.el = li;
        else if (text.includes("number")) requirements.number.el = li;
        else if (text.includes("special")) requirements.special.el = li;
    });

    if (newPassword) {
        newPassword.addEventListener("input", function() {
            var password = this.value;
            var strength = 0;
            var status = "";

            if (password.length > 0) {
                Object.keys(requirements).forEach(function(key) {
                    var req = requirements[key];
                    var passed = req.regex.test(password);
                    if (passed) strength += 20;
                    if (req.el) {
                        req.el.classList.toggle("met", passed);
                    }
                });

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
                Object.keys(requirements).forEach(function(key) {
                    if (requirements[key].el) requirements[key].el.classList.remove("met");
                });
            }

            strengthBar.style.width = strength + "%";
            strengthText.textContent = "Password strength: " + status;
            strengthBar.setAttribute("aria-valuenow", strength);
        });
    }

    // Password confirmation validation
    if (confirmPassword) {
        confirmPassword.addEventListener("input", function() {
            if (this.value && this.value !== newPassword.value) {
                this.classList.add("field-error");
                this.classList.remove("field-valid");
            } else if (this.value) {
                this.classList.remove("field-error");
                this.classList.add("field-valid");
            }
        });
    }

    // Change password functionality
    var changePasswordBtn = document.getElementById("changePasswordBtn");
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener("click", function() {
            var currentPassword = document.getElementById("currentPassword");
            var newPasswordValue = newPassword ? newPassword.value : "";
            var confirmPasswordValue = confirmPassword ? confirmPassword.value : "";

            if (!currentPassword || !currentPassword.value || !newPasswordValue || !confirmPasswordValue) {
                showToast("Please fill in all password fields.", "error");
                return;
            }

            if (newPasswordValue !== confirmPasswordValue) {
                showToast("New passwords do not match.", "error");
                confirmPassword.classList.add("field-error");
                return;
            }

            // Check minimum strength
            var strength = 0;
            Object.keys(requirements).forEach(function(key) {
                if (requirements[key].regex.test(newPasswordValue)) strength += 20;
            });
            if (strength < 60) {
                showToast("Password is too weak. Please choose a stronger password.", "warning");
                return;
            }

            var originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
            this.disabled = true;

            var btn = this;
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast("Password changed successfully!", "success");
                currentPassword.value = "";
                newPassword.value = "";
                confirmPassword.value = "";
                strengthBar.style.width = "0%";
                strengthText.textContent = "Password strength: Not set";
                Object.keys(requirements).forEach(function(key) {
                    if (requirements[key].el) requirements[key].el.classList.remove("met");
                });
                confirmPassword.classList.remove("field-error", "field-valid");
            }, 1000);
        });
    }

    // Two-factor authentication toggle
    var twoFactorToggle = document.getElementById("twoFactorToggle");
    var twoFactorSetup = document.querySelector(".two-factor-setup");

    if (twoFactorToggle) {
        twoFactorToggle.addEventListener("change", function() {
            if (twoFactorSetup) {
                twoFactorSetup.style.display = this.checked ? "block" : "none";
            }
        });
    }

    // Two-factor authentication setup
    var setupTwoFactorBtn = document.getElementById("setupTwoFactorBtn");
    var closeTwoFactorModal = document.getElementById("closeTwoFactorModal");
    var cancelTwoFactor = document.getElementById("cancelTwoFactor");
    var verifyTwoFactor = document.getElementById("verifyTwoFactor");

    if (setupTwoFactorBtn) {
        setupTwoFactorBtn.addEventListener("click", function() {
            openModal("twoFactorModal");
        });
    }

    if (closeTwoFactorModal) {
        closeTwoFactorModal.addEventListener("click", function() {
            closeModal("twoFactorModal");
        });
    }

    if (cancelTwoFactor) {
        cancelTwoFactor.addEventListener("click", function() {
            closeModal("twoFactorModal");
            if (twoFactorToggle) twoFactorToggle.checked = false;
            if (twoFactorSetup) twoFactorSetup.style.display = "none";
        });
    }

    if (verifyTwoFactor) {
        verifyTwoFactor.addEventListener("click", function() {
            var codeInput = document.querySelector(".verification-code-input input");
            var verificationCode = codeInput ? codeInput.value : "";

            if (!verificationCode || verificationCode.length !== 6) {
                showToast("Please enter a valid 6-digit verification code.", "error");
                if (codeInput) codeInput.classList.add("field-error");
                return;
            }

            var originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
            this.disabled = true;

            var btn = this;
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast("Two-factor authentication enabled successfully!", "success");
                closeModal("twoFactorModal");
                if (twoFactorSetup) twoFactorSetup.style.display = "none";
            }, 1000);
        });
    }

    // End session functionality
    document.querySelectorAll(".end-session-btn").forEach(function(btn) {
        btn.addEventListener("click", function() {
            var sessionItem = this.closest(".session-item");
            if (!confirm("End this session?")) return;

            sessionItem.style.opacity = "0.5";
            setTimeout(function() {
                sessionItem.remove();
                showToast("Session ended successfully.", "success");
            }, 500);
        });
    });

    // End all sessions functionality
    var endAllSessionsBtn = document.getElementById("endAllSessionsBtn");
    if (endAllSessionsBtn) {
        endAllSessionsBtn.addEventListener("click", function() {
            if (!confirm("Are you sure you want to end all other sessions? You will remain logged in on this device.")) return;

            var otherSessions = document.querySelectorAll(".session-item:not(.current)");
            otherSessions.forEach(function(session) {
                session.style.opacity = "0.5";
            });

            setTimeout(function() {
                otherSessions.forEach(function(session) { session.remove(); });
                showToast("All other sessions ended successfully.", "success");
            }, 500);
        });
    }

    // Theme selection
    var themeOptions = document.querySelectorAll(".theme-option");
    themeOptions.forEach(function(option) {
        option.addEventListener("click", function() {
            themeOptions.forEach(function(o) { o.classList.remove("active"); });
            this.classList.add("active");

            var theme = this.querySelector("span").textContent.toLowerCase().trim();
            if (theme === "dark") {
                document.documentElement.setAttribute("data-theme", "dark");
                localStorage.setItem("rmu-theme", "dark");
            } else if (theme === "light") {
                document.documentElement.removeAttribute("data-theme");
                localStorage.setItem("rmu-theme", "light");
            } else {
                // System preference
                var prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
                if (prefersDark) {
                    document.documentElement.setAttribute("data-theme", "dark");
                } else {
                    document.documentElement.removeAttribute("data-theme");
                }
                localStorage.setItem("rmu-theme", "system");
            }

            // Update dark mode toggle icon if present
            var darkModeToggle = document.getElementById("darkModeToggle");
            if (darkModeToggle) {
                var isDark = document.documentElement.getAttribute("data-theme") === "dark";
                var icon = darkModeToggle.querySelector("i");
                if (icon) icon.className = isDark ? "fas fa-sun" : "fas fa-moon";
            }
        });
    });

    // Sync theme option selection with current theme on load
    var currentTheme = localStorage.getItem("rmu-theme") || "light";
    themeOptions.forEach(function(option) {
        var optionTheme = option.querySelector("span").textContent.toLowerCase().trim();
        if (optionTheme === currentTheme) {
            themeOptions.forEach(function(o) { o.classList.remove("active"); });
            option.classList.add("active");
        }
    });

    // Save preferences functionality
    var savePreferencesBtn = document.getElementById("savePreferencesBtn");
    if (savePreferencesBtn) {
        savePreferencesBtn.addEventListener("click", function() {
            var originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            this.disabled = true;

            var btn = this;
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast("Preferences saved successfully!", "success");
            }, 800);
        });
    }

    // Activity log filtering
    var activityType = document.getElementById("activityType");
    var activityDate = document.getElementById("activityDate");
    if (activityType && activityDate) {
        [activityType, activityDate].forEach(function(filter) {
            filter.addEventListener("change", function() {
                showToast("Filters applied.", "info");
            });
        });
    }

    // Load more activities
    var loadMoreBtn = document.querySelector(".load-more-btn");
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener("click", function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            var btn = this;
            setTimeout(function() {
                btn.innerHTML = '<i class="fas fa-sync"></i> Load More';
                showToast("No more activities to load.", "info");
            }, 1000);
        });
    }

    // Modal Functions
    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        }
    }

    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove("active");
            document.body.style.overflow = "auto";
        }
    }
});

// Toast notification helper (global)
function showToast(message, type) {
    var container = document.getElementById("toastContainer");
    if (!container) return;

    var toast = document.createElement("div");
    toast.className = "toast toast-" + (type || "info");

    var iconMap = {
        success: "fa-check-circle",
        error: "fa-exclamation-circle",
        warning: "fa-exclamation-triangle",
        info: "fa-info-circle"
    };

    var icon = iconMap[type] || iconMap.info;
    toast.innerHTML = '<i class="fas ' + icon + '" style="margin-right: 8px;"></i>' + message;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(function() { toast.classList.add("toast-show"); }, 10);

    // Auto-dismiss
    setTimeout(function() {
        toast.classList.remove("toast-show");
        setTimeout(function() { toast.remove(); }, 300);
    }, 3500);
}
