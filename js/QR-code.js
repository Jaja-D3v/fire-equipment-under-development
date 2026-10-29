const scanner = new Html5Qrcode("qr-reader");

let isProcessingQR = false;
let isScannerRunning = false;

// =====================================================
// EQUIPMENT CONFIGURATION
// =====================================================

const equipmentHandlers = {
  FE: {
    action: "getFeInfo",
    modalId: "inspectionChecklistModal",

    populate(data) {
      document.getElementById("extinguisherCode").value =
        data.extinguisher_code ?? "";

      document.getElementById("inspectionExtinguisherId").value =
        data.extinguisher_id ?? "";

      document.getElementById("inspectionLocation").value = data.location ?? "";
      document.getElementById("inspectionCapacity").value = data.capacity ?? "";
      document.getElementById("inspectionType").value = data.type ?? "";
      document.getElementById("inspectionClass").value = data.class ?? "";
      document.getElementById("inspectionBranch").value = data.branch ?? "";
    },
  },

  CSK: {
    action: "getChemicalSpillKitInfo",
    modalId: "spillKitChecklistModal",

    populate(data) {
      // Kit Code
      const code = document.getElementById("spillKitCode");
      const codeDisplay = document.getElementById("spillKitCodeDisplay");

      if (code) {
        code.value = data.chemical_spill_kit_code ?? "";
      }

      if (codeDisplay) {
        codeDisplay.textContent = data.chemical_spill_kit_code ?? "—";
      }

      // Branch
      const branch = document.getElementById("spillKitBranch");
      const branchDisplay = document.getElementById("spillKitBranchDisplay");

      if (branch) {
        branch.value = data.branch ?? "";
      }

      if (branchDisplay) {
        branchDisplay.textContent = data.branch ?? "—";
      }

      // Location
      const location = document.getElementById("spillKitLocation");
      const locationDisplay = document.getElementById(
        "spillKitLocationDisplay",
      );

      if (location) {
        location.value = data.location ?? "";
      }

      if (locationDisplay) {
        locationDisplay.textContent = data.location ?? "—";
      }

      // Inspection By
      const inspector = document.getElementById("spillKitInspector");
      const inspectorDisplay = document.getElementById(
        "spillKitInspectorDisplay",
      );

      if (inspector && inspectorDisplay) {
        inspectorDisplay.textContent = inspector.value || "—";
      }

      // Inspection Date
      const inspectionDate = document.getElementById("spillKitInspectionDate");
      const inspectionDateDisplay = document.getElementById(
        "spillKitInspectionDateDisplay",
      );

      if (inspectionDate && inspectionDateDisplay) {
        inspectionDateDisplay.textContent = inspectionDate.value || "—";
      }

      // Reset stock inputs
      const form = document.getElementById("spillKitChecklistForm");

      if (form) {
        form.querySelectorAll(".actual-stock-input").forEach((input) => {
          input.value = "";
        });
      }

      // Reset overall status
      const status = document.getElementById("spillKitStatus");

      if (status) {
        status.textContent = "Not yet checked";
        status.className =
          "badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2";
      }
    },
  },
};


// =====================================================
// IDENTIFY EQUIPMENT FROM QR CODE
// =====================================================

function getEquipmentHandler(code) {
  const normalizedCode = code.trim().toUpperCase();

  if (normalizedCode.startsWith("FE-")) {
    return equipmentHandlers.FE;
  }

  if (normalizedCode.startsWith("CSK-")) {
    return equipmentHandlers.CSK;
  }

  return null;
}

// =====================================================
// DISPLAY MESSAGE
// =====================================================

function showScanMessage(type, message) {
  const resultElement = document.getElementById("scan-result");

  if (!resultElement) return;

  const alert = document.createElement("div");
  alert.className = `alert alert-${type}`;
  alert.textContent = message;

  resultElement.replaceChildren(alert);
}

// =====================================================
// FETCH AND VALIDATE QR CODE
// =====================================================

async function handleQRCode(qrCodeMessage) {
  if (isProcessingQR) return;

  const code = qrCodeMessage.trim();

  if (!code) return;

  const handler = getEquipmentHandler(code);

  if (!handler) {
    showScanMessage(
      "warning",
      "Unrecognized QR code. Supported equipment: FE and CSK.",
    );
    return;
  }

  isProcessingQR = true;

  try {
    showScanMessage("info", "Validating QR code...");

    const params = new URLSearchParams({
      action: handler.action,
      code: code,
      branch: new URLSearchParams(window.location.search).get("branch") || "",
    });

    const response = await fetch(
      `backend/controller/ScanQRCodeController.php?${params.toString()}`,
    );

    const result = await response.json();

    if (!response.ok || !result.success) {
      showScanMessage(
        "danger",
        result.message || "Unable to validate QR code.",
      );
      return;
    }

    const data = result.data;

    // Find the correct inspection modal.
    const modalElement = document.getElementById(handler.modalId);

    if (!modalElement) {
      throw new Error(`Inspection modal not found: ${handler.modalId}`);
    }

    // Populate the corresponding equipment form.
    handler.populate(data);

    // Clear previous scan messages.
    const scanResult = document.getElementById("scan-result");

    if (scanResult) {
      scanResult.replaceChildren();
    }

    // Pause the camera while the inspection is being completed.
    await stopScanner();

    // Open the correct inspection modal.
    bootstrap.Modal.getOrCreateInstance(modalElement).show();
  } catch (error) {
    console.error("QR validation error:", error);

    showScanMessage(
      "danger",
      error.message || "Unable to validate QR code. Please try again.",
    );
  } finally {
    isProcessingQR = false;
  }
}

// =====================================================
// START CAMERA SCANNER
// =====================================================

function stopScanner() {
  if (!isScannerRunning) return Promise.resolve();

  return scanner.stop().then(() => {
    isScannerRunning = false;
  }).catch((error) => {
    console.error("Scanner stop error:", error);
  });
}

function startScanner() {
  Html5Qrcode.getCameras()
    .then((cameras) => {
      if (!cameras || cameras.length === 0) {
        showScanMessage("danger", "No camera found on this device.");
        return;
      }

      const cameraId = cameras[0].id;

      scanner
        .start(
          cameraId,
          {
            fps: 10,
            qrbox: {
              width: 350,
              height: 250,
            },
          },
          (qrCodeMessage) => {
            if (qrCodeMessage && !isProcessingQR) {
              handleQRCode(qrCodeMessage);
            }
          },
          (errorMessage) => {
            // QR not detected in this frame.
          },
        )
        .then(() => {
          isScannerRunning = true;
        })
        .catch((error) => {
          console.error("Scanner start error:", error);
          showScanMessage("danger", "Unable to start the camera scanner.");
        });
    })
    .catch((error) => {
      console.error("Camera access error:", error);
      showScanMessage("danger", "Camera access was denied or unavailable.");
    });
}

// =====================================================
// INITIALIZE
// =====================================================

["inspectionChecklistModal", "spillKitChecklistModal"].forEach((modalId) => {
  const modalElement = document.getElementById(modalId);
  if (modalElement) {
    modalElement.addEventListener("hidden.bs.modal", startScanner);
  }
});

startScanner();
