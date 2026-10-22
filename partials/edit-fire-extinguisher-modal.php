  <!-- this is for edit form extinguisher -->
  <?php include_once './backend/controller/DropdownController.php'; ?>

  <div
    class="modal fade"
    id="editFireExtinguisherModal"
    tabindex="-1"
    aria-labelledby="editFireExtinguisherModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

      <div class="modal-content rounded-4 border-0 shadow">

        <!-- Header -->
        <div class="modal-header px-4 py-3">

          <div>
            <h5
              class="modal-title fw-semibold mb-1"
              id="editFireExtinguisherModalLabel">
              Edit Fire Extinguisher
            </h5>

            <small class="text-body-secondary">
              Update the fire extinguisher information below.
            </small>
          </div>

          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close">
          </button>

        </div>


        <!-- Body -->
        <div class="modal-body px-4 py-4">

          <form
            id="editFireExtinguisherForm"
            class="row g-3"
            action="backend/controller/FireExtinguisherController.php"
            method="post">

            <input
              type="hidden"
              name="action"
              value="update">

            <input
              type="hidden"
              id="editExtinguisherId"
              name="extinguisher_id">

            <?php foreach (['page', 'search', 'branch', 'condition', 'placement'] as $returnParam): ?>
              <input
                type="hidden"
                name="return_<?= htmlspecialchars($returnParam, ENT_QUOTES, 'UTF-8') ?>"
                value="<?= htmlspecialchars($_GET[$returnParam] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php endforeach; ?>



            <!-- Fire Extinguisher Code -->
            <div class="col-md-6">

              <label
                for="editExtinguisherCode"
                class="form-label">
                Fire Extinguisher Code
              </label>

              <input
                type="text"
                class="form-control"
                id="editExtinguisherCode"
                name="extinguisher_code"
                value=" "
                readonly
                required>

            </div>

            <!-- Type -->
            <div class="col-md-6">

              <label
                for="editType"
                class="form-label">
                Type
              </label>

              <select
                id="editType"
                name="type"
                class="form-select"
                required>

                <option
                  value=""
                  selected
                  disabled
                  readonly>

                </option>

                <?php
                $types = getAllTypeDropdownController();

                if (!empty($types)):
                  foreach ($types as $type):
                ?>
                    <option value="<?= htmlspecialchars($type['value']) ?>">
                      <?= htmlspecialchars($type['value']) ?>
                    </option>
                <?php
                  endforeach;
                endif;
                ?>
              </select>

            </div>

            <!-- Capacity -->
            <div class="col-md-4">

              <label
                for="editCapacity"
                class="form-label">
                Capacity
              </label>

              <select
                type="text"
                class="form-select"
                id="editCapacity"
                name="capacity"
                value=""
                required>

                <?php
                $capacities = getAllCapacityDropdownController();

                if (!empty($capacities)):
                  foreach ($capacities as $capacity):
                ?>
                    <option value="<?= htmlspecialchars($capacity['value']) ?>">
                      <?= htmlspecialchars($capacity['value']) ?>
                    </option>
                <?php
                  endforeach;
                endif;
                ?>
              </select>

            </div>


            <!-- Class -->
            <div class="col-md-4">

              <label
                for="editClass"
                class="form-label">
                Fire Class
              </label>

              <select
                id="editClass"
                name="class"
                class="form-select"
                required>

                <option
                  value=" "
                  selected>

                </option>

                <option value="AB">AB</option>
                <option value="ABC">ABC</option>
                <option value="BC">BC</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>

              </select>

            </div>


            <!-- Placement -->
            <div class="col-md-4">

              <label
                for="editPlacement"
                class="form-label">
                Placement
              </label>

              <select
                type="text"
                class="form-select"
                id="editPlacement"
                name="placement"
                value=" "
                required>

                <?php
                $placements = getAllPlacementDropdownController();

                if (!empty($placements)):
                  foreach ($placements as $placement):
                ?>
                    <option value="<?= htmlspecialchars($placement['value']) ?>">
                      <?= htmlspecialchars($placement['value']) ?>
                    </option>
                <?php
                  endforeach;
                endif;
                ?>
              </select>

            </div>


            <!-- Location -->
            <div class="col-md-8">

              <label
                for="editLocation"
                class="form-label">
                Location
              </label>

              <input
                type="text"
                class="form-control"
                id="editLocation"
                name="location"
                value=" "
                required>

            </div>

            <!-- Provider -->
            <div class="col-md-4">

              <label
                for="editProvider"
                class="form-label">
                Provider
              </label>

              <input
                type="text"
                class="form-control"
                id="editProvider"
                name="provider"
                placeholder="Enter provider name"
                maxlength="255">

            </div>


            <!-- Condition -->
            <div class="col-md-4">

              <label
                for="editConditionStatus"
                class="form-label">
                Condition
              </label>

              <select
                id="editConditionStatus"
                name="condition_status"
                class="form-select"
                required>

                <option
                  value=" "
                  selected>

                </option>

                <option value="Good">Good</option>
                <option value="No Good">No Good</option>

              </select>

            </div>


            <!-- Manufactured Date -->
            <div class="col-md-6">

              <label
                for="editManufacturedDate"
                class="form-label">
                Manufactured Date
              </label>

              <input
                type="date"
                class="form-control"
                id="editManufacturedDate"
                name="manufactured_date"
                value=""
                readonly>
            </div>


            <!-- Expiration Date -->
            <div class="col-md-6">

              <label
                for="editExpirationDate"
                class="form-label">
                Expiration Date
              </label>

              <input
                type="date"
                class="form-control"
                id="editExpirationDate"
                name="expiration_date"
                value=" "
                readonly
                required>

              <small class="text-body-secondary">
                Automatically calculated as 3 years from manufactured date or refill date.
              </small>

            </div>


            <!-- Remarks -->
            <div class="col-12">

              <label
                for="editRemarks"
                class="form-label">
                Remarks
              </label>

              <textarea
                class="form-control"
                id="editRemarks"
                name="remarks"
                rows="2"
                placeholder="Additional remarks (optional)"> </textarea>

            </div>

          </form>

        </div>


        <!-- Footer -->
        <div class="modal-footer px-4 py-3">

          <button
            type="button"
            class="btn btn-light border"
            data-bs-dismiss="modal">
            Cancel
          </button>

          <button
            type="submit"
            form="editFireExtinguisherForm"
            class="btn btn-warning px-4">
            <i class="bi bi-check-lg me-1"></i>
            Update Info
          </button>

        </div>

      </div>

    </div>

  </div>

  <!-- this is for edit extinguisher end -->
