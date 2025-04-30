document.addEventListener("DOMContentLoaded", function() {
    // Sidebar Toggle
    const sidebar = document.getElementById("sidebar")
    const toggleSidebarBtn = document.getElementById("toggleSidebar")
    const mainContent = document.querySelector(".main-content")
  
    toggleSidebarBtn.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed")

        // For mobile
        if (window.innerWidth <= 768) {
        sidebar.classList.toggle("active")
        }
    })
  
    // Close sidebar when clicking outside on mobile
    mainContent.addEventListener("click", () => {
        if (window.innerWidth <= 768 && sidebar.classList.contains("active")) {
        sidebar.classList.remove("active")
        }
    })
})
  