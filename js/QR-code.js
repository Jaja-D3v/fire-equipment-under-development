
const scanner = new Html5Qrcode("qr-reader");

function startScanner() {

    Html5Qrcode.getCameras()
        .then(cameras => {

            if (!cameras || cameras.length === 0) {

                document.getElementById("scan-result").innerHTML = `
                        <div class="alert alert-danger">
                            No camera found on this device.
                        </div>
                    `;

                return;
            }

            const cameraId = cameras[0].id;

            scanner.start(
                cameraId, {
                fps: 10,
                qrbox: {
                    width: 350,
                    height: 250
                }
            },

                qrCodeMessage => {

                    if (qrCodeMessage) {

                        fetch(
                            `backend/controller/ScanQRCodeController.php?action=getFeInfo&code=${encodeURIComponent(qrCodeMessage)}&branch=${encodeURIComponent(new URLSearchParams(window.location.search).get("branch") || "")}`
                        )
                            .then(response => response.json())
                            .then(result => {

                                if (!result.success) {

                                    document.getElementById("scan-result").innerHTML = `
                            <div class="alert alert-danger">
                                <div class="fw-bold">
                                    QR Code Validation
                                </div>

                                <div class="small text-body-secondary">
                                    ${result.message || "Invalid QR code. Fire extinguisher not found."}
                                </div>
                            </div>
                        `;

                                    return;
                                }

                                // Valid QR
                                const data = result.data;

                                document.getElementById("extinguisherCode").value = data.extinguisher_code;
                                document.getElementById("inspectionExtinguisherId").value = data.extinguisher_id;
                                document.getElementById("inspectionLocation").value = data.location;
                                document.getElementById("inspectionCapacity").value = data.capacity;
                                document.getElementById("inspectionType").value = data.type;
                                document.getElementById("inspectionClass").value = data.class;
                                document.getElementById("inspectionBranch").value = data.branch;

                                const inspectionModal = new bootstrap.Modal(
                                    document.getElementById("inspectionChecklistModal")
                                );

                                inspectionModal.show();

                                // scanner.stop();
                            })
                            .catch(error => {

                                console.error("Error:", error);

                                document.getElementById("scan-result").innerHTML = `
                            <div class="alert alert-danger">
                                <div class="fw-bold">
                                    QR Code Validation
                                </div>

                                <div class="small text-body-secondary">
                                    Unable to validate QR code. Please try again.
                                </div>
                            </div>
                        `;
                            });
                    }
                },

                errorMessage => {
                    // QR not detected
                }
            );

        })

        .catch(() => {

            document.getElementById("scan-result").innerHTML = `
                    <div class="alert alert-danger">
                        Camera access was denied or unavailable.
                    </div>
                `;

        });
}

startScanner();
