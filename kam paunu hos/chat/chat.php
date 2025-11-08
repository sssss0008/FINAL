<?php include '../header.php'; 
$job_id = $_GET['job_id'] ?? 0;
?>
<h2>Chat</h2>
<div id="chat-box" style="height:400px; overflow-y:scroll; border:1px solid #ccc; padding:10px; background:white;"></div>
<textarea id="msg" placeholder="Type message..." style="width:100%; height:100px;"></textarea><br>
<button onclick="send()">Send</button>

<script>
function send(){
    let msg = document.getElementById('msg').value;
    fetch('../chat/send_message.php?msg='+encodeURIComponent(msg)+'&job_id=<?php echo $job_id; ?>');
    document.getElementById('msg').value = '';
}
setInterval(() => {
    fetch('../chat/get_messages.php?job_id=<?php echo $job_id; ?>')
    .then(r => r.text())
    .then(d => document.getElementById('chat-box').innerHTML = d);
}, 2000);
</script>
<?php include '../footer.php'; ?>