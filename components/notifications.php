<div class="notifications-container">
    <div class="notifications-header">
        <div class="notifications-tabs">
            <button class="tab-btn active" data-tab="all">All</button>
            <button class="tab-btn" data-tab="unread">Unread</button>
            <button class="tab-btn" data-tab="important">Important</button>
        </div>
        <div class="notifications-actions">
            <button class="mark-all-read-btn" id="markAllReadBtn">
                <i class="fas fa-check-double"></i> Mark All as Read
            </button>
            <button class="notification-settings-btn" id="notificationSettingsBtn">
                <i class="fas fa-cog"></i>
            </button>
        </div>
    </div>

    <div class="notifications-list">
        <div class="notification-item unread important">
            <div class="notification-icon bg-danger">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="notification-content">
                <h3>Deadline Approaching</h3>
                <p>The deadline for Oceanography (OC205) results submission is tomorrow.</p>
                <div class="notification-meta">
                    <span class="notification-time">2 hours ago</span>
                    <span class="notification-category">Deadlines</span>
                </div>
            </div>
            <div class="notification-actions">
                <button class="mark-read-btn" data-id="1">
                    <i class="fas fa-check"></i>
                </button>
                <button class="notification-menu-btn" data-id="1">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <div class="notification-item unread">
            <div class="notification-icon bg-success">
                <i class="fas fa-file-upload"></i>
            </div>
            <div class="notification-content">
                <h3>Results Submitted</h3>
                <p>Dr. James Wilson has submitted results for Maritime Law (ML201).</p>
                <div class="notification-meta">
                    <span class="notification-time">5 hours ago</span>
                    <span class="notification-category">Results</span>
                </div>
            </div>
            <div class="notification-actions">
                <button class="mark-read-btn" data-id="2">
                    <i class="fas fa-check"></i>
                </button>
                <button class="notification-menu-btn" data-id="2">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <div class="notification-item unread">
            <div class="notification-icon bg-primary">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="notification-content">
                <h3>New Course Assignment</h3>
                <p>You have assigned Navigation Systems (NS302) to Prof. Sarah Johnson.</p>
                <div class="notification-meta">
                    <span class="notification-time">Yesterday</span>
                    <span class="notification-category">Courses</span>
                </div>
            </div>
            <div class="notification-actions">
                <button class="mark-read-btn" data-id="3">
                    <i class="fas fa-check"></i>
                </button>
                <button class="notification-menu-btn" data-id="3">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <div class="notification-item">
            <div class="notification-icon bg-warning">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <div class="notification-content">
                <h3>Deadline Set</h3>
                <p>You have set a deadline for Marine Engineering (ME101) results submission.</p>
                <div class="notification-meta">
                    <span class="notification-time">2 days ago</span>
                    <span class="notification-category">Deadlines</span>
                </div>
            </div>
            <div class="notification-actions">
                <button class="mark-read-btn" data-id="4" style="visibility: hidden;">
                    <i class="fas fa-check"></i>
                </button>
                <button class="notification-menu-btn" data-id="4">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <div class="notification-item important">
            <div class="notification-icon bg-accent">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="notification-content">
                <h3>New Message</h3>
                <p>You have received a new message from Dr. Emily Davis regarding Oceanography (OC205).</p>
                <div class="notification-meta">
                    <span class="notification-time">3 days ago</span>
                    <span class="notification-category">Messages</span>
                </div>
            </div>
            <div class="notification-actions">
                <button class="mark-read-btn" data-id="5" style="visibility: hidden;">
                    <i class="fas fa-check"></i>
                </button>
                <button class="notification-menu-btn" data-id="5">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <div class="notification-item">
            <div class="notification-icon bg-primary">
                <i class="fas fa-upload"></i>
            </div>
            <div class="notification-content">
                <h3>Courses Uploaded</h3>
                <p>You have successfully uploaded 15 new courses for the Fall 2023 semester.</p>
                <div class="notification-meta">
                    <span class="notification-time">1 week ago</span>
                    <span class="notification-category">Courses</span>
                </div>
            </div>
            <div class="notification-actions">
                <button class="mark-read-btn" data-id="6" style="visibility: hidden;">
                    <i class="fas fa-check"></i>
                </button>
                <button class="notification-menu-btn" data-id="6">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <div class="notification-item">
            <div class="notification-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="notification-content">
                <h3>Account Verified</h3>
                <p>Your account has been verified. You now have full access to all features.</p>
                <div class="notification-meta">
                    <span class="notification-time">2 weeks ago</span>
                    <span class="notification-category">Account</span>
                </div>
            </div>
            <div class="notification-actions">
                <button class="mark-read-btn" data-id="7" style="visibility: hidden;">
                    <i class="fas fa-check"></i>
                </button>
                <button class="notification-menu-btn" data-id="7">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Settings Modal -->
<div class="modal" id="notificationSettingsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Notification Settings</h2>
                <button class="close-btn" id="closeNotificationSettingsModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Email Notifications</label>
                    <div class="toggle-switch-container">
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span>Receive email notifications</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notification Categories</label>
                    <div class="checkbox-list">
                        <div class="checkbox-item">
                            <input type="checkbox" id="notifyCourses" checked>
                            <label for="notifyCourses">Courses</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="notifyResults" checked>
                            <label for="notifyResults">Results</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="notifyDeadlines" checked>
                            <label for="notifyDeadlines">Deadlines</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="notifyMessages" checked>
                            <label for="notifyMessages">Messages</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="notifyAccount" checked>
                            <label for="notifyAccount">Account</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notification Preferences</label>
                    <div class="toggle-switch-container">
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span>Show desktop notifications</span>
                    </div>
                    <div class="toggle-switch-container">
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span>Play sound for new notifications</span>
                    </div>
                    <div class="toggle-switch-container">
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="toggle-slider"></span>
                        </label>
                        <span>Auto-mark as read when viewed</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" id="cancelNotificationSettings">Cancel</button>
                <button class="submit-btn" id="saveNotificationSettings">Save Settings</button>
            </div>
        </div>
    </div>
</div>