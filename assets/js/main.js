// ── Dark mode: apply saved preference before DOM renders (prevent flash) ──
(function() {
    var saved = localStorage.getItem("rmu-theme");
    if (saved === "dark") {
        document.documentElement.setAttribute("data-theme", "dark");
    }
})();

document.addEventListener("DOMContentLoaded", function() {
    // Sidebar Toggle
    var sidebar = document.getElementById("sidebar");
    var toggleSidebarBtn = document.getElementById("toggleSidebar");
    var mainContent = document.querySelector(".main-content");

    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener("click", function() {
            sidebar.classList.toggle("collapsed");
            var isCollapsed = sidebar.classList.contains("collapsed");
            toggleSidebarBtn.setAttribute("aria-expanded", !isCollapsed);

            // For mobile
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle("active");
            }
        });
    }

    // Close sidebar when clicking outside on mobile
    if (mainContent) {
        mainContent.addEventListener("click", function() {
            if (window.innerWidth <= 768 && sidebar.classList.contains("active")) {
                sidebar.classList.remove("active");
            }
        });
    }

    // ── Dark mode toggle ──
    var darkModeToggle = document.getElementById("darkModeToggle");
    if (darkModeToggle) {
        var isDark = document.documentElement.getAttribute("data-theme") === "dark";
        updateDarkModeIcon(isDark);

        darkModeToggle.addEventListener("click", function() {
            var currentlyDark = document.documentElement.getAttribute("data-theme") === "dark";
            if (currentlyDark) {
                document.documentElement.removeAttribute("data-theme");
                localStorage.setItem("rmu-theme", "light");
                updateDarkModeIcon(false);
            } else {
                document.documentElement.setAttribute("data-theme", "dark");
                localStorage.setItem("rmu-theme", "dark");
                updateDarkModeIcon(true);
            }
        });
    }

    function updateDarkModeIcon(isDark) {
        if (!darkModeToggle) return;
        var icon = darkModeToggle.querySelector("i");
        if (icon) {
            icon.className = isDark ? "fas fa-sun" : "fas fa-moon";
        }
        darkModeToggle.setAttribute("aria-label", isDark ? "Switch to light mode" : "Switch to dark mode");
    }

    // ── Accessibility: auto-enhance all modals with ARIA attributes ──
    document.querySelectorAll(".modal").forEach(function(modal) {
        if (!modal.getAttribute("role")) {
            modal.setAttribute("role", "dialog");
        }
        if (!modal.getAttribute("aria-modal")) {
            modal.setAttribute("aria-modal", "true");
        }
        if (!modal.getAttribute("aria-labelledby")) {
            var heading = modal.querySelector(".modal-header h2");
            if (heading) {
                var titleId = modal.id + "Title";
                heading.id = titleId;
                modal.setAttribute("aria-labelledby", titleId);
            }
        }
        modal.querySelectorAll(".close-btn").forEach(function(btn) {
            if (!btn.getAttribute("aria-label")) {
                btn.setAttribute("aria-label", "Close dialog");
            }
        });
    });

    // ── Accessibility: mark decorative icons as aria-hidden ──
    document.querySelectorAll("i.fas, i.far, i.fab, i.fa").forEach(function(icon) {
        if (!icon.getAttribute("aria-hidden") && !icon.getAttribute("aria-label")) {
            icon.setAttribute("aria-hidden", "true");
        }
    });

    // ── Global search: filter visible cards/table rows by text ──
    var globalSearch = document.getElementById("globalSearch");
    if (globalSearch) {
        globalSearch.addEventListener("input", function() {
            var query = this.value.toLowerCase().trim();
            var cards = document.querySelectorAll(".student-card, .lecturer-card, .course-card, .result-card, .program-card");
            cards.forEach(function(card) {
                var text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? "" : "none";
            });
            document.querySelectorAll(".results-table tbody tr").forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? "" : "none";
            });
        });
    }

    // ── Loading overlay for AJAX filter buttons ──
    document.querySelectorAll(".filter-btn.apply").forEach(function(btn) {
        btn.addEventListener("click", function() {
            var container = this.closest(".dashboard-content") || this.closest(".main-content");
            if (container) {
                showTableLoading(container);
                setTimeout(function() { hideTableLoading(container); }, 2000);
            }
        });
    });

    // ── Inline form validation ──
    initFormValidation();

    // ── Confirmation dialogs for destructive actions ──
    initDestructiveConfirmations();

    // ── Character counts for textareas and inputs ──
    initCharacterCounts();
});

// ── Table loading spinner helpers ──
function showTableLoading(container) {
    if (container.querySelector(".table-loading-overlay")) return;
    var overlay = document.createElement("div");
    overlay.className = "table-loading-overlay";
    overlay.setAttribute("role", "status");
    overlay.setAttribute("aria-live", "polite");
    overlay.innerHTML = '<div class="table-spinner"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i><span>Loading data...</span></div>';
    container.style.position = "relative";
    container.appendChild(overlay);
}

function hideTableLoading(container) {
    var overlay = container.querySelector(".table-loading-overlay");
    if (overlay) overlay.remove();
}

// ── Inline form validation ──
function initFormValidation() {
    // Validate required inputs on blur
    document.querySelectorAll("input[required], select[required], textarea[required]").forEach(function(field) {
        field.addEventListener("blur", function() {
            validateField(this);
        });
        field.addEventListener("input", function() {
            if (this.classList.contains("field-error")) {
                validateField(this);
            }
        });
    });

    // Email validation
    document.querySelectorAll('input[type="email"]').forEach(function(field) {
        field.addEventListener("blur", function() {
            if (this.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                showFieldError(this, "Please enter a valid email address");
            } else {
                clearFieldError(this);
            }
        });
    });

    // Phone validation
    document.querySelectorAll('input[type="tel"]').forEach(function(field) {
        field.addEventListener("blur", function() {
            if (this.value && !/^[\d\s\-+()]{7,}$/.test(this.value)) {
                showFieldError(this, "Please enter a valid phone number");
            } else {
                clearFieldError(this);
            }
        });
    });
}

function validateField(field) {
    if (field.hasAttribute("required") && !field.value.trim()) {
        showFieldError(field, "This field is required");
        return false;
    }
    clearFieldError(field);
    return true;
}

function showFieldError(field, message) {
    field.classList.add("field-error");
    field.classList.remove("field-valid");
    var existing = field.parentElement.querySelector(".field-error-text");
    if (existing) existing.textContent = message;
    else {
        var errorEl = document.createElement("span");
        errorEl.className = "field-error-text";
        errorEl.textContent = message;
        errorEl.setAttribute("role", "alert");
        field.parentElement.appendChild(errorEl);
    }
}

function clearFieldError(field) {
    field.classList.remove("field-error");
    if (field.value.trim()) field.classList.add("field-valid");
    var existing = field.parentElement.querySelector(".field-error-text");
    if (existing) existing.remove();
}

// ── Confirmation dialogs for destructive actions ──
function initDestructiveConfirmations() {
    // Archive buttons
    document.querySelectorAll(".archive-student, .archive-lecturer, [id*='archive'], [class*='danger'][class*='Btn']").forEach(function(btn) {
        if (btn._confirmBound) return;
        btn._confirmBound = true;
        btn.addEventListener("click", function(e) {
            if (!confirm("Are you sure you want to perform this action? This may be difficult to undo.")) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    });

    // Decline/reject buttons
    document.querySelectorAll(".declineResultsBtn, [class*='decline'], [class*='reject']").forEach(function(btn) {
        if (btn._confirmBound) return;
        btn._confirmBound = true;
        btn.addEventListener("click", function(e) {
            if (!confirm("Are you sure you want to decline this submission?")) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    });
}

// ── Character counts ──
function initCharacterCounts() {
    document.querySelectorAll("textarea[maxlength], input[maxlength]").forEach(function(field) {
        var max = parseInt(field.getAttribute("maxlength"));
        if (isNaN(max) || max <= 0) return;

        var counter = document.createElement("span");
        counter.className = "char-counter";
        counter.textContent = "0 / " + max;
        field.parentElement.appendChild(counter);

        field.addEventListener("input", function() {
            counter.textContent = this.value.length + " / " + max;
            counter.classList.toggle("char-counter-warn", this.value.length > max * 0.9);
        });
    });
}
