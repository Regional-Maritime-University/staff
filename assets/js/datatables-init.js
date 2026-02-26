/**
 * DataTables initialization utility
 * Automatically initializes DataTables on tables with the class 'results-table',
 * 'students-table', 'course-table', or 'grades-table'.
 * Requires jQuery and DataTables.js to be loaded first.
 */
document.addEventListener("DOMContentLoaded", function() {
    if (typeof $ === "undefined" || typeof $.fn.DataTable === "undefined") return;

    var tableSelectors = [
        "table.results-table",
        "table.students-table",
        "table.course-table",
        "table.grades-table"
    ];

    tableSelectors.forEach(function(selector) {
        $(selector).each(function() {
            // Skip if already initialized
            if ($.fn.DataTable.isDataTable(this)) return;

            // Skip tables inside hidden modals (they'll be initialized when the modal opens)
            var modal = $(this).closest(".modal");
            if (modal.length && !modal.hasClass("active")) {
                // Set up observer to initialize when modal becomes active
                var table = this;
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.target.classList.contains("active")) {
                            initDataTable(table);
                            observer.disconnect();
                        }
                    });
                });
                observer.observe(modal[0], { attributes: true, attributeFilter: ["class"] });
                return;
            }

            initDataTable(this);
        });
    });

    function initDataTable(table) {
        if ($.fn.DataTable.isDataTable(table)) return;

        // Check if table has data rows
        var rows = $(table).find("tbody tr");
        if (rows.length === 0) return;
        if (rows.length === 1 && rows.find("td[colspan]").length > 0) return; // "No data" row

        $(table).DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            language: {
                search: "Filter:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                emptyTable: "No data available",
                zeroRecords: "No matching records found"
            },
            dom: '<"datatable-top"lf>rt<"datatable-bottom"ip>',
            order: [],
            columnDefs: [
                // Don't sort the "Actions" column (usually last)
                { orderable: false, targets: -1 }
            ],
            responsive: true,
            autoWidth: false
        });
    }
});
