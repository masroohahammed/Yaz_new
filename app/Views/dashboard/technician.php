<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-person-gear me-2 text-primary"></i>My Dashboard</h1><div class="small text-muted"><?= date('l, d F Y') ?></div></div>
</div>

<!-- Check-in/out Widget -->
<div class="fm-card mb-3" style="background:linear-gradient(135deg,var(--fm-navy),#0d3a5c)">
  <div class="fm-card-body">
    <div class="row g-3 align-items-center">
      <div class="col-md-5">
        <h6 class="text-white mb-1"><i class="bi bi-geo-alt me-2" style="color:var(--fm-gold)"></i>Attendance & GPS</h6>
        <?php if($todayAtt && $todayAtt['check_in'] && !$todayAtt['check_out']): ?>
        <div class="text-success small mb-2">● Checked in at <?= date('H:i',strtotime($todayAtt['check_in'])) ?></div>
        <?php elseif($todayAtt && $todayAtt['check_out']): ?>
        <div class="text-muted small mb-2">✓ Completed — <?= number_format($todayAtt['hours_worked']??0,1) ?>h worked</div>
        <?php else: ?>
        <div class="text-warning small mb-2">○ Not checked in yet</div>
        <?php endif; ?>
        <div class="d-flex gap-2 flex-wrap">
          <?php if(!$todayAtt || !$todayAtt['check_in']): ?>
          <button class="btn btn-sm btn-success" onclick="doCheckin()"><i class="bi bi-box-arrow-in-right me-1"></i>Check In</button>
          <?php elseif($todayAtt && !$todayAtt['check_out']): ?>
          <button class="btn btn-sm btn-danger" onclick="doCheckout()"><i class="bi bi-box-arrow-right me-1"></i>Check Out</button>
          <button class="btn btn-sm btn-warning" onclick="doBreak('start')"><i class="bi bi-pause-circle me-1"></i>Start Break</button>
          <button class="btn btn-sm btn-outline-light btn-sm" onclick="doBreak('end')"><i class="bi bi-play-circle me-1"></i>End Break</button>
          <?php endif; ?>
        </div>
        <div id="att-msg" class="small mt-2"></div>
      </div>
      <div class="col-md-3 text-center">
        <div class="text-white" style="font-size:.75rem;opacity:.7">Tasks Today</div>
        <div class="text-white fw-bold" style="font-size:2.5rem"><?= count($myWO ?? []) ?></div>
        <div class="text-white" style="font-size:.75rem;opacity:.7">assigned</div>
      </div>
      <div class="col-md-2 text-center">
        <div class="text-white" style="font-size:.75rem;opacity:.7">Completed</div>
        <div class="text-success fw-bold" style="font-size:2.5rem"><?= $completedToday ?></div>
        <div class="text-white" style="font-size:.75rem;opacity:.7">today</div>
      </div>
      <div class="col-md-2 text-center">
        <div class="text-white" style="font-size:.75rem;opacity:.7">Shift</div>
        <div class="text-white fw-semibold"><?= $emp ? substr($emp['shift_start'],0,5).' – '.substr($emp['shift_end'],0,5) : '08:00 – 17:00' ?></div>
      </div>
    </div>
  </div>
</div>

<!-- My Tasks -->
<div class="fm-card">
  <div class="card-header-fm"><h5><i class="bi bi-list-task me-2"></i>My Assigned Tasks</h5><a href="<?= base_url('workorders') ?>" class="small text-primary">View all</a></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
  <table class="fm-table"><thead><tr><th>WO Number</th><th>Title</th><th>Facility</th><th>Priority</th><th>SLA Due</th><th>Status</th><th>Actions</th></tr></thead><tbody>
  <?php foreach($myWO as $wo): ?>
  <?php $overdue = $wo['sla_due'] && strtotime($wo['sla_due']) < time(); ?>
  <tr class="<?= $overdue ? 'sla-warn' : '' ?>">
    <td class="fw-semibold small"><a href="<?= base_url('workorders/view/'.$wo['id']) ?>" class="text-primary"><?= esc($wo['wo_number']) ?></a></td>
    <td class="small"><?= esc($wo['title']) ?></td>
    <td class="small text-muted"><?= esc($wo['facility_name']) ?></td>
    <td><span class="fm-badge badge-priority-<?= $wo['priority'] ?>"><?= ucfirst($wo['priority']) ?></span></td>
    <td class="small <?= $overdue ? 'text-danger fw-bold' : '' ?>"><?= $wo['sla_due'] ? date('d M H:i',strtotime($wo['sla_due'])) : '—' ?><?= $overdue ? ' ⚠️' : '' ?></td>
    <td><span class="fm-badge badge-status-<?= $wo['status'] ?>"><?= ucfirst(str_replace('_',' ',$wo['status'])) ?></span></td>
    <td><a href="<?= base_url('workorders/view/'.$wo['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary"><i class="bi bi-eye"></i></a></td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($myWO)): ?><tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-check-circle text-success me-2"></i>No pending tasks — all clear!</td></tr><?php endif; ?>
  </tbody></table></div></div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title">Check Out</h6></div>
  <div class="modal-body">
    <label class="form-label small">Reason (if early checkout)</label>
    <textarea id="checkout-reason" class="form-control" rows="2" placeholder="Optional..."></textarea>
  </div>
  <div class="modal-footer"><button class="btn btn-fm-outline btn-sm" data-bs-dismiss="modal">Cancel</button><button class="btn btn-fm-primary btn-sm" onclick="confirmCheckout()">Confirm Check Out</button></div>
</div></div></div>

<?= $this->section('scripts') ?>
<script>
async function doCheckin(){
  let lat=null,lng=null;
  try{const pos=await new Promise((res,rej)=>navigator.geolocation.getCurrentPosition(res,rej,{timeout:5000}));lat=pos.coords.latitude;lng=pos.coords.longitude;}catch(e){}
  const fd=new FormData();if(lat)fd.append('latitude',lat);if(lng)fd.append('longitude',lng);
  const r=await fetch('<?= base_url('attendance/checkin') ?>',{method:'POST',body:fd});
  const d=await r.json();
  document.getElementById('att-msg').innerHTML='<span class="text-success">'+d.message+'</span>';
  setTimeout(()=>location.reload(),1500);
}
function doCheckout(){new bootstrap.Modal(document.getElementById('checkoutModal')).show();}
async function confirmCheckout(){
  const reason=document.getElementById('checkout-reason').value;
  const fd=new FormData();fd.append('reason',reason);
  const r=await fetch('<?= base_url('attendance/checkout') ?>',{method:'POST',body:fd});
  const d=await r.json();
  document.getElementById('att-msg').innerHTML='<span class="text-info">'+d.message+'</span>';
  bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
  setTimeout(()=>location.reload(),1500);
}
async function doBreak(action){
  const r=await fetch('<?= base_url('attendance/break') ?>'+(action==='end'?'/end':''),{method:'POST'});
  const d=await r.json();
  document.getElementById('att-msg').innerHTML='<span class="text-warning">'+d.message+'</span>';
}
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
