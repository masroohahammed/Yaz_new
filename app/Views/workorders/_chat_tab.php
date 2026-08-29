{{-- INSTRUCTIONS --}}
{{-- In app/Views/workorders/view.php, find the nav-tabs ul and add this tab link: --}}
{{--   <li class="nav-item"><a class="nav-link" href="#tab-chat" data-bs-toggle="tab"><i class="bi bi-chat-dots me-1"></i>Team Chat <span class="badge bg-primary ms-1" id="chatCount"><?= count($chatMessages) ?></span></a></li> --}}
{{-- Then add this tab pane inside .tab-content, after #tab-activity and before #tab-approval: --}}

<!-- ============================================================
     COPY THIS ENTIRE BLOCK into app/Views/workorders/view.php
     INSIDE the .tab-content div, after #tab-activity pane
     ============================================================ -->

  <div class="tab-pane fade" id="tab-chat">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-chat-dots me-2"></i>Team Chat</h5>
        <span class="x-small text-muted">Internal discussion — not visible to client</span>
      </div>
      <div class="fm-card-body p-0">

        <!-- Chat messages area -->
        <div id="chatMessages" style="height:380px;overflow-y:auto;padding:16px;background:#fafafa;display:flex;flex-direction:column;gap:10px">
          <?php if(empty($chatMessages)): ?>
          <div class="text-center text-muted small py-5" id="chatEmpty">
            <i class="bi bi-chat-dots fs-2 d-block mb-2 opacity-25"></i>
            No messages yet. Start the team discussion below.
          </div>
          <?php endif; ?>
          <?php foreach($chatMessages as $msg):
            $isMe = (int)$msg['user_id'] === (int)$currentUser['id'];
          ?>
          <div class="d-flex <?= $isMe?'justify-content-end':'' ?> gap-2" data-msg-id="<?= $msg['id'] ?>">
            <?php if(!$isMe): ?>
            <div class="user-avatar flex-shrink-0" style="width:30px;height:30px;font-size:.65rem"><?= strtoupper(substr($msg['sender_name']??'U',0,1)) ?></div>
            <?php endif; ?>
            <div style="max-width:70%">
              <?php if(!$isMe): ?><div class="x-small text-muted mb-1 fw-semibold"><?= esc($msg['sender_name']) ?></div><?php endif; ?>
              <div class="rounded-3 px-3 py-2 <?= $isMe?'text-white':'bg-white border' ?>" style="<?= $isMe?"background:var(--fm-primary)":"" ?>;font-size:.83rem;line-height:1.5">
                <?= nl2br(esc($msg['message'])) ?>
              </div>
              <div class="x-small text-muted mt-1 <?= $isMe?'text-end':'' ?>"><?= date('d M H:i',strtotime($msg['created_at'])) ?></div>
            </div>
            <?php if($isMe): ?>
            <div class="user-avatar flex-shrink-0" style="width:30px;height:30px;font-size:.65rem"><?= strtoupper(substr($currentUser['name']??'U',0,1)) ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Chat input -->
        <div class="border-top p-3 bg-white">
          <div class="d-flex gap-2 align-items-end">
            <textarea id="chatInput" class="form-control" rows="2"
              placeholder="Type a message… (Enter to send, Shift+Enter for new line)"
              style="resize:none;font-size:.83rem;border-radius:10px"></textarea>
            <button id="chatSend" class="btn btn-fm-primary px-3" style="height:60px">
              <i class="bi bi-send"></i>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

<!-- ============================================================
     END OF CHAT TAB PANE
     ============================================================ -->

<!-- Also add this script at the BOTTOM of workorders/view.php scripts section: -->
<script>
// Initialize WO Chat (powered by fm-global.js)
(function(){
  const WO_ID = <?= $wo['id'] ?>;
  const MY_ID = <?= $currentUser['id'] ?>;

  const messagesEl = document.getElementById('chatMessages');
  const inputEl    = document.getElementById('chatInput');
  const sendBtn    = document.getElementById('chatSend');
  const emptyEl    = document.getElementById('chatEmpty');
  const countBadge = document.getElementById('chatCount');

  if(!messagesEl) return;

  let lastId = <?= !empty($chatMessages) ? end($chatMessages)['id'] : 0 ?>;

  function scrollBottom(){ messagesEl.scrollTop = messagesEl.scrollHeight; }
  scrollBottom();

  function avatarLetter(name){ return (name||'U')[0].toUpperCase(); }

  function appendMsg(msg){
    if(emptyEl) emptyEl.remove();
    const isMe = parseInt(msg.user_id) === parseInt(MY_ID);
    const wrap  = document.createElement('div');
    wrap.className = `d-flex ${isMe?'justify-content-end':''} gap-2`;
    wrap.dataset.msgId = msg.id;
    wrap.innerHTML = `
      ${!isMe ? `<div class="user-avatar flex-shrink-0" style="width:30px;height:30px;font-size:.65rem">${avatarLetter(msg.sender_name)}</div>` : ''}
      <div style="max-width:70%">
        ${!isMe ? `<div class="x-small text-muted mb-1 fw-semibold">${msg.sender_name||'User'}</div>` : ''}
        <div class="rounded-3 px-3 py-2 ${isMe?'text-white':'bg-white border'}" style="${isMe?'background:var(--fm-primary)':''};font-size:.83rem;line-height:1.5">
          ${msg.message.replace(/\n/g,'<br>').replace(/</g,'&lt;').replace(/>/g,'&gt;')}
        </div>
        <div class="x-small text-muted mt-1 ${isMe?'text-end':''}">${msg.created_at}</div>
      </div>
      ${isMe ? `<div class="user-avatar flex-shrink-0" style="width:30px;height:30px;font-size:.65rem"><?= strtoupper(substr($currentUser['name']??'U',0,1)) ?></div>` : ''}
    `;
    messagesEl.appendChild(wrap);
    scrollBottom();
    lastId = Math.max(lastId, msg.id);
    if(countBadge) countBadge.textContent = parseInt(countBadge.textContent||0)+1;
  }

  // Poll every 8s for new messages
  setInterval(function(){
    fetch(`${window.BASE_URL}ajax/wo-chat/${WO_ID}?after=${lastId}`,{
      headers:{'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.json()).then(data=>{
      if(data.messages && data.messages.length){
        data.messages.forEach(m=>{ if(!document.querySelector(`[data-msg-id="${m.id}"]`)) appendMsg(m); });
      }
    }).catch(()=>{});
  }, 8000);

  // Send message
  function sendMessage(){
    const msg = inputEl.value.trim();
    if(!msg) return;
    sendBtn.disabled = true;
    inputEl.value = '';

    FM.post(`${window.BASE_URL}ajax/wo-chat/${WO_ID}`, { message: msg })
    .then(data=>{
      if(data.status && data.msg) appendMsg(data.msg);
      else { FM.toast(data.message||'Send failed.','error'); inputEl.value = msg; }
    }).catch(()=>{ FM.toast('Network error.','error'); inputEl.value = msg; })
    .finally(()=>{ sendBtn.disabled = false; inputEl.focus(); });
  }

  sendBtn.addEventListener('click', sendMessage);
  inputEl.addEventListener('keydown', e=>{
    if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); sendMessage(); }
  });
})();
</script>
