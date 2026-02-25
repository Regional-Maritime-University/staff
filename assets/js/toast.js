/**
 * Toast Notification System
 *
 * Usage:
 *   showToast('Course added successfully', 'success');
 *   showToast('Failed to save', 'error');
 *   showToast('Please check the form', 'warning');
 *   showToast('Loading data...', 'info');
 */

(function () {
    'use strict';

    // Create toast container on load
    let container = null;

    function getContainer() {
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle',
    };

    /**
     * Show a toast notification
     * @param {string} message - The message to display
     * @param {string} type - 'success' | 'error' | 'warning' | 'info'
     * @param {number} duration - Duration in ms (default 4000)
     */
    window.showToast = function (message, type, duration) {
        type = type || 'info';
        duration = duration || 4000;

        const toastContainer = getContainer();

        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;

        const icon = icons[type] || icons.info;

        toast.innerHTML =
            '<div class="toast-icon"><i class="fas ' + icon + '"></i></div>' +
            '<div class="toast-message">' + message + '</div>' +
            '<button class="toast-close"><i class="fas fa-times"></i></button>';

        toastContainer.appendChild(toast);

        // Trigger entrance animation
        requestAnimationFrame(function () {
            toast.classList.add('toast-visible');
        });

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', function () {
            removeToast(toast);
        });

        // Auto-dismiss
        var timer = setTimeout(function () {
            removeToast(toast);
        }, duration);

        // Pause on hover
        toast.addEventListener('mouseenter', function () {
            clearTimeout(timer);
        });

        toast.addEventListener('mouseleave', function () {
            timer = setTimeout(function () {
                removeToast(toast);
            }, 2000);
        });
    };

    function removeToast(toast) {
        toast.classList.remove('toast-visible');
        toast.classList.add('toast-exit');
        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
})();
