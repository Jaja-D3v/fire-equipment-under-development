   <!-- VIEW DETAILS AND EDIT MODALS -->
   <?php foreach ($chemicalSpillKits as $kit): ?>
     <?php
      $kitId = (int)$kit['chemical_spill_kit_id'];
      $kitCode = (string)$kit['chemical_spill_kit_code'];
      $kitBranch = (string)$kit['branch'];
      $kitMaterials = $kit['current_materials'] ?? [];
      ?>
     <div class="modal fade" id="viewCskModal-<?= $kitId ?>" tabindex="-1" aria-labelledby="viewCskTitle-<?= $kitId ?>" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable csk-inventory-dialog">
         <div class="modal-content csk-inventory-modal">
           <div class="modal-header csk-inventory-header">
             <div class="d-flex align-items-center gap-3">
               <div class="csk-inventory-header-icon"><i class="bi bi-clipboard-check"></i></div>
               <div>
                 <h5 class="modal-title" id="viewCskTitle-<?= $kitId ?>">Chemical Spill Kit Details</h5>
                 <p class="mb-0"><?= htmlspecialchars($kitCode, ENT_QUOTES, 'UTF-8') ?></p>
               </div>
             </div>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body csk-inventory-body">
             <section class="csk-add-section">
               <div class="csk-section-heading">
                 <div class="csk-section-icon"><i class="bi bi-info-circle"></i></div>
                 <div>
                   <h6>Basic Information</h6>
                   <p>Kit identification and location.</p>
                 </div>
               </div>
               <div class="row g-3">
                 <div class="col-12 col-md-4">
                   <div class="csk-detail-label">CSK Code</div>
                   <div class="csk-detail-value"><?= htmlspecialchars($kitCode, ENT_QUOTES, 'UTF-8') ?></div>
                 </div>
                 <div class="col-12 col-md-4">
                   <div class="csk-detail-label">Location</div>
                   <div class="csk-detail-value"><?= htmlspecialchars((string)$kit['location'], ENT_QUOTES, 'UTF-8') ?></div>
                 </div>
                 <div class="col-12 col-md-4">
                   <div class="csk-detail-label">Branch</div>
                   <div class="csk-detail-value"><?= htmlspecialchars($kitBranch, ENT_QUOTES, 'UTF-8') ?></div>
                 </div>
               </div>
             </section>

             <section class="csk-add-section">
               <div class="csk-section-heading">
                 <div class="csk-section-icon"><i class="bi bi-box-seam"></i></div>
                 <div>
                   <h6>Kit Materials</h6>
                   <p>Current stock, condition, and individual remarks.</p>
                 </div>
               </div>
               <div class="row g-3">
                 <?php foreach ($materialDefinitions as $materialDefinition): ?>
                   <?php
                    $materialName = $materialDefinition['name'];
                    $materialField = $materialDefinition['field'];
                    $materialInfo = $kitMaterials[$materialName] ?? [];
                    $materialStatus = trim((string)($materialInfo['status'] ?? ''));
                    $statusClass = match (strtolower($materialStatus)) {
                      'good' => 'bg-success-subtle text-success-emphasis',
                      'no good' => 'bg-danger-subtle text-danger-emphasis',
                      default => 'bg-secondary-subtle text-secondary-emphasis'
                    };
                    ?>
                   <div class="col-12 col-md-6">
                     <div class="csk-material-card h-100">
                       <div class="csk-material-title">
                         <span class="csk-material-icon"><i class="bi <?= htmlspecialchars($materialDefinition['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                         <span><?= htmlspecialchars($materialName, ENT_QUOTES, 'UTF-8') ?></span>
                       </div>
                       <div class="row g-2">
                         <div class="col-6">
                           <div class="csk-detail-label">Stock</div>
                           <div class="csk-detail-value"><?= htmlspecialchars((string)($kit[$materialField] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
                         </div>
                         <div class="col-6">
                           <div class="csk-detail-label">Status</div>
                           <span class="badge rounded-pill <?= $statusClass ?> px-3 py-2"><?= htmlspecialchars($materialStatus !== '' ? $materialStatus : 'Not recorded', ENT_QUOTES, 'UTF-8') ?></span>
                         </div>
                         <div class="col-12">
                           <div class="csk-detail-label">Remarks</div>
                           <div class="csk-detail-remarks"><?= htmlspecialchars(trim((string)($materialInfo['remarks'] ?? '')) !== '' ? (string)$materialInfo['remarks'] : 'No remarks entered', ENT_QUOTES, 'UTF-8') ?></div>
                         </div>
                       </div>
                     </div>
                   </div>
                 <?php endforeach; ?>
               </div>
             </section>
           </div>
           <div class="modal-footer csk-inventory-footer justify-content-between">
             <button type="button" class="btn csk-btn-archive" data-csk-archive="archiveCskForm-<?= $kitId ?>" onclick="if (confirm('Archive this kit?')) document.getElementById(this.dataset.cskArchive).submit();">
               <i class="bi bi-archive me-1"></i>Archive
             </button>
             <div class="d-flex gap-2">
               <button type="button" class="btn csk-btn-cancel" data-bs-dismiss="modal">Close</button>
               <button type="button" class="btn csk-btn-save" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editCskModal-<?= $kitId ?>">
                 <i class="bi bi-pencil-square me-1"></i>Edit
               </button>
             </div>
           </div>
         </div>
       </div>
     </div>

     <form method="POST" action="chemical-spill-kit-inventory.php" id="archiveCskForm-<?= $kitId ?>" class="d-none">
       <input type="hidden" name="csk_action" value="archive">
       <input type="hidden" name="chemical_spill_kit_id" value="<?= $kitId ?>">
       <input type="hidden" name="chemical_spill_kit_code" value="<?= htmlspecialchars($kitCode, ENT_QUOTES, 'UTF-8') ?>">
       <input type="hidden" name="branch" value="<?= htmlspecialchars($kitBranch, ENT_QUOTES, 'UTF-8') ?>">
     </form>

     <!-- edit modal  -->
     <div class="modal fade" id="editCskModal-<?= $kitId ?>" tabindex="-1" aria-labelledby="editCskTitle-<?= $kitId ?>" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable csk-inventory-dialog csk-edit-dialog">
         <div class="modal-content csk-inventory-modal">
           <div class="modal-header csk-inventory-header">
             <div class="d-flex align-items-center gap-3">
               <div class="csk-inventory-header-icon"><i class="bi bi-pencil-square"></i></div>
               <div>
                 <h5 class="modal-title" id="editCskTitle-<?= $kitId ?>">Edit Chemical Spill Kit</h5>
                 <p class="mb-0"><?= htmlspecialchars($kitCode, ENT_QUOTES, 'UTF-8') ?></p>
               </div>
             </div>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <form method="POST" action="chemical-spill-kit-inventory.php" class="csk-edit-form">
             <input type="hidden" name="csk_action" value="edit">
             <input type="hidden" name="chemical_spill_kit_id" value="<?= $kitId ?>">
             <input type="hidden" name="chemical_spill_kit_code" value="<?= htmlspecialchars($kitCode, ENT_QUOTES, 'UTF-8') ?>">
             <input type="hidden" name="branch" value="<?= htmlspecialchars($kitBranch, ENT_QUOTES, 'UTF-8') ?>">
             <div class="modal-body csk-inventory-body">
               <section class="csk-add-section">
                 <div class="csk-section-heading">
                   <div class="csk-section-icon"><i class="bi bi-clipboard-check"></i></div>
                   <div>
                     <h6>Basic Information</h6>
                     <p>Update the kit location.</p>
                   </div>
                 </div>
                 <div class="row g-3">
                   <div class="col-12 col-md-6">
                     <label class="form-label" for="editCskCode-<?= $kitId ?>">CSK Code</label>
                     <input type="text" class="form-control" id="editCskCode-<?= $kitId ?>" value="<?= htmlspecialchars($kitCode, ENT_QUOTES, 'UTF-8') ?>" readonly>
                   </div>
                   <div class="col-12 col-md-6">
                     <label class="form-label" for="editCskLocation-<?= $kitId ?>">Location <span class="text-danger">*</span></label>
                     <input type="text" class="form-control" id="editCskLocation-<?= $kitId ?>" name="location" value="<?= htmlspecialchars((string)$kit['location'], ENT_QUOTES, 'UTF-8') ?>" required>
                   </div>
                 </div>
               </section>
               <section class="csk-add-section">
                 <div class="csk-section-heading">
                   <div class="csk-section-icon"><i class="bi bi-box-seam"></i></div>
                   <div>
                     <h6>Kit Materials</h6>
                     <p>Update stock, condition, and individual remarks.</p>
                   </div>
                 </div>
                 <div class="row g-3">
                   <?php foreach ($materialDefinitions as $materialDefinition): ?>
                     <?php
                      $materialName = $materialDefinition['name'];
                      $materialField = $materialDefinition['field'];
                      $materialInfo = $kitMaterials[$materialName] ?? [];
                      $materialStatus = (string)($materialInfo['status'] ?? '');
                      ?>
                     <div class="col-12 col-lg-6">
                       <div class="csk-material-card h-100">
                         <div class="csk-material-title">
                           <span class="csk-material-icon"><i class="bi <?= htmlspecialchars($materialDefinition['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                           <span><?= htmlspecialchars($materialName, ENT_QUOTES, 'UTF-8') ?></span>
                         </div>
                         <div class="row g-2">
                           <div class="col-5">
                             <label class="form-label" for="editCsk-<?= $kitId ?>-<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>">Stock <span class="text-danger">*</span></label>
                             <input type="number" class="form-control" id="editCsk-<?= $kitId ?>-<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>" min="0" step="1" value="<?= htmlspecialchars((string)($kit[$materialField] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" required>
                           </div>
                           <div class="col-7">
                             <label class="form-label" for="editCsk-<?= $kitId ?>-<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>-status">Status</label>
                             <select class="form-select" id="editCsk-<?= $kitId ?>-<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>-status" name="<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>_status">
                               <option value="" <?= $materialStatus === '' ? 'selected' : '' ?>>Not recorded</option>
                               <option value="Good" <?= $materialStatus === 'Good' ? 'selected' : '' ?>>Good</option>
                               <option value="No Good" <?= $materialStatus === 'No Good' ? 'selected' : '' ?>>No Good</option>
                             </select>
                           </div>
                           <div class="col-12">
                             <label class="form-label" for="editCsk-<?= $kitId ?>-<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>-remarks">Remarks <span class="csk-optional">Optional</span></label>
                             <textarea class="form-control" id="editCsk-<?= $kitId ?>-<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>-remarks" name="<?= htmlspecialchars($materialField, ENT_QUOTES, 'UTF-8') ?>_remarks" rows="2"><?= htmlspecialchars((string)($materialInfo['remarks'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                           </div>
                         </div>
                       </div>
                     </div>
                   <?php endforeach; ?>
                 </div>
               </section>
             </div>
             <div class="modal-footer csk-inventory-footer">
               <button type="button" class="btn csk-btn-cancel" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn csk-btn-save"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
             </div>
           </form>
         </div>
       </div>
     </div>
   <?php endforeach; ?>