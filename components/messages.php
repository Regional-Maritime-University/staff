<div class="messages-container">
    <div class="contacts-sidebar">
        <div class="contacts-header">
            <h2>Conversations</h2>
            <div class="contacts-header-actions">
                <button class="new-message-btn" id="bulkMessageBtn" title="Send bulk message" aria-label="Send bulk message">
                    <i class="fas fa-users" aria-hidden="true"></i>
                </button>
                <button class="new-message-btn" id="newMessageBtn" title="New message" aria-label="New message">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="contacts-search">
            <input type="text" id="contactSearchInput" placeholder="Search conversations..." aria-label="Search conversations">
        </div>
        <div class="contacts-filter">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="unread">Unread</button>
            <button class="filter-btn" data-filter="lecturers">Lecturers</button>
            <button class="filter-btn" data-filter="hods">HODs</button>
        </div>
        <div class="contacts-list">
            <div class="contact-item active unread" data-id="1" data-role="lecturer" data-thread="thread-maritime-law">
                <div class="contact-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                    <span class="status-indicator online"></span>
                </div>
                <div class="contact-info">
                    <div class="contact-name">Dr. James Wilson</div>
                    <div class="contact-subject">Re: Maritime Law Course</div>
                    <div class="contact-preview">About the Maritime Law course...</div>
                </div>
                <div class="contact-meta">
                    <div class="message-time">10:30 AM</div>
                    <div class="unread-badge" aria-label="2 unread messages">2</div>
                </div>
            </div>
            <div class="contact-item read" data-id="2" data-role="lecturer" data-thread="thread-semester-update">
                <div class="contact-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Prof. Sarah Johnson">
                    <span class="status-indicator offline"></span>
                </div>
                <div class="contact-info">
                    <div class="contact-name">Prof. Sarah Johnson</div>
                    <div class="contact-subject">Semester Update</div>
                    <div class="contact-preview">Thank you for the update on...</div>
                </div>
                <div class="contact-meta">
                    <div class="message-time">Yesterday</div>
                    <div class="read-indicator" title="Read"><i class="fas fa-check-double" aria-hidden="true"></i></div>
                </div>
            </div>
            <div class="contact-item unread" data-id="3" data-role="lecturer" data-thread="thread-results-submission">
                <div class="contact-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Dr. Michael Brown">
                    <span class="status-indicator away"></span>
                </div>
                <div class="contact-info">
                    <div class="contact-name">Dr. Michael Brown</div>
                    <div class="contact-subject">Results Submission</div>
                    <div class="contact-preview">I'll submit the results by...</div>
                </div>
                <div class="contact-meta">
                    <div class="message-time">Yesterday</div>
                    <div class="unread-badge" aria-label="1 unread message">1</div>
                </div>
            </div>
            <div class="contact-item read" data-id="4" data-role="hod" data-thread="thread-deadline-discussion">
                <div class="contact-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Prof. Robert Taylor">
                    <span class="status-indicator online"></span>
                </div>
                <div class="contact-info">
                    <div class="contact-name">Prof. Robert Taylor</div>
                    <div class="contact-subject">Deadline Discussion</div>
                    <div class="contact-preview">Can we discuss the deadline...</div>
                </div>
                <div class="contact-meta">
                    <div class="message-time">2 days ago</div>
                    <div class="read-indicator" title="Read"><i class="fas fa-check-double" aria-hidden="true"></i></div>
                </div>
            </div>
            <div class="contact-item read" data-id="5" data-role="lecturer" data-thread="thread-course-materials">
                <div class="contact-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Dr. Emily Davis">
                    <span class="status-indicator offline"></span>
                </div>
                <div class="contact-info">
                    <div class="contact-name">Dr. Emily Davis</div>
                    <div class="contact-subject">Course Materials</div>
                    <div class="contact-preview">The course materials are...</div>
                </div>
                <div class="contact-meta">
                    <div class="message-time">3 days ago</div>
                    <div class="read-indicator" title="Read"><i class="fas fa-check-double" aria-hidden="true"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="chat-area">
        <div class="chat-header">
            <div class="chat-contact">
                <div class="contact-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                    <span class="status-indicator online"></span>
                </div>
                <div class="contact-info">
                    <div class="contact-name">Dr. James Wilson</div>
                    <div class="contact-status">Online</div>
                </div>
            </div>
            <div class="chat-thread-subject">
                <i class="fas fa-comments" aria-hidden="true"></i>
                <span id="threadSubject">Maritime Law Course</span>
            </div>
            <div class="chat-actions">
                <button class="chat-action-btn" title="Mark as unread" aria-label="Mark as unread" id="markUnreadBtn">
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                </button>
                <button class="chat-action-btn" title="Phone call" aria-label="Phone call">
                    <i class="fas fa-phone" aria-hidden="true"></i>
                </button>
                <button class="chat-action-btn" title="Video call" aria-label="Video call">
                    <i class="fas fa-video" aria-hidden="true"></i>
                </button>
                <button class="chat-action-btn" title="Conversation info" aria-label="Conversation info">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="chat-messages">
            <div class="message-date">Today</div>

            <div class="message received">
                <div class="message-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        Good morning, I wanted to discuss the Maritime Law course assignment for this semester.
                    </div>
                    <div class="message-time">10:15 AM <i class="fas fa-check-double message-read-tick" aria-hidden="true" title="Read"></i></div>
                </div>
            </div>

            <div class="message received">
                <div class="message-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        I've prepared all the materials and would like to know when the deadline for submission is.
                    </div>
                    <div class="message-time">10:16 AM <i class="fas fa-check-double message-read-tick" aria-hidden="true" title="Read"></i></div>
                </div>
            </div>

            <div class="message sent">
                <div class="message-content">
                    <div class="message-bubble">
                        Good morning Dr. Wilson, the deadline for the Maritime Law course is April 20th.
                    </div>
                    <div class="message-time">10:20 AM <i class="fas fa-check-double message-read-tick read" aria-hidden="true" title="Read"></i></div>
                </div>
            </div>

            <div class="message received">
                <div class="message-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        Thank you for the information. I'll make sure to submit everything before the deadline.
                    </div>
                    <div class="message-time">10:25 AM</div>
                </div>
            </div>

            <div class="message received">
                <div class="message-avatar">
                    <img src="../assets/img/icons8-user-96.png" alt="Dr. James Wilson">
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        Also, I wanted to ask if there are any specific requirements for the submission format?
                    </div>
                    <div class="message-attachment">
                        <div class="attachment-item">
                            <i class="fas fa-file-pdf" aria-hidden="true"></i>
                            <span>submission_guidelines.pdf</span>
                            <small>(245 KB)</small>
                        </div>
                    </div>
                    <div class="message-time">10:30 AM</div>
                </div>
            </div>
        </div>

        <div class="chat-input-area">
            <div class="attachment-preview" id="attachmentPreview" style="display:none;">
                <div class="attachment-preview-item">
                    <i class="fas fa-file" aria-hidden="true"></i>
                    <span id="attachmentPreviewName"></span>
                    <button class="attachment-remove-btn" id="removeAttachment" aria-label="Remove attachment">&times;</button>
                </div>
            </div>
            <div class="chat-input-row">
                <input type="file" id="chatFileInput" class="file-input" multiple>
                <button class="attachment-btn" id="chatAttachBtn" title="Attach file" aria-label="Attach file">
                    <i class="fas fa-paperclip" aria-hidden="true"></i>
                </button>
                <input type="text" class="chat-input" placeholder="Type a message..." aria-label="Type a message">
                <button class="emoji-btn" aria-label="Insert emoji">
                    <i class="fas fa-smile" aria-hidden="true"></i>
                </button>
                <button class="send-btn" aria-label="Send message">
                    <i class="fas fa-paper-plane" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div class="modal" id="newMessageModal" role="dialog" aria-modal="true" aria-labelledby="newMessageModalTitle">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="newMessageModalTitle">New Message</h2>
                <button class="close-btn" id="closeNewMessageModal" aria-label="Close dialog">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="recipientSelect">To:</label>
                    <select id="recipientSelect" required>
                        <option value="">-- Select Recipient --</option>
                        <optgroup label="Lecturers">
                            <option value="1">Dr. James Wilson</option>
                            <option value="2">Prof. Sarah Johnson</option>
                            <option value="3">Dr. Michael Brown</option>
                            <option value="4">Prof. Robert Taylor</option>
                            <option value="5">Dr. Emily Davis</option>
                        </optgroup>
                        <optgroup label="HODs">
                            <option value="6">Prof. John Smith - Maritime Studies</option>
                            <option value="7">Dr. Lisa Anderson - Marine Engineering</option>
                            <option value="8">Prof. David Clark - Nautical Science</option>
                        </optgroup>
                        <optgroup label="Administration">
                            <option value="9">Mr. Richard Thomas - Registrar</option>
                            <option value="10">Mrs. Patricia White - Academic Affairs</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label for="messageSubject">Subject:</label>
                    <input type="text" id="messageSubject" placeholder="Enter subject" required>
                </div>
                <div class="form-group">
                    <label for="messageContent">Message:</label>
                    <textarea id="messageContent" rows="5" placeholder="Type your message here..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="messageAttachment">Attachments (optional):</label>
                    <input type="file" id="messageAttachment" class="file-input" multiple>
                    <label for="messageAttachment" class="file-label"><i class="fas fa-paperclip" aria-hidden="true"></i> Choose Files</label>
                    <span class="selected-file-name" id="selectedFileName">No files selected</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" id="cancelNewMessage">Cancel</button>
                <button class="submit-btn" id="sendNewMessage"><i class="fas fa-paper-plane" aria-hidden="true"></i> Send Message</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Message Modal -->
<div class="modal" id="bulkMessageModal" role="dialog" aria-modal="true" aria-labelledby="bulkMessageModalTitle">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="bulkMessageModalTitle">Send Bulk Message</h2>
                <button class="close-btn" id="closeBulkMessageModal" aria-label="Close dialog">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Recipients:</label>
                    <div class="bulk-recipient-options">
                        <label class="bulk-option">
                            <input type="radio" name="bulkTarget" value="all-lecturers" checked>
                            <span>All Lecturers in Department</span>
                        </label>
                        <label class="bulk-option">
                            <input type="radio" name="bulkTarget" value="all-hods">
                            <span>All HODs</span>
                        </label>
                        <label class="bulk-option">
                            <input type="radio" name="bulkTarget" value="custom">
                            <span>Select Individuals</span>
                        </label>
                    </div>
                </div>
                <div class="form-group" id="customRecipientsGroup" style="display:none;">
                    <label>Select Staff Members:</label>
                    <div class="checkbox-list">
                        <div class="checkbox-item"><input type="checkbox" id="bulk1" value="1"><label for="bulk1">Dr. James Wilson</label></div>
                        <div class="checkbox-item"><input type="checkbox" id="bulk2" value="2"><label for="bulk2">Prof. Sarah Johnson</label></div>
                        <div class="checkbox-item"><input type="checkbox" id="bulk3" value="3"><label for="bulk3">Dr. Michael Brown</label></div>
                        <div class="checkbox-item"><input type="checkbox" id="bulk4" value="4"><label for="bulk4">Prof. Robert Taylor</label></div>
                        <div class="checkbox-item"><input type="checkbox" id="bulk5" value="5"><label for="bulk5">Dr. Emily Davis</label></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="bulkSubject">Subject:</label>
                    <input type="text" id="bulkSubject" placeholder="Enter message subject" required>
                </div>
                <div class="form-group">
                    <label for="bulkContent">Message:</label>
                    <textarea id="bulkContent" rows="6" placeholder="Type your message here..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="bulkAttachment">Attachment (optional):</label>
                    <input type="file" id="bulkAttachment" class="file-input" multiple>
                    <label for="bulkAttachment" class="file-label"><i class="fas fa-paperclip" aria-hidden="true"></i> Choose Files</label>
                    <span class="selected-file-name" id="bulkFileName">No files selected</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" id="cancelBulkMessage">Cancel</button>
                <button class="submit-btn" id="sendBulkMessage"><i class="fas fa-paper-plane" aria-hidden="true"></i> Send to All</button>
            </div>
        </div>
    </div>
</div>
