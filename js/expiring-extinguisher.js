document.addEventListener("DOMContentLoaded", function () {
  const branchFilter = document.getElementById("branchFilter");

  if (!branchFilter) {
    return;
  }

  branchFilter.addEventListener("change", function () {
    const selectedBranch = this.value;
    const url = new URL(window.location.href);

    url.searchParams.set("branch", selectedBranch);
    url.searchParams.set("page", "1");
    window.location.href = url.toString();
  });
});

const conditionFilter = document.getElementById("conditionFilter");

if (conditionFilter) {
  conditionFilter.addEventListener("change", function () {
    const selectedCondition = this.value;
    const url = new URL(window.location.href);

    if (selectedCondition === "all") {
      url.searchParams.delete("condition");
    } else {
      url.searchParams.set("condition", selectedCondition);
    }

    url.searchParams.set("page", "1");
    window.location.href = url.toString();
  });
}

const header = document.querySelector("header.header");

document.addEventListener("scroll", () => {
  if (header) {
    header.classList.toggle(
      "shadow-sm",
      document.documentElement.scrollTop > 0,
    );
  }
});

document.querySelectorAll(".edit-extinguisher-btn").forEach((button) => {
  button.addEventListener("click", function () {
    const id = this.dataset.id;

    fetch(
      `backend/controller/FireExtinguisherController.php?action=get&id=${id}`,
    )
      .then((response) => response.json())
      .then((result) => {
        if (!result.success) {
          alert("Failed to load fire extinguisher.");
          return;
        }

        const data = result.data;

        document.getElementById("editExtinguisherId").value =
          data.extinguisher_id;
        document.getElementById("editExtinguisherCode").value =
          data.extinguisher_code;
        document.getElementById("editType").value = data.type;
        document.getElementById("editCapacity").value = data.capacity;
        document.getElementById("editClass").value = data.class;
        document.getElementById("editPlacement").value = data.placement;
        document.getElementById("editLocation").value = data.location;
        document.getElementById("editConditionStatus").value =
          data.condition_status;
        document.getElementById("editManufacturedDate").value =
          data.manufactured_date;
        document.getElementById("editExpirationDate").value =
          data.expiration_date;
        document.getElementById("editRemarks").value = data.remarks ?? "";
      })
      .catch((error) => {
        console.error(error);
        alert("Something went wrong.");
      });
  });
});

const codeInput = document.getElementById("extinguisherCode");
const codeFeedback = document.getElementById("extinguisherCodeFeedback");
const addFireExtinguisherBtn = document.getElementById(
  "addFireExtinguisherBtn",
);

let codeCheckTimeout;
let isCodeDuplicate = false;

function checkFireExtinguisherCode(code) {
  clearTimeout(codeCheckTimeout);

  codeFeedback.textContent = "";
  codeFeedback.className = "small mt-1";

  isCodeDuplicate = false;
  addFireExtinguisherBtn.disabled = false;

  if (code === "") {
    codeInput.classList.remove("is-valid", "is-invalid");

    return;
  }

  codeCheckTimeout = setTimeout(() => {
    fetch(
      `backend/controller/FireExtinguisherController.php?action=checkCode&code=${encodeURIComponent(code)}`,
    )
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error: ${response.status}`);
        }

        return response.json();
      })
      .then((result) => {
        console.log("Code check result:", result);

        if (result.exists === true) {
          isCodeDuplicate = true;
          codeFeedback.textContent =
            "This fire extinguisher code already exists.";
          codeFeedback.className = "small mt-1 text-danger";
          codeInput.classList.add("is-invalid");
          codeInput.classList.remove("is-valid");
          addFireExtinguisherBtn.disabled = true;
        } else {
          isCodeDuplicate = false;

          codeFeedback.textContent = "Fire extinguisher code is available.";
          codeFeedback.className = "small mt-1 text-success";
          codeInput.classList.remove("is-invalid");
          codeInput.classList.add("is-valid");
          addFireExtinguisherBtn.disabled = false;
        }
      })
      .catch((error) => {
        console.error("Code check error:", error);

        isCodeDuplicate = false;
        codeFeedback.textContent = "Unable to check fire extinguisher code.";
        codeFeedback.className = "small mt-1 text-warning";
        codeInput.classList.remove("is-valid", "is-invalid");
        addFireExtinguisherBtn.disabled = true;
      });
  }, 400);
}

codeInput.addEventListener("input", function () {
  const code = this.value.trim();
  checkFireExtinguisherCode(code);
});

function generateFireExtinguisherCode() {
  fetch("backend/controller/FireExtinguisherController.php?action=getNextCode")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error: ${response.status}`);
      }

      return response.json();
    })
    .then((result) => {
      console.log("Generated code:", result);

      if (result.success) {
        codeInput.value = result.code;
        checkFireExtinguisherCode(result.code);
      } else {
        console.error("Failed to generate fire extinguisher code.");

        addFireExtinguisherBtn.disabled = true;
      }
    })
    .catch((error) => {
      console.error("Generate code error:", error);
      addFireExtinguisherBtn.disabled = true;
    });
}

const addFireExtinguisherModal = document.getElementById(
  "addFireExtinguisherModal",
);

if (addFireExtinguisherModal) {
  addFireExtinguisherModal.addEventListener("shown.bs.modal", function () {
    if (codeInput.value.trim() === "") {
      addFireExtinguisherBtn.disabled = true;
      generateFireExtinguisherCode();
    } else {
      checkFireExtinguisherCode(codeInput.value.trim());
    }
  });
}

const addForm = addFireExtinguisherBtn.closest("form");

if (addForm) {
  addForm.addEventListener("submit", function (event) {
    const code = codeInput.value.trim();

    if (code === "") {
      event.preventDefault();
      codeFeedback.textContent = "Fire extinguisher code is required.";
      codeFeedback.className = "small mt-1 text-danger";
      codeInput.classList.add("is-invalid");
      return;
    }

    if (isCodeDuplicate) {
      event.preventDefault();
      codeFeedback.textContent = "This fire extinguisher code already exists.";
      codeFeedback.className = "small mt-1 text-danger";
      codeInput.classList.add("is-invalid");
      addFireExtinguisherBtn.disabled = true;
      return;
    }
  });
}

const expiringExtinguisherResults = document.getElementById(
  "expiringExtinguisherResults",
);

if (expiringExtinguisherResults) {
  expiringExtinguisherResults.addEventListener("click", function (event) {
    const button = event.target.closest(".view-extinguisher-btn");

    if (!button) {
      return;
    }

    const id = button.dataset.id;

    fetch(
      `backend/controller/FireExtinguisherController.php?action=get&id=${id}`,
    )
      .then((response) => {
        if (!response.ok) {
          throw new Error("Failed to fetch fire extinguisher.");
        }

        return response.json();
      })

      .then((result) => {
        if (!result.success) {
          alert(result.message || "Failed to load fire extinguisher.");

          return;
        }

        const data = result.data;

        document.getElementById("viewExtinguisherCode").value =
          data.extinguisher_code ?? "";
        document.getElementById("viewType").value = data.type ?? "";
        document.getElementById("viewCapacity").value = data.capacity ?? "";
        document.getElementById("viewClass").value = data.class ?? "";
        document.getElementById("viewPlacement").value = data.placement ?? "";
        document.getElementById("viewLocation").value = data.location ?? "";
        document.getElementById("viewConditionStatus").value =
          data.condition_status ?? "";
        document.getElementById("viewManufacturedDate").value =
          data.manufactured_date ?? "";
        document.getElementById("viewLastRefilledDate").value =
          data.refilled_date ?? "";
        document.getElementById("viewExpirationDate").value =
          data.expiration_date ?? "";
        document.getElementById("viewRemarks").value = data.remarks ?? "";
      })

      .catch((error) => {
        console.error(error);
        alert("Something went wrong while loading fire extinguisher.");
      });
  });
}
