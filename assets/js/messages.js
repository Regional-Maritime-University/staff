document.addEventListener("DOMContentLoaded", () => {
    // New Message Modal
    const newMessageBtn = document.getElementById("newMessageBtn");
    const closeNewMessageModal = document.getElementById("closeNewMessageModal");
    const cancelNewMessage = document.getElementById("cancelNewMessage");
    const sendNewMessage = document.getElementById("sendNewMessage");
    const newMessageModal = document.getElementById("newMessageModal");
    
    // File attachment handling
    const messageAttachment = document.getElementById("messageAttachment");
    const selectedFileName = document.getElementById("selectedFileName");
    
    messageAttachment.addEventListener("change", function() {
        if (this.files.length > 0) {
            selectedFileName.textContent = this.files[0].name;
        } else {
            selectedFileName.textContent = "No file selected";
        }
    });
    
    // Open new message modal
    newMessageBtn.addEventListener("click", () => {
        newMessageModal.classList.add("active");
        document.body.style.overflow = "hidden";
    });
    
    // Close new message modal
    closeNewMessageModal.addEventListener("click", () => {
        newMessageModal.classList.remove("active");
        document.body.style.overflow = "auto";
    });
    
    cancelNewMessage.addEventListener("click", () => {
        newMessageModal.classList.remove("active");
        document.body.style.overflow = "auto";
    });
    
    // Close modal when clicking outside
    newMessageModal.addEventListener("click", function(e) {
        if (e.target === this) {
            this.classList.remove("active");
            document.body.style.overflow = "auto";
        }
    });
    
    // Send new message
    sendNewMessage.addEventListener("click", () => {
        const recipient = document.getElementById("recipientSelect").value;
        const subject = document.getElementById("messageSubject").value;
        const content = document.getElementById("messageContent").value;
        
        if (!recipient || !subject || !content) {
            alert("Please fill in all required fields");
            return;
        }
        
        // Simulate sending message
        console.log("Sending message:", {
            recipient,
            subject,
            content,
            attachment: messageAttachment.files[0] ? messageAttachment.files[0].name : null
        });
        
        // Simulate successful sending
        setTimeout(() => {
            alert("Message sent successfully!");
            newMessageModal.classList.remove("active");
            document.body.style.overflow = "auto";
            document.getElementById("recipientSelect").value = "";
            document.getElementById("messageSubject").value = "";
            document.getElementById("messageContent").value = "";
            messageAttachment.value = "";
            selectedFileName.textContent = "No file selected";
        }, 1000);
    });
    
    // Contact filtering
    const filterBtns = document.querySelectorAll(".filter-btn");
    const contactItems = document.querySelectorAll(".contact-item");
    
    filterBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            const filter = this.getAttribute("data-filter");
            
            // Remove active class from all filter buttons
            filterBtns.forEach(btn => btn.classList.remove("active"));
            
            // Add active class to clicked button
            this.classList.add("active");
            
            // Filter contacts
            if (filter === "all") {
                contactItems.forEach(item => item.style.display = "flex");
            } else if (filter === "unread") {
                contactItems.forEach(item => {
                    const unreadBadge = item.querySelector(".unread-badge");
                    item.style.display = unreadBadge ? "flex" : "none";
                });
            } else if (filter === "lecturers") {
                // In a real app, you would have data attributes to identify lecturers
                // For this demo, we'll just show all
                contactItems.forEach(item => item.style.display = "flex");
            } else if (filter === "hods") {
                // In a real app, you would have data attributes to identify HODs
                // For this demo, we'll just show a subset
                contactItems.forEach((item, index) => {
                    item.style.display = index > 2 ? "flex" : "none";
                });
            }
        });
    });
    
    // Contact selection
    contactItems.forEach(item => {
        item.addEventListener("click", function() {
            // Remove active class from all contact items
            contactItems.forEach(item => item.classList.remove("active"));
            
            // Add active class to clicked item
            this.classList.add("active");
            
            // In a real app, you would load the conversation for this contact
            const contactId = this.getAttribute("data-id");
            console.log("Loading conversation for contact ID:", contactId);
            
            // For demo purposes, we'll just update the chat header
            const contactName = this.querySelector(".contact-name").textContent;
            const contactStatus = this.querySelector(".status-indicator").classList.contains("online") ? "Online" : 
                                 this.querySelector(".status-indicator").classList.contains("away") ? "Away" : "Offline";
            
            document.querySelector(".chat-contact .contact-name").textContent = contactName;
            document.querySelector(".chat-contact .contact-status").textContent = contactStatus;
            
            // Remove unread badge if present
            const unreadBadge = this.querySelector(".unread-badge");
            if (unreadBadge) {
                unreadBadge.remove();
            }
        });
    });
    
    // Send message in chat
    const chatInput = document.querySelector(".chat-input");
    const sendBtn = document.querySelector(".send-btn");
    
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Get current time
        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        const timeString = `${formattedHours}:${formattedMinutes} ${ampm}`;
        
        // Create message element
        const messageElement = document.createElement("div");
        messageElement.className = "message sent";
        messageElement.innerHTML = `
            <div class="message-content">
                <div class="message-bubble">
                    ${message}
                </div>
                <div class="message-time">${timeString}</div>
            </div>
        `;
        
        // Add message to chat
        const chatMessages = document.querySelector(".chat-messages");
        chatMessages.appendChild(messageElement);
        
        // Clear input
        chatInput.value = "";
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // In a real app, you would send the message to the server
        console.log("Sending message:", message);
        
        // Simulate reply after a delay
        setTimeout(() => {
            const replyElement = document.createElement("div");
            replyElement.className = "message received";
            replyElement.innerHTML = `
                <div class="message-avatar">
                    <img src="/placeholder.svg?height=40&width=40" alt="Dr. James Wilson">
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        Thank you for your message. I'll get back to you shortly.
                    </div>
                    <div class="message-time">${timeString}</div>
                </div>
            `;
            
            chatMessages.appendChild(replyElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 2000);
    }
    
    sendBtn.addEventListener("click", sendMessage);
    
    chatInput.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            sendMessage();
        }
    });
});

