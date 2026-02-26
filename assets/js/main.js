document.addEventListener("DOMContentLoaded", function() {
    // Sidebar Toggle
    const sidebar = document.getElementById("sidebar");
    const toggleSidebarBtn = document.getElementById("toggleSidebar");
    const mainContent = document.querySelector(".main-content");

    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener("click", () => {
            sidebar.classList.toggle("collapsed");
            const isCollapsed = sidebar.classList.contains("collapsed");
            toggleSidebarBtn.setAttribute("aria-expanded", !isCollapsed);

            // For mobile
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle("active");
            }
        });
    }

    // Close sidebar when clicking outside on mobile
    if (mainContent) {
        mainContent.addEventListener("click", () => {
            if (window.innerWidth <= 768 && sidebar.classList.contains("active")) {
                sidebar.classList.remove("active");
            }
        });
    }

    // ── Accessibility: auto-enhance all modals with ARIA attributes ──
    document.querySelectorAll(".modal").forEach(function(modal) {
        if (!modal.getAttribute("role")) {
            modal.setAttribute("role", "dialog");
        }
        if (!modal.getAttribute("aria-modal")) {
            modal.setAttribute("aria-modal", "true");
        }
        // Set aria-labelledby from modal header h2
        if (!modal.getAttribute("aria-labelledby")) {
            var heading = modal.querySelector(".modal-header h2");
            if (heading) {
                var titleId = modal.id + "Title";
                heading.id = titleId;
                modal.setAttribute("aria-labelledby", titleId);
            }
        }
        // Ensure close buttons have aria-label
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
            // Filter card-based layouts
            var cards = document.querySelectorAll(".student-card, .lecturer-card, .course-card, .result-card, .program-card");
            cards.forEach(function(card) {
                var text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? "" : "none";
            });
            // Filter table rows
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
                // Auto-hide after a timeout (actual AJAX should call hideTableLoading)
                setTimeout(function() { hideTableLoading(container); }, 2000);
            }
        });
    });
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
