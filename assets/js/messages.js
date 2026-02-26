document.addEventListener("DOMContentLoaded", function() {

    // ── Modal helpers ──
    function openModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
        }
    }
    function closeModal(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove("active");
            document.body.style.overflow = "auto";
        }
    }

    // ── New Message Modal ──
    var newMessageBtn = document.getElementById("newMessageBtn");
    var closeNewMessageModal = document.getElementById("closeNewMessageModal");
    var cancelNewMessage = document.getElementById("cancelNewMessage");
    var sendNewMessage = document.getElementById("sendNewMessage");
    var newMessageModal = document.getElementById("newMessageModal");
    var messageAttachment = document.getElementById("messageAttachment");
    var selectedFileName = document.getElementById("selectedFileName");

    if (messageAttachment) {
        messageAttachment.addEventListener("change", function() {
            if (this.files.length > 0) {
                var names = Array.from(this.files).map(function(f) { return f.name; });
                selectedFileName.textContent = names.join(", ");
            } else {
                selectedFileName.textContent = "No files selected";
            }
        });
    }

    if (newMessageBtn) newMessageBtn.addEventListener("click", function() { openModal("newMessageModal"); });
    if (closeNewMessageModal) closeNewMessageModal.addEventListener("click", function() { closeModal("newMessageModal"); });
    if (cancelNewMessage) cancelNewMessage.addEventListener("click", function() { closeModal("newMessageModal"); });

    if (newMessageModal) {
        newMessageModal.addEventListener("click", function(e) {
            if (e.target === this) closeModal("newMessageModal");
        });
    }

    if (sendNewMessage) {
        sendNewMessage.addEventListener("click", function() {
            var recipient = document.getElementById("recipientSelect").value;
            var subject = document.getElementById("messageSubject").value;
            var content = document.getElementById("messageContent").value;

            if (!recipient || !subject || !content) {
                alert("Please fill in all required fields");
                return;
            }

            console.log("Sending message:", { recipient: recipient, subject: subject, content: content });
            setTimeout(function() {
                alert("Message sent successfully!");
                closeModal("newMessageModal");
                document.getElementById("recipientSelect").value = "";
                document.getElementById("messageSubject").value = "";
                document.getElementById("messageContent").value = "";
                if (messageAttachment) messageAttachment.value = "";
                selectedFileName.textContent = "No files selected";
            }, 1000);
        });
    }

    // ── Bulk Message Modal ──
    var bulkMessageBtn = document.getElementById("bulkMessageBtn");
    var closeBulkMessageModal = document.getElementById("closeBulkMessageModal");
    var cancelBulkMessage = document.getElementById("cancelBulkMessage");
    var sendBulkMessage = document.getElementById("sendBulkMessage");
    var bulkMessageModal = document.getElementById("bulkMessageModal");

    if (bulkMessageBtn) bulkMessageBtn.addEventListener("click", function() { openModal("bulkMessageModal"); });
    if (closeBulkMessageModal) closeBulkMessageModal.addEventListener("click", function() { closeModal("bulkMessageModal"); });
    if (cancelBulkMessage) cancelBulkMessage.addEventListener("click", function() { closeModal("bulkMessageModal"); });

    if (bulkMessageModal) {
        bulkMessageModal.addEventListener("click", function(e) {
            if (e.target === this) closeModal("bulkMessageModal");
        });
    }

    // Show/hide custom recipients based on radio selection
    document.querySelectorAll('input[name="bulkTarget"]').forEach(function(radio) {
        radio.addEventListener("change", function() {
            var customGroup = document.getElementById("customRecipientsGroup");
            if (customGroup) {
                customGroup.style.display = this.value === "custom" ? "block" : "none";
            }
        });
    });

    // Bulk attachment handling
    var bulkAttachment = document.getElementById("bulkAttachment");
    var bulkFileName = document.getElementById("bulkFileName");
    if (bulkAttachment) {
        bulkAttachment.addEventListener("change", function() {
            if (this.files.length > 0) {
                var names = Array.from(this.files).map(function(f) { return f.name; });
                bulkFileName.textContent = names.join(", ");
            } else {
                bulkFileName.textContent = "No files selected";
            }
        });
    }

    if (sendBulkMessage) {
        sendBulkMessage.addEventListener("click", function() {
            var target = document.querySelector('input[name="bulkTarget"]:checked');
            var subject = document.getElementById("bulkSubject").value;
            var content = document.getElementById("bulkContent").value;

            if (!subject || !content) {
                alert("Please fill in subject and message");
                return;
            }

            var targetLabel = target ? target.value : "unknown";
            console.log("Sending bulk message to:", targetLabel, { subject: subject, content: content });

            setTimeout(function() {
                alert("Bulk message sent successfully to " + targetLabel.replace(/-/g, " ") + "!");
                closeModal("bulkMessageModal");
                document.getElementById("bulkSubject").value = "";
                document.getElementById("bulkContent").value = "";
                if (bulkAttachment) bulkAttachment.value = "";
                if (bulkFileName) bulkFileName.textContent = "No files selected";
            }, 1000);
        });
    }

    // ── Contact filtering (with data-role support) ──
    var filterBtns = document.querySelectorAll(".contacts-filter .filter-btn");
    var contactItems = document.querySelectorAll(".contact-item");

    filterBtns.forEach(function(btn) {
        btn.addEventListener("click", function() {
            var filter = this.getAttribute("data-filter");
            filterBtns.forEach(function(b) { b.classList.remove("active"); });
            this.classList.add("active");

            contactItems.forEach(function(item) {
                if (filter === "all") {
                    item.style.display = "flex";
                } else if (filter === "unread") {
                    item.style.display = item.classList.contains("unread") ? "flex" : "none";
                } else if (filter === "lecturers") {
                    item.style.display = item.getAttribute("data-role") === "lecturer" ? "flex" : "none";
                } else if (filter === "hods") {
                    item.style.display = item.getAttribute("data-role") === "hod" ? "flex" : "none";
                }
            });
        });
    });

    // ── Contact search ──
    var contactSearchInput = document.getElementById("contactSearchInput");
    if (contactSearchInput) {
        contactSearchInput.addEventListener("input", function() {
            var query = this.value.toLowerCase().trim();
            contactItems.forEach(function(item) {
                var text = item.textContent.toLowerCase();
                item.style.display = text.includes(query) ? "flex" : "none";
            });
        });
    }

    // ── Contact selection with read/unread management ──
    contactItems.forEach(function(item) {
        item.addEventListener("click", function() {
            contactItems.forEach(function(i) { i.classList.remove("active"); });
            this.classList.add("active");

            var contactId = this.getAttribute("data-id");
            console.log("Loading conversation for contact ID:", contactId);

            // Update chat header
            var contactName = this.querySelector(".contact-name").textContent;
            var statusEl = this.querySelector(".status-indicator");
            var contactStatus = statusEl.classList.contains("online") ? "Online" :
                               statusEl.classList.contains("away") ? "Away" : "Offline";

            var chatNameEl = document.querySelector(".chat-contact .contact-name");
            var chatStatusEl = document.querySelector(".chat-contact .contact-status");
            if (chatNameEl) chatNameEl.textContent = contactName;
            if (chatStatusEl) chatStatusEl.textContent = contactStatus;

            // Update thread subject
            var subjectEl = this.querySelector(".contact-subject");
            var threadSubjectEl = document.getElementById("threadSubject");
            if (threadSubjectEl && subjectEl) {
                threadSubjectEl.textContent = subjectEl.textContent;
            }

            // Mark as read - remove unread state
            this.classList.remove("unread");
            this.classList.add("read");
            var unreadBadge = this.querySelector(".unread-badge");
            if (unreadBadge) {
                unreadBadge.remove();
                // Add read indicator
                var meta = this.querySelector(".contact-meta");
                if (meta && !meta.querySelector(".read-indicator")) {
                    var readInd = document.createElement("div");
                    readInd.className = "read-indicator";
                    readInd.title = "Read";
                    readInd.innerHTML = '<i class="fas fa-check-double" aria-hidden="true"></i>';
                    meta.appendChild(readInd);
                }
            }
        });
    });

    // ── Mark as unread button ──
    var markUnreadBtn = document.getElementById("markUnreadBtn");
    if (markUnreadBtn) {
        markUnreadBtn.addEventListener("click", function() {
            var activeContact = document.querySelector(".contact-item.active");
            if (activeContact) {
                activeContact.classList.remove("read");
                activeContact.classList.add("unread");
                var readInd = activeContact.querySelector(".read-indicator");
                if (readInd) readInd.remove();
                // Add unread badge back
                var meta = activeContact.querySelector(".contact-meta");
                if (meta && !meta.querySelector(".unread-badge")) {
                    var badge = document.createElement("div");
                    badge.className = "unread-badge";
                    badge.textContent = "1";
                    meta.appendChild(badge);
                }
            }
        });
    }

    // ── Chat file attachment ──
    var chatAttachBtn = document.getElementById("chatAttachBtn");
    var chatFileInput = document.getElementById("chatFileInput");
    var attachmentPreview = document.getElementById("attachmentPreview");
    var attachmentPreviewName = document.getElementById("attachmentPreviewName");
    var removeAttachment = document.getElementById("removeAttachment");

    if (chatAttachBtn && chatFileInput) {
        chatAttachBtn.addEventListener("click", function() {
            chatFileInput.click();
        });

        chatFileInput.addEventListener("change", function() {
            if (this.files.length > 0) {
                var names = Array.from(this.files).map(function(f) { return f.name; });
                attachmentPreviewName.textContent = names.join(", ");
                attachmentPreview.style.display = "block";
            }
        });
    }

    if (removeAttachment) {
        removeAttachment.addEventListener("click", function() {
            chatFileInput.value = "";
            attachmentPreview.style.display = "none";
        });
    }

    // ── Send message in chat ──
    var chatInput = document.querySelector(".chat-input");
    var sendBtn = document.querySelector(".send-btn");

    function getTimeString() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var ampm = hours >= 12 ? "PM" : "AM";
        var formattedHours = hours % 12 || 12;
        var formattedMinutes = minutes < 10 ? "0" + minutes : minutes;
        return formattedHours + ":" + formattedMinutes + " " + ampm;
    }

    function sendMessage() {
        var message = chatInput.value.trim();
        var hasAttachment = chatFileInput && chatFileInput.files.length > 0;
        if (!message && !hasAttachment) return;

        var timeString = getTimeString();
        var chatMessages = document.querySelector(".chat-messages");

        // Build message HTML
        var bubbleHtml = "";
        if (message) {
            bubbleHtml += '<div class="message-bubble">' + escapeHtml(message) + '</div>';
        }
        if (hasAttachment) {
            var attachHtml = '<div class="message-attachment">';
            Array.from(chatFileInput.files).forEach(function(file) {
                var sizeKB = Math.round(file.size / 1024);
                attachHtml += '<div class="attachment-item"><i class="fas fa-file" aria-hidden="true"></i><span>' +
                    escapeHtml(file.name) + '</span><small>(' + sizeKB + ' KB)</small></div>';
            });
            attachHtml += '</div>';
            bubbleHtml += attachHtml;
        }

        var messageElement = document.createElement("div");
        messageElement.className = "message sent";
        messageElement.innerHTML = '<div class="message-content">' + bubbleHtml +
            '<div class="message-time">' + timeString +
            ' <i class="fas fa-check message-read-tick" aria-hidden="true" title="Sent"></i></div></div>';

        chatMessages.appendChild(messageElement);
        chatInput.value = "";
        if (chatFileInput) chatFileInput.value = "";
        if (attachmentPreview) attachmentPreview.style.display = "none";
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Update contact preview
        var activeContact = document.querySelector(".contact-item.active");
        if (activeContact && message) {
            var preview = activeContact.querySelector(".contact-preview");
            if (preview) preview.textContent = "You: " + message;
            var timeEl = activeContact.querySelector(".contact-meta .message-time");
            if (timeEl) timeEl.textContent = timeString;
        }

        console.log("Sending message:", message);

        // Simulate read tick after delay
        setTimeout(function() {
            var tick = messageElement.querySelector(".message-read-tick");
            if (tick) {
                tick.className = "fas fa-check-double message-read-tick read";
                tick.title = "Read";
            }
        }, 3000);

        // Simulate reply
        setTimeout(function() {
            var contactName = document.querySelector(".chat-contact .contact-name");
            var name = contactName ? contactName.textContent : "Contact";
            var replyElement = document.createElement("div");
            replyElement.className = "message received";
            replyElement.innerHTML =
                '<div class="message-avatar"><img src="../assets/img/icons8-user-96.png" alt="' + escapeHtml(name) + '"></div>' +
                '<div class="message-content"><div class="message-bubble">Thank you for your message. I\'ll get back to you shortly.</div>' +
                '<div class="message-time">' + getTimeString() + '</div></div>';
            chatMessages.appendChild(replyElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 2000);
    }

    if (sendBtn) sendBtn.addEventListener("click", sendMessage);
    if (chatInput) {
        chatInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") sendMessage();
        });
    }

    // ── HTML escape helper ──
    function escapeHtml(text) {
        var div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
});
