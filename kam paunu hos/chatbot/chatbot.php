<div class="chatbot-container">
    <div class="chatbot-toggle" onclick="toggleChat()">
        <img src="assets/images/bot-avatar.png" alt="KAM PAUNUHOS Helper">
    </div>
    <div class="chatbot-popup" id="chatbotPopup">
        <div class="chatbot-header">
            KAM PAUNUHOS Helper
            <div style="font-size:12px;">Always here to help!</div>
        </div>
        <div class="chatbot-body" id="chatBody">
            <div class="bot-message">Namaste! Welcome to KAM PAUNUHOS!<br>How can I help you today?</div>
        </div>
        <div class="chatbot-footer">
            <input type="text" id="userInput" placeholder="Type a message..." onkeypress="if(event.key==='Enter') sendMessage()">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
</div>

<script>
function toggleChat() {
    let popup = document.getElementById('chatbotPopup');
    popup.style.display = popup.style.display === 'flex' ? 'none' : 'flex';
}
function sendMessage() {
    let input = document.getElementById('userInput');
    let msg = input.value.trim();
    if (!msg) return;
    let chatBody = document.getElementById('chatBody');
    chatBody.innerHTML += `<div class="message user-message">${msg}</div>`;
    chatBody.innerHTML += `<div class="message bot-message typing">Typing...</div>`;
    chatBody.scrollTop = chatBody.scrollHeight;
    fetch('chatbot/process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(msg)
    })
    .then(r => r.text())
    .then(response => {
        chatBody.innerHTML = chatBody.innerHTML.replace('Typing...', response);
        chatBody.scrollTop = chatBody.scrollHeight;
    });
    input.value = '';
}
setTimeout(toggleChat, 3000);
</script>