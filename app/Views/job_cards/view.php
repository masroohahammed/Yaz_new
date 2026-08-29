<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-card-checklist me-2"></i><?= esc($jc['jc_number']) ?></h1>
    <span class="fm-badge badge-status-<?= esc($jc['status']) ?>"><?= ucfirst($jc['status']) ?></span>
  </div>
  <div class="d-flex gap-2">
    <?php if($jc['status']==='completed' && in_array(session()->get('user_role'),['super_admin','facility_manager'])): ?>
    <?= form_open(base_url('job-cards/approve/'.$jc['id'])) ?>
    <button type="submit" class="btn btn-fm-primary btn-sm" onclick="return confirm('Approve this job card?')"><i class="bi bi-shield-check me-1"></i>Approve</button>
    <?= form_close() ?>
    <?php endif; ?>
    <a href="<?= base_url('job-cards/'.$jc['id'].'/print') ?>" class="btn btn-fm-outline btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print</a>
    <?php if(!in_array($jc['status'],['approved'])): ?>
    <a href="<?= base_url('job-cards/edit/'.$jc['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <?php endif; ?>
  </div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-info-circle"></i>Details</h6>
      <div class="small mb-2"><span class="text-muted">Work Order:</span> <a href="<?= base_url('workorders/view/'.$jc['wo_id']) ?>" class="fw-semibold"><?= esc($jc['wo_number']??'—') ?></a></div>
      <div class="small mb-2"><span class="text-muted">Title:</span> <?= esc($jc['wo_title']??'—') ?></div>
      <div class="small mb-2"><span class="text-muted">Facility:</span> <strong><?= esc($jc['facility_name']??'—') ?></strong></div>
      <div class="small mb-2"><span class="text-muted">Priority:</span> <span class="fm-badge badge-priority-<?= esc($jc['priority']??'low') ?>"><?= ucfirst($jc['priority']??'low') ?></span></div>
      <div class="small mb-2"><span class="text-muted">Assigned:</span> <strong><?= esc($jc['assigned_to_name']??'—') ?></strong></div>
      <div class="small mb-2"><span class="text-muted">Labor Hours:</span> <?= number_format($jc['labor_hours'],1) ?>h</div>
      <div class="small mb-2"><span class="text-muted">Created by:</span> <?= esc($jc['created_by_name']??'—') ?></div>
      <div class="small"><span class="text-muted">Created:</span> <?= date('d M Y H:i',strtotime($jc['created_at'])) ?></div>
      <?php if($jc['completed_at']): ?><div class="small mt-1"><span class="text-muted">Completed:</span> <?= date('d M Y',strtotime($jc['completed_at'])) ?></div><?php endif; ?>
      <?php if($jc['approved_by_name']): ?><div class="small mt-1"><span class="text-muted">Approved by:</span> <?= esc($jc['approved_by_name']) ?></div><?php endif; ?>
    </div>
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-currency-dollar"></i>Cost Summary</h6>
      <div class="d-flex justify-content-between small mb-1"><span>Materials:</span><strong><?= $currency ?> <?= number_format($totalMaterialCost,2) ?></strong></div>
      <div class="d-flex justify-content-between small mb-1"><span>Labor (est.):</span><strong><?= $currency ?> <?= number_format($laborCost,2) ?></strong></div>
      <hr><div class="d-flex justify-content-between fw-bold"><span>Total:</span><span style="color:var(--fm-primary)"><?= $currency ?> <?= number_format($totalMaterialCost+$laborCost,2) ?></span></div>
    </div>
    <!-- Update Status -->
    <?php if(!in_array($jc['status'],['approved'])): ?>
    <div class="fm-form-section">
      <h6><i class="bi bi-arrow-right-circle"></i>Update Status</h6>
      <?= form_open(base_url('job-cards/update/'.$jc['id'])) ?>
      <?= form_hidden('description',$jc['description']) ?>
      <?= form_hidden('labor_hours',$jc['labor_hours']) ?>
      <select name="status" class="form-select form-select-sm mb-2">
        <?php foreach(['draft'=>'Draft','in_progress'=>'In Progress','completed'=>'Completed'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= $jc['status']===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <textarea name="completion_notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Completion notes..."><?= esc($jc['completion_notes']??'') ?></textarea>
      <button type="submit" class="btn btn-fm-primary btn-sm w-100">Update</button>
      <?= form_close() ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="col-lg-8">
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item"><a class="nav-link active" href="#tab-desc"      data-bs-toggle="tab">Description</a></li>
      <li class="nav-item"><a class="nav-link"        href="#tab-materials" data-bs-toggle="tab">Materials <span class="badge bg-secondary ms-1"><?= count($materials) ?></span></a></li>
      <li class="nav-item"><a class="nav-link"        href="#tab-photos"    data-bs-toggle="tab">Photos</a></li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tab-desc">
        <div class="fm-card">
          <div class="fm-card-body">
            <div class="small fw-semibold mb-2 text-muted">WORK DESCRIPTION</div>
            <div style="white-space:pre-wrap;font-size:.85rem;line-height:1.7"><?= esc($jc['description']) ?></div>
            <?php if($jc['completion_notes']): ?>
            <hr>
            <div class="small fw-semibold mb-1 text-muted">COMPLETION NOTES</div>
            <div style="white-space:pre-wrap;font-size:.85rem"><?= esc($jc['completion_notes']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="tab-materials">
        <div class="fm-card">
          <div class="card-header-fm">
            <h5><i class="bi bi-box-seam me-2"></i>Materials Used</h5>
          </div>
          <?php if(!in_array($jc['status'],['approved'])): ?>
          <div class="fm-card-body border-bottom">
            <?= form_open(base_url('job-cards/material/add/'.$jc['id'])) ?>
            <div class="row g-2">
              <div class="col-md-3"><select name="item_id" class="form-select form-select-sm"><option value="">— Select Item —</option><?php foreach($inventoryItems as $i): ?><option value="<?= $i['id'] ?>"><?= esc($i['name']) ?> (<?= $i['quantity'] ?? 0 ?> <?= esc($i['unit'] ?? '') ?>)</option><?php endforeach; ?></select></div>
              <div class="col-md-3"><input type="text" name="item_name" class="form-control form-control-sm" placeholder="Or type item name"></div>
              <div class="col-md-2"><input type="number" name="quantity" class="form-control form-control-sm" placeholder="Qty" step="0.1" min="0.1" value="1" required></div>
              <div class="col-md-2"><input type="number" name="unit_cost" class="form-control form-control-sm" placeholder="Unit Cost" step="0.01" min="0"></div>
              <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100"><i class="bi bi-plus me-1"></i>Add</button></div>
            </div>
            <?= form_close() ?>
          </div>
          <?php endif; ?>
          <div class="fm-card-body p-0">
            <?php if(empty($materials)): ?>
            <p class="text-center text-muted py-3 small">No materials recorded.</p>
            <?php else: ?>
            <table class="fm-table">
              <thead><tr><th>Item</th><th>Qty</th><th>Unit Cost</th><th>Total</th></tr></thead>
              <tbody>
              <?php foreach($materials as $m): ?>
              <tr>
                <td class="small fw-semibold"><?= esc($m['item_name']) ?></td>
                <td class="small"><?= $m['quantity'] ?></td>
                <td class="small"><?= $currency ?> <?= number_format($m['unit_cost'],2) ?></td>
                <td class="small fw-bold"><?= $currency ?> <?= number_format($m['total_cost'],2) ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="tab-photos">
        <div class="fm-card">
          <div class="fm-card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="small fw-semibold mb-2 text-muted">BEFORE</div>
                <?php if($jc['before_image']): ?>
                <img src="<?= base_url('file/job_cards/'.basename($jc['before_image'])) ?>" class="img-fluid rounded" alt="Before">
                <?php else: ?><div class="text-muted small fst-italic">No before image uploaded.</div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <div class="small fw-semibold mb-2 text-muted">AFTER</div>
                <?php if($jc['after_image']): ?>
                <img src="<?= base_url('file/job_cards/'.basename($jc['after_image'])) ?>" class="img-fluid rounded" alt="After">
                <?php else: ?><div class="text-muted small fst-italic">No after image uploaded.</div><?php endif; ?>
              </div>
            </div>
            <?php if(!in_array($jc['status'],['approved'])): ?>
            <hr>
            <?= form_open_multipart(base_url('job-cards/update/'.$jc['id'])) ?>
            <?= form_hidden('description',$jc['description']) ?>
            <?= form_hidden('labor_hours',$jc['labor_hours']) ?>
            <?= form_hidden('status',$jc['status']) ?>
            <div class="row g-2">
              <div class="col-6"><label class="form-label small">Before Image</label><input type="file" name="before_image" class="form-control form-control-sm" accept="image/*"></div>
              <div class="col-6"><label class="form-label small">After Image</label><input type="file" name="after_image" class="form-control form-control-sm" accept="image/*"></div>
            </div>
            <button type="submit" class="btn btn-fm-outline btn-sm mt-2">Upload Photos</button>
            <?= form_close() ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
