// Read-only browser regression runner. Requires local XAMPP, PHP and Chrome.
// Run: node tests/sms-live-regression.js --run
// Synthetic test sessions are temporary. All browser POST/PUT/PATCH/DELETE requests are blocked.
// Camera startup uses Chrome's synthetic media device and the real Html5Qrcode library.
"use strict";
const fs = require("fs");
const os = require("os");
const path = require("path");
const crypto = require("crypto");
const { spawn, spawnSync } = require("child_process");
const resultsArgument = process.argv.find((argument) =>
  argument.startsWith("--results="),
);
if (!process.argv.includes("--run")) {
  console.log(
    "Use --run to test the local application with temporary synthetic sessions.",
  );
  process.exit(0);
}
const root = path.resolve(__dirname, "..");
const base = "http://localhost/fire-equipment-under-development/";
const php = "C:/xampp/php/php.exe";
const chrome = "C:/Program Files/Google/Chrome/Application/chrome.exe";
const output = {
  sessionType:
    "Temporary synthetic sessions; credential login and database writes not tested",
  checks: [],
  exceptions: [],
  consoleErrors: [],
  httpErrors: [],
  networkFailures: [],
  blockedWrites: [],
  phpErrors: [],
};
const sessionIds = [];
let browser, socket, browserProfile;
const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
function phpRun(code) {
  const result = spawnSync(
    php,
    ["-d", "display_errors=1", "-d", "log_errors=0"],
    { input: "<?php " + code, cwd: root, encoding: "utf8" },
  );
  if (result.status !== 0)
    throw new Error("PHP fixture error: " + result.stderr + result.stdout);
  return result.stdout;
}
function createSession(role, branch = "Laguna", expired = false, employeeName = "Jared Abrera") {
  const id = "codexregression" + crypto.randomBytes(12).toString("hex");
  sessionIds.push(id);
  phpRun(
    "session_id('" +
      id +
      "'); session_start(); $_SESSION=['id'=>2147483000,'Role'=>'" +
      role +
      "','EmployeeName'=>'" + employeeName + "','Branch'=>'" +
      branch +
      "','LoginID'=>'regression-read-only','System'=>'KPFEMS','Status'=>'1','LAST_ACTIVITY'=>time()-" +
      (expired ? "1000" : "0") +
      "]; session_write_close();",
  );
  return id;
}
function record(name, passed, details) {
  output.checks.push({ name, result: passed ? "PASSED" : "FAILED", details });
  console.log(
    (passed ? "PASS: " : "FAIL: ") + name + " " + JSON.stringify(details),
  );
}
async function waitFor(predicate, timeout = 12000) {
  const start = Date.now();
  let last;
  while (Date.now() - start < timeout) {
    try {
      last = await predicate();
      if (last) return last;
    } catch (error) {
      last = error.message;
    }
    await delay(100);
  }
  throw new Error("Timed out waiting for browser condition: " + String(last));
}
let nextId = 1;
const pending = new Map();
const handlers = new Map();
function on(method, handler) {
  handlers.set(method, handler);
}
function send(method, params = {}) {
  return new Promise((resolve, reject) => {
    const id = nextId++;
    const timer = setTimeout(() => {
      pending.delete(id);
      reject(new Error("CDP timeout: " + method));
    }, 20000);
    pending.set(id, { resolve, reject, timer });
    socket.send(JSON.stringify({ id, method, params }));
  });
}
async function evaluate(expression) {
  if (typeof expression === "function")
    expression = "(" + expression.toString() + ")()";
  const r = await send("Runtime.evaluate", {
    expression,
    returnByValue: true,
    awaitPromise: true,
  });
  if (r.exceptionDetails)
    throw new Error(
      r.exceptionDetails.exception?.description || r.exceptionDetails.text,
    );
  return r.result.value;
}
async function navigate(relative) {
  await send("Page.navigate", { url: base + relative });
  await waitFor(
    () =>
      evaluate(
        "document.readyState !== 'loading' && location.href !== 'about:blank'",
      ),
    20000,
  );
  await delay(400);
  const body = await evaluate('document.body ? document.body.innerText : ""');
  if (
    /(?:Warning|Notice|Fatal error):|Uncaught mysqli_sql_exception/.test(body)
  )
    output.phpErrors.push({ page: relative, text: body.slice(0, 1200) });
  return body;
}
async function setSession(id) {
  await send("Network.clearBrowserCookies");
  if (id)
    await send("Network.setCookie", {
      name: "PHPSESSID",
      value: id,
      url: base,
      path: "/",
      httpOnly: true,
    });
}
async function request(relative) {
  return evaluate(
    "(async()=>{const response=await fetch(" +
      JSON.stringify(relative) +
      "); const text=await response.text(); let data=null;try{data=JSON.parse(text);}catch{} return {status:response.status,url:response.url,data,text:text.slice(0,300)};})()",
  );
}
async function run() {
  const baseline = JSON.parse(
    phpRun(
      "require 'backend/model/RejectedInspectionModel.php'; $out=[]; foreach(['all','Fire Extinguisher','Chemical Spill Kit'] as $type){$out[$type]=count(getRejectedInspections('Jared Abrera','Laguna','','all',$type));} $out['badge']=getRejectedInspectionCountForSidebar('Jared Abrera','Laguna'); $out['today']=count(getRejectedInspections('Jared Abrera','Laguna','','today','all')); echo json_encode($out);",
    ),
  );
  output.baseline = baseline;
  const profile = fs.mkdtempSync(
    path.join(os.tmpdir(), "sms-regression-browser-"),
  );
  browserProfile = profile;
  browser = spawn(
    chrome,
    [
      "--headless",
      "--disable-gpu",
      "--no-first-run",
      "--no-default-browser-check",
      "--disable-extensions",
      "--use-fake-device-for-media-stream",
      "--use-fake-ui-for-media-stream",
      "--remote-debugging-port=0",
      "--user-data-dir=" + profile,
      "about:blank",
    ],
    { windowsHide: true, stdio: "ignore" },
  );
  const portFile = path.join(profile, "DevToolsActivePort");
  await waitFor(() => fs.existsSync(portFile));
  const port = fs.readFileSync(portFile, "utf8").split(/\r?\n/)[0];
  const targets = await (
    await fetch("http://127.0.0.1:" + port + "/json/list")
  ).json();
  socket = new WebSocket(
    targets.find((target) => target.type === "page").webSocketDebuggerUrl,
  );
  await new Promise((resolve, reject) => {
    socket.addEventListener("open", resolve, { once: true });
    socket.addEventListener("error", reject, { once: true });
  });
  socket.addEventListener("message", (event) => {
    const message = JSON.parse(event.data);
    if (message.id) {
      const item = pending.get(message.id);
      if (item) {
        clearTimeout(item.timer);
        pending.delete(message.id);
        message.error
          ? item.reject(new Error(message.error.message))
          : item.resolve(message.result);
      }
    } else if (handlers.has(message.method)) {
      Promise.resolve(handlers.get(message.method)(message.params)).catch(
        (error) => console.log("Event error: " + error.message),
      );
    }
  });
  on("Runtime.exceptionThrown", (event) =>
    output.exceptions.push({
      page: currentPage,
      message:
        event.exceptionDetails.exception?.description ||
        event.exceptionDetails.text,
    }),
  );
  on("Runtime.consoleAPICalled", (event) => {
    if (event.type === "error")
      output.consoleErrors.push({
        page: currentPage,
        message: event.args
          .map((arg) => arg.value || arg.description)
          .join(" "),
      });
  });
  on("Network.responseReceived", (event) => {
    if (event.response.status >= 400)
      output.httpErrors.push({
        page: currentPage,
        status: event.response.status,
        url: event.response.url,
      });
  });
  on("Network.loadingFailed", (event) =>
    output.networkFailures.push({
      page: currentPage,
      error: event.errorText,
      type: event.type,
      canceled: event.canceled,
    }),
  );
  on("Fetch.requestPaused", async (event) => {
    const requestAction = new URL(event.request.url).searchParams.get("action") || "";
    const destructiveAction = ["delete", "restore", "archive", "update", "save", "approve", "reject", "evaluate_inspection"].includes(requestAction.toLowerCase());
    if (!["GET", "HEAD", "OPTIONS"].includes(event.request.method) || destructiveAction) {
      output.blockedWrites.push({
        method: event.request.method,
        url: event.request.url,
      });
      await send("Fetch.failRequest", {
        requestId: event.requestId,
        errorReason: "BlockedByClient",
      });
    } else await send("Fetch.continueRequest", { requestId: event.requestId });
  });
  await send("Runtime.enable");
  await send("Page.enable");
  await send("Network.enable");
  await send("Network.setCacheDisabled", { cacheDisabled: true });
  await send("Fetch.enable", {
    patterns: [{ urlPattern: "*", requestStage: "Request" }],
  });
  currentPage = "unauthenticated";
  await navigate("chemical-spill-kit-inventory.php");
  record(
    "Unauthenticated inventory is denied",
    (await evaluate("location.pathname")).includes("401"),
    await evaluate("location.pathname"),
  );
  record(
    "401 uses the existing favicon without a missing manifest",
    await evaluate(
      "document.querySelector('link[rel=icon]').href.endsWith('/assets/img/favicon.ico') && !document.querySelector('link[rel=manifest]')",
    ),
    "Existing project asset",
  );
  for (const assetPath of ["assets/img/favicon.ico", "css/style.css"]) {
    const response = await fetch(base + assetPath);
    const bytes = (await response.arrayBuffer()).byteLength;
    record(
      "Shared asset HTTP " + assetPath,
      response.status === 200 && bytes > 0,
      {
        status: response.status,
        type: response.headers.get("content-type"),
        bytes,
      },
    );
  }
  await navigate("authentication/login.php");
  record(
    "Credential login page renders",
    await evaluate('!!document.querySelector("input[type=password]")'),
    "GET only; no credentials submitted",
  );
  const admin = createSession("Admin"),
    inspector = createSession("Inspector"),
    cebu = createSession("Inspector", "Cebu"),
    expired = createSession("Inspector", "Laguna", true);
  await setSession(admin);
  currentPage = "admin inventory";
  await navigate("chemical-spill-kit-inventory.php");
  await waitFor(() => evaluate('!!document.querySelector(".csk-edit-form")'));
  const inventory = await evaluate(function () {
    return {
      edits: document.querySelectorAll(".csk-edit-form").length,
      materials: document.querySelectorAll(
        "[data-csk-edit-materials] [data-csk-material]",
      ).length,
      unknown: Array.from(
        document.querySelectorAll("[data-csk-edit-materials] [data-csk-stock]"),
      ).filter((input) => input.value === "").length,
      role: document.querySelector(".ims-role-chip")?.textContent.trim(),
      rejectedInspectorLink: !!document.querySelector(
        'a[href="inspector-rejected-inspection.php"]',
      ),
    };
  });
  record(
    "Admin inventory and companion edit quantities render",
    inventory.edits > 0 &&
      inventory.materials >= inventory.edits * 8 &&
      inventory.unknown === 0 &&
      inventory.role === "Admin" &&
      !inventory.rejectedInspectorLink,
    inventory,
  );
  const code = await evaluate(
    'document.querySelector(".csk-edit-form input[name=chemical_spill_kit_code]").value',
  );
  const scan = await request(
    "backend/controller/ScanQRCodeController.php?action=getChemicalSpillKitInfo&branch=Laguna&code=" +
      encodeURIComponent(code),
  );
  record(
    "Actual CSK lookup returns companion materials",
    scan.status === 200 &&
      scan.data?.success &&
      Object.keys(scan.data.data.current_materials).length >= 8 &&
      !("pads" in scan.data.data),
    {
      status: scan.status,
      materials:
        scan.data?.data && Object.keys(scan.data.data.current_materials).length,
    },
  );
  await evaluate(
    'document.querySelector("button[data-bs-target^=\\"#viewCskModal-\\"]").click()',
  );
  await waitFor(
    () => evaluate('!!document.querySelector("[id^=viewCskModal-].show")'),
    6000,
  );
  record(
    "Details modal opens",
    true,
    await evaluate(
      'document.querySelector("[id^=viewCskModal-].show .csk-detail-value")?.textContent',
    ),
  );
  await evaluate(
    'document.querySelector("[id^=viewCskModal-].show button[data-bs-target^=\\"#editCskModal-\\"]").click()',
  );
  await waitFor(
    () => evaluate('!!document.querySelector("[id^=editCskModal-].show")'),
    6000,
  );
  const validation = await evaluate(function () {
    const form = document.querySelector("[id^=editCskModal-].show form");
    const input = form.querySelector("[data-csk-stock]");
    const original = input.value;
    const results = [];
    for (const value of ["-1", "1.5", "2147483648", ""]) {
      input.value = value;
      input.dispatchEvent(new Event("input", { bubbles: true }));
      const event = new Event("submit", { cancelable: true });
      form.dispatchEvent(event);
      results.push(!input.checkValidity() && event.defaultPrevented);
    }
    input.value = original;
    input.dispatchEvent(new Event("input", { bubbles: true }));
    return results;
  });
  record(
    "Actual edit form rejects invalid/empty quantities without submitting",
    validation.every(Boolean),
    validation,
  );
  await evaluate(
    'bootstrap.Modal.getInstance(document.querySelector("[id^=editCskModal-].show")).hide()',
  );
  await delay(400);
  await evaluate(
    'document.querySelector("button[data-bs-target=\\"#addChemicalSpillKitModal\\"]").click()',
  );
  await waitFor(
    () =>
      evaluate(
        'document.getElementById("addChemicalSpillKitForm").dataset.cskMaterialsReady === "true"',
      ),
    8000,
  );
  record(
    "Actual add form fetches configured materials/code",
    await evaluate(
      'document.querySelectorAll("#addCskMaterials [data-csk-material]").length>=8 && /^CSK-\\d+$/.test(document.getElementById("addCskCode").value)',
    ),
    await evaluate(
      '({materials:document.querySelectorAll("#addCskMaterials [data-csk-material]").length,code:document.getElementById("addCskCode").value})',
    ),
  );
  await evaluate(
    'document.getElementById("addCskLocation").value="READ ONLY REGRESSION - UNSAVED";document.getElementById("addCskLocation").dispatchEvent(new Event("input",{bubbles:true}))',
  );
  record(
    "Valid add form becomes saveable without a save",
    await evaluate(
      '!document.getElementById("addChemicalSpillKitSubmit").disabled',
    ),
    "No submit performed",
  );
  await evaluate(
    'document.getElementById("addCskBranch").value="Cebu";document.getElementById("addCskBranch").dispatchEvent(new Event("change",{bubbles:true}))',
  );
  await waitFor(
    () =>
      evaluate(
        'document.getElementById("addCskMaterials").textContent.includes("No materials")',
      ),
    8000,
  );
  record(
    "Empty configured branch blocks saving",
    await evaluate(
      'document.getElementById("addChemicalSpillKitSubmit").disabled',
    ),
    await evaluate('document.getElementById("addCskMaterials").textContent'),
  );
  currentPage = "admin report";
  const pdf = await evaluate(
    '(async()=>{const r=await fetch("backend/controller/GenerateChemicalSpillKitInventoryReportController.php?branch=Laguna");const bytes=new Uint8Array(await r.arrayBuffer());return {status:r.status,type:r.headers.get("content-type"),bytes:bytes.length,magic:String.fromCharCode(...bytes.slice(0,5))};})()',
  );
  record(
    "Actual HTTP PDF report",
    pdf.status === 200 && pdf.magic === "%PDF-",
    pdf,
  );
  currentPage = "admin pending";
  await navigate("admin-pending-inspections.php");
  record(
    "Pending page renders without database mutations",
    !(await evaluate("location.pathname")).includes("401") &&
      !output.phpErrors.some(
        (error) => error.page === "admin-pending-inspections.php",
      ),
    await evaluate("document.title"),
  );
  currentPage = "admin denied inspector page";
  await navigate("inspector-rejected-inspection.php");
  record(
    "Admin cannot open inspector-only rejected page",
    (await evaluate("location.pathname")).includes("401"),
    await evaluate("location.pathname"),
  );
  await setSession(inspector);
  currentPage = "inspector rejected";
  let text = await navigate("inspector-rejected-inspection.php");
  const counts = await evaluate(function () {
    return {
      main: document.querySelector(".card-header .badge")?.textContent.trim(),
      sidebar:
        document
          .querySelector(
            'a[href="inspector-rejected-inspection.php"] [aria-label]',
          )
          ?.getAttribute("aria-label") || null,
    };
  });
  record(
    "Actual rejected listing and sidebar agree",
    parseInt(counts.main) === baseline.all &&
      (baseline.badge === 0
        ? counts.sidebar === null
        : parseInt(counts.sidebar) === baseline.badge),
    { expected: baseline, actual: counts },
  );
  for (const [query, expected] of [
    ["equipment=Chemical%20Spill%20Kit", baseline["Chemical Spill Kit"]],
    ["equipment=Fire%20Extinguisher", baseline["Fire Extinguisher"]],
    ["date=today", baseline.today],
    ["search=REGRESSION_NO_MATCH_237491", 0],
    ["page=999", baseline.all],
  ]) {
    currentPage = "rejected " + query;
    await navigate("inspector-rejected-inspection.php?" + query);
    const value = await evaluate(
      'document.querySelector(".card-header .badge")?.textContent.trim()',
    );
    record("Rejected filter " + query, parseInt(value) === expected, {
      expected,
      actual: value,
    });
  }
  currentPage = "inspector history";
  await navigate("my-inspections.php");
  record(
    "Inspector history page renders",
    !(await evaluate("location.pathname")).includes("401") &&
      !output.phpErrors.some((error) => error.page === "my-inspections.php"),
    await evaluate("document.title"),
  );
  currentPage = "inspector scan";
  await navigate("scan-QR-Code.php");
  record(
    "CSK inspection form present",
    await evaluate('!!document.getElementById("spillKitChecklistForm")'),
    "No scanner/camera permission requested",
  );
  await evaluate(
    'document.getElementById("spillKitCode").value=' +
      JSON.stringify(code) +
      ';document.getElementById("spillKitBranch").value="Laguna";bootstrap.Modal.getOrCreateInstance(document.getElementById("spillKitChecklistModal")).show()',
  );
  await waitFor(
    () =>
      evaluate(
        'document.getElementById("spillKitChecklistForm").dataset.cskMaterialsReady === "true"',
      ),
    8000,
  );
  record(
    "Actual inspection checklist fetches materials and requires quantities",
    await evaluate(
      'document.querySelectorAll("#spillKitItems [data-csk-material]").length>=8 && document.getElementById("spillKitChecklistSaveButton").disabled',
    ),
    "Checklist opened; no submit",
  );
  const denied = await request(
    "backend/controller/ChemicalSpillKitRequiredStockController.php?action=getRequiredStock&branch=Cebu",
  );
  record(
    "Inspector cannot fetch other branch configuration",
    denied.status === 403,
    { status: denied.status },
  );
  await setSession(cebu);
  currentPage = "Cebu inspector";
  await navigate("chemical-spill-kit-inventory.php?branch=Laguna");
  record(
    "Inspector cannot select another branch via URL",
    await evaluate('document.querySelectorAll(".csk-edit-form").length===0'),
    "Cebu session requesting Laguna",
  );
  const wrong = await request(
    "backend/controller/ScanQRCodeController.php?action=getChemicalSpillKitInfo&code=" +
      encodeURIComponent(code) +
      "&branch=Laguna",
  );
  record(
    "Inspector QR lookup uses assigned branch",
    wrong.status === 200 && wrong.data?.success === false,
    { status: wrong.status, success: wrong.data?.success },
  );

  // These are read-only browser interactions. Persisting any form remains blocked.
  await setSession(admin);
  currentPage = "inventory search";
  await navigate(
    "chemical-spill-kit-inventory.php?search=" + encodeURIComponent(code),
  );
  const searchResult = await evaluate(function () {
    return Array.from(
      document.querySelectorAll(
        ".csk-edit-form input[name=chemical_spill_kit_code]",
      ),
    ).map((input) => input.value);
  });
  record(
    "Inventory search keeps the matching kit",
    searchResult.length === 1 && searchResult[0] === code,
    searchResult,
  );
  currentPage = "inventory empty search";
  await navigate(
    "chemical-spill-kit-inventory.php?search=REGRESSION_NO_MATCH_237491",
  );
  record(
    "Inventory empty search is safe",
    await evaluate("document.querySelectorAll('.csk-edit-form').length === 0"),
    "No matching inventory",
  );
  currentPage = "inventory page clamp";
  await navigate("chemical-spill-kit-inventory.php?page=999");
  record(
    "Inventory out-of-range page clamps to existing rows",
    await evaluate("document.querySelectorAll('.csk-edit-form').length > 0"),
    "Existing kits; multi-page acceptance needs additional authorized records",
  );

  for (const viewport of [
    { width: 1440, height: 1000 },
    { width: 768, height: 1024 },
    { width: 390, height: 844 },
  ]) {
    await send("Emulation.setDeviceMetricsOverride", {
      ...viewport,
      deviceScaleFactor: 1,
      mobile: false,
    });
    await delay(100);
    const layout = await evaluate(function () {
      return {
        viewport: innerWidth,
        document: document.documentElement.scrollWidth,
        client: document.documentElement.clientWidth,
      };
    });
    record(
      "Inventory viewport " + viewport.width,
      layout.document <= layout.client + 1,
      layout,
    );
  }
  await send("Emulation.clearDeviceMetricsOverride");
  const listeners = await send("Runtime.evaluate", {
    expression: "getEventListeners(document.querySelector('.csk-edit-form'))",
    includeCommandLineAPI: true,
    returnByValue: true,
  });
  const eventTypes = Object.fromEntries(
    Object.entries(listeners.result.value || {}).map(([type, entries]) => [
      type,
      entries.length,
    ]),
  );
  record(
    "Shared edit form handlers attach once",
    eventTypes.input === 1 && eventTypes.submit === 1,
    eventTypes,
  );
  const duplicateIds = await evaluate(function () {
    const seen = new Set();
    return Array.from(document.querySelectorAll("[id]"))
      .map((element) => element.id)
      .filter((id) => {
        if (seen.has(id)) return true;
        seen.add(id);
        return false;
      });
  });
  record(
    "CSK inventory has no duplicate IDs",
    duplicateIds.length === 0,
    duplicateIds,
  );

  currentPage = "inventory failed material fetch simulation";
  const failureState = await evaluate(async function () {
    const form = document.getElementById("addChemicalSpillKitForm");
    const container = document.getElementById("addCskMaterials");
    const originalFetch = window.fetch;
    try {
      window.fetch = async function () {
        throw new Error("Regression simulated network failure");
      };
      await window.cskMaterialForms.load(
        form,
        container,
        "Laguna",
        "failure",
        false,
      );
      return {
        message: container.textContent,
        disabled: document.getElementById("addChemicalSpillKitSubmit").disabled,
      };
    } finally {
      window.fetch = originalFetch;
    }
  });
  record(
    "Live form safely handles a mocked material request failure",
    failureState.disabled &&
      failureState.message === "Regression simulated network failure",
    failureState,
  );

  currentPage = "admin other inventory navigation";
  await navigate("list-extinguisher.php");
  record(
    "Existing Fire Extinguisher inventory page renders",
    (await evaluate("!!document.querySelector('body.fe-inventory-page h1')")) &&
      !output.phpErrors.some((error) => error.page === "list-extinguisher.php"),
    "Read-only navigation smoke check",
  );
  currentPage = "admin dashboard navigation";
  await navigate("dashboard.php");
  record(
    "Dashboard navigation renders",
    await evaluate("!!document.querySelector('main')"),
    "Read-only smoke check, not a full dashboard regression",
  );

  await setSession(inspector);
  currentPage = "inspector report restriction";
  const deniedPdf = await request(
    "backend/controller/GenerateChemicalSpillKitInventoryReportController.php?branch=Laguna",
  );
  record("Inspector cannot generate Admin-only PDF", deniedPdf.status === 403, {
    status: deniedPdf.status,
  });
  currentPage = "hardware keyboard-wedge CSK path";
  await navigate("scan-QR-Code.php");
  record(
    "Manual scanner skips camera initialization",
    await evaluate(
      'scanner === null && !document.getElementById("qr-reader") && !document.querySelector("video")',
    ),
    "Badge helpers and Enter handler remain available",
  );
  await evaluate(
    'document.getElementById("qrCodeInput").value=' +
      JSON.stringify(code) +
      ';document.getElementById("qrCodeInput").dispatchEvent(new Event("input",{bubbles:true}));' +
      'document.getElementById("qrCodeInput").dispatchEvent(new KeyboardEvent("keydown",{key:"Enter",bubbles:true,cancelable:true}));',
  );
  await waitFor(
    () =>
      evaluate(
        'document.getElementById("spillKitChecklistModal").classList.contains("show") && document.getElementById("spillKitChecklistForm").dataset.cskMaterialsReady === "true"',
      ),
    8000,
  );
  record(
    "CSK keyboard Enter lookup opens actual inspection checklist",
    await evaluate(
      'document.getElementById("spillKitCode").value === ' +
        JSON.stringify(code) +
        ' && document.querySelectorAll("#spillKitItems [data-csk-material]").length >= 8',
    ),
    "Synthetic keyboard/input events on actual page and backend; physical scanner not tested",
  );
  await evaluate(function () {
    document
      .querySelectorAll("#spillKitItems [data-csk-stock]")
      .forEach((input) => {
        input.value = "0";
        input.dispatchEvent(new Event("input", { bubbles: true }));
      });
  });
  record(
    "Actual inspection form accepts zero quantities without saving",
    await evaluate(
      '!document.getElementById("spillKitChecklistSaveButton").disabled',
    ),
    "Unsaved UI values only; no submission",
  );
  for (const date of ["yesterday", "7", "30"]) {
    currentPage = "rejected date " + date;
    await navigate("inspector-rejected-inspection.php?date=" + date);
    record(
      "Rejected date filter " + date,
      await evaluate("!!document.querySelector('.card-header .badge')"),
      "Read-only page/filter check; date-boundary SQL cases are tested separately",
    );
  }

  currentPage = "hardware keyboard-wedge FE path";
  const fireCode = phpRun(
    "require 'backend/config/db.php'; $row=mysqli_fetch_assoc(mysqli_query($conn, \"SELECT extinguisher_code FROM fire_extinguishers_tbl WHERE archived=0 AND branch='Laguna' ORDER BY extinguisher_id LIMIT 1\")); echo $row['extinguisher_code'];",
  ).trim();
  await navigate("scan-QR-Code.php");
  await evaluate(
    'document.getElementById("qrCodeInput").value=' +
      JSON.stringify(fireCode) +
      ';document.getElementById("qrCodeInput").dispatchEvent(new Event("input",{bubbles:true}));' +
      'document.getElementById("qrCodeInput").dispatchEvent(new KeyboardEvent("keydown",{key:"Enter",bubbles:true,cancelable:true}));',
  );
  await waitFor(
    () =>
      evaluate(
        'document.getElementById("inspectionChecklistModal").classList.contains("show")',
      ),
    8000,
  );
  record(
    "FE keyboard Enter lookup opens actual inspection checklist",
    await evaluate(
      'document.getElementById("extinguisherCode").value === ' +
        JSON.stringify(fireCode) +
        ' && document.getElementById("inspectionBranch").value === "Laguna"',
    ),
    "Actual FE endpoint/modal; no inspection submitted",
  );

  currentPage = "hardware input debounce";
  await navigate("scan-QR-Code.php");
  await evaluate(function () {
    window.regressionLookupStarted = performance.now();
    document.getElementById("spillKitChecklistModal").addEventListener(
      "show.bs.modal",
      function () {
        window.regressionLookupElapsed =
          performance.now() - window.regressionLookupStarted;
      },
      { once: true },
    );
  });
  await evaluate(
    'document.getElementById("qrCodeInput").value=' +
      JSON.stringify(code) +
      ';window.regressionLookupStarted=performance.now();document.getElementById("qrCodeInput").dispatchEvent(new Event("input",{bubbles:true}));',
  );
  await waitFor(
    () =>
      evaluate(
        'document.getElementById("spillKitChecklistModal").classList.contains("show")',
      ),
    8000,
  );
  const debounceElapsed = await evaluate("window.regressionLookupElapsed");
  record("Input-only lookup retains 300 ms debounce", debounceElapsed >= 290, {
    millisecondsToModal: debounceElapsed,
    note: "Includes backend response time; no Enter event sent",
  });

  // The real page and Html5Qrcode use Chrome's synthetic media device, without API mocks.
  currentPage = "camera scanner initialization";
  await navigate("QR-code.php");
  await waitFor(
    () =>
      evaluate(function () {
        const video = document.querySelector("#qr-reader video");
        return (
          isScannerRunning &&
          video &&
          video.readyState >= 2 &&
          video.videoWidth > 0 &&
          !video.paused
        );
      }),
    15000,
  );
  const cameraState = await evaluate(function () {
    const video = document.querySelector("#qr-reader video");
    return {
      reader: !!document.getElementById("qr-reader"),
      running: isScannerRunning,
      videoWidth: video.videoWidth,
      videoHeight: video.videoHeight,
    };
  });
  record(
    "Real camera page initializes Html5Qrcode with synthetic media",
    cameraState.reader && cameraState.running,
    cameraState,
  );
  await evaluate("stopScanner()");
  record(
    "Actual camera stop completes",
    await evaluate("isScannerRunning === false"),
    "No camera API was replaced",
  );
  await evaluate("startScanner()");
  await waitFor(() => evaluate("isScannerRunning === true"), 15000);
  record(
    "Actual camera restarts after stopping",
    await evaluate('!!document.querySelector("#qr-reader video")'),
    "Synthetic media device; physical camera not tested",
  );
  await evaluate("stopScanner()");


 // Reuse the existing helpers for live, read-only Super Admin permission checks.
 const permissionData = JSON.parse(phpRun(
   "$_SESSION=['Role'=>'Super Admin','Branch'=>'','EmployeeName'=>'Permission Access QA']; " +
   "require 'backend/config/db.php'; require 'backend/model/RejectedInspectionModel.php'; " +
   "mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_ONLY); $out=[]; " +
   "$rows=mysqli_fetch_all(mysqli_query($conn,\"SELECT extinguisher_id,extinguisher_code,branch FROM fire_extinguishers_tbl WHERE archived=0 ORDER BY extinguisher_id\"),MYSQLI_ASSOC); " +
   "$out['fe_total']=count($rows); $out['fe_first']=$rows[0]; $out['fe_branches']=array_values(array_unique(array_column($rows,'branch'))); " +
   "foreach($rows as $row){if($row['branch']!==$rows[0]['branch']){$out['fe_other']=$row;break;}} " +
   "$duplicate=mysqli_fetch_assoc(mysqli_query($conn,\"SELECT extinguisher_code FROM fire_extinguishers_tbl WHERE archived=0 GROUP BY extinguisher_code HAVING COUNT(DISTINCT branch)>1 LIMIT 1\")); $out['duplicate_code']=$duplicate['extinguisher_code']??null; " +
   "$out['csk']=mysqli_fetch_assoc(mysqli_query($conn,\"SELECT chemical_spill_kit_id,chemical_spill_kit_code,branch FROM chemical_spill_kit_materials_inventory_tbl WHERE archived=0 ORDER BY chemical_spill_kit_id LIMIT 1\")); " +
   "$out['approved_ids']=array_column(mysqli_fetch_all(mysqli_query($conn,\"SELECT inspect_id FROM inspection_checklist_tbl WHERE LOWER(evaluation_status)='approved' ORDER BY inspect_id DESC LIMIT 2\"),MYSQLI_ASSOC),'inspect_id'); " +
   "$out['csk_inspection']=mysqli_fetch_assoc(mysqli_query($conn,\"SELECT inspection_id,branch FROM chemical_spill_kit_inspection_tbl ORDER BY inspection_id DESC LIMIT 1\")); " +
   "$out['logs']=(int)mysqli_fetch_assoc(mysqli_query($conn,'SELECT COUNT(*) AS total FROM activity_logs_tbl'))['total']; " +
   "$out['rejected']=count(getRejectedInspections('Permission Access QA','')); " +
   "$out['rejected_badge']=getRejectedInspectionCountForSidebar('Permission Access QA',''); " +
   "mysqli_rollback($conn); echo json_encode($out);"
 ));
 output.permissionData = permissionData;
 const pageChecks = [
   ['dashboard.php', '#dashboardBranch'],
   ['list-extinguisher.php', '#branchFilter'],
   ['archived-extinguisher.php', '#branchFilter'],
   ['expiring-extinguisher.php', '#branchFilter'],
   ['chemical-spill-kit-inventory.php', '#branchFilter'],
   ['admin-pending-inspections.php', '#branchFilter'],
   ['inspection-pending.php', '#branchFilter'],
   ['inspection-approved.php', '#branchFilter'],
   ['inspection-rejected.php', '#branchFilter'],
   ['my-inspections.php', '.card-header'],
   ['inspector-rejected-inspection.php', '.card-header'],
   ['QR-generator.php', '#branchFilter'],
   ['QR-generator.php?equipment=chemical_spill_kit', '#branchFilter'],
   ['scan-QR-Code.php', '#qrCodeInput'],
   ['QR-code.php', '#qr-reader'],
   ['fire-extinguisher-history.php', '#branchFilter'],
   ['activity-log.php', 'body'],
   ['maintenance.php', '#required-stock-branch'],
   ['authentication/system-selector.php', 'body'],
   ['super-admin-monitoring.php', 'body']
 ];
 for (const assignedBranch of ['Laguna', '']) {
   const session = createSession('Super Admin', assignedBranch, false, 'Permission Access QA');
   await setSession(session);
   const label = assignedBranch || 'unassigned';
   for (const [page, selector] of pageChecks) {
     currentPage = 'Super Admin ' + label + ': ' + page;
     await navigate(page);
     const state = await evaluate('({path:location.pathname,found:!!document.querySelector(' + JSON.stringify(selector) +
       '),branch:document.querySelector("#branchFilter,#dashboardBranch")?.value||null})');
     const file = page.split('?')[0];
     const hasPageError = output.phpErrors.some(error => error.page === page);
     record('Super Admin ' + label + ' opens ' + page,
       state.path.endsWith('/' + file) && state.found && !hasPageError, state);
     if (file === 'QR-code.php') {
       await waitFor(() => evaluate('isScannerRunning === true'), 15000);
       record('Super Admin ' + label + ' camera page starts the existing scanner', await evaluate('!!document.querySelector("#qr-reader video")'), 'Real library with synthetic Chrome media');
       await evaluate('stopScanner()');
     }
   }

   currentPage = 'Super Admin ' + label + ': navigation and default scope';
   await navigate('chemical-spill-kit-inventory.php');
   const navigation = await evaluate('({links:Array.from(document.querySelectorAll("#sidebar a[href]")).map(link=>link.getAttribute("href")),branch:document.getElementById("branchFilter").value,report:!!document.getElementById("generateCskInventoryReportBtn")})');
   record('Super Admin ' + label + ' sees existing module links and report action',
     ['QR-generator.php','my-inspections.php','inspector-rejected-inspection.php','admin-pending-inspections.php','maintenance.php','super-admin-monitoring.php']
       .every(link => navigation.links.includes(link)) && navigation.branch === 'all' && navigation.report, navigation);
   const summary = await request('backend/controller/DashboardDataController.php?period=this_month');
   record('Super Admin ' + label + ' dashboard defaults to all branches',
     summary.status === 200 && summary.data?.success && summary.data.data.filters.branch === 'all' &&
     summary.data.data.filters.can_select_branch &&
     summary.data.data.equipment.find(item=>item.id==='fire_extinguisher')?.active === permissionData.fe_total,
     {status:summary.status,filters:summary.data?.data?.filters,totals:summary.data?.data?.totals});
   const filtered = await request('backend/controller/DashboardDataController.php?period=last_30&branch=' + encodeURIComponent(permissionData.fe_other.branch));
   record('Super Admin ' + label + ' dashboard honors a concrete branch filter',
     filtered.status === 200 && filtered.data?.data?.filters.branch === permissionData.fe_other.branch,
     {status:filtered.status,branch:filtered.data?.data?.filters.branch});
   const detail = await request('backend/controller/FireExtinguisherController.php?action=get&id=' + permissionData.fe_other.extinguisher_id);
   record('Super Admin ' + label + ' retrieves inventory from another branch',
     detail.status === 200 && detail.data?.success && detail.data.data.branch === permissionData.fe_other.branch,
     {status:detail.status,branch:detail.data?.data?.branch});
   const branches = await request('backend/controller/FireExtinguisherController.php?action=getBranches');
   record('Super Admin ' + label + ' can retrieve branch options', branches.status === 200 && branches.data?.success,
     {status:branches.status,data:branches.data});
   const stock = await request('backend/controller/ChemicalSpillKitRequiredStockController.php?action=getRequiredStock&branch=' + encodeURIComponent(permissionData.csk.branch));
   record('Super Admin ' + label + ' can retrieve selected-branch material configuration',
     stock.status === 200 && stock.data?.success && stock.data.materials.length > 0, {status:stock.status,branch:stock.data?.branch});
   const required = await request('backend/controller/RequiredStockManagementController.php?action=list&equipment=chemical_spill_kit&branch=' + encodeURIComponent(permissionData.csk.branch));
   record('Super Admin ' + label + ' can read Required Stock management',
     required.status === 200 && required.data?.success, {status:required.status,branch:required.data?.branch});
   const kit = await request('backend/controller/ScanQRCodeController.php?action=getChemicalSpillKitInfo&code=' + encodeURIComponent(permissionData.csk.chemical_spill_kit_code));
   record('Super Admin ' + label + ' can find a unique CSK without an assigned branch',
     kit.status === 200 && kit.data?.success && kit.data.data.branch === permissionData.csk.branch,
     {status:kit.status,branch:kit.data?.data?.branch});
   const scanFe = await request('backend/controller/ScanQRCodeController.php?action=getFeInfo&code=' + encodeURIComponent(permissionData.fe_other.extinguisher_code) +
     '&branch=' + encodeURIComponent(permissionData.fe_other.branch));
   record('Super Admin ' + label + ' can scan an explicitly selected foreign-branch FE',
     scanFe.status === 200 && scanFe.data?.success && scanFe.data.data.branch === permissionData.fe_other.branch,
     {status:scanFe.status,branch:scanFe.data?.data?.branch});
   if (permissionData.duplicate_code) {
     const ambiguous = await request('backend/controller/ScanQRCodeController.php?action=getFeInfo&code=' + encodeURIComponent(permissionData.duplicate_code));
     record('Super Admin ' + label + ' retains QR branch ambiguity protection',
       ambiguous.status === 200 && ambiguous.data?.success === false && /more than one branch/i.test(ambiguous.data.message),
       {status:ambiguous.status,message:ambiguous.data?.message});
   }
   const logs = await request('backend/controller/ActivityLogController.php?action=getAll');
   record('Super Admin ' + label + ' sees other employees activity',
     logs.status === 200 && logs.data?.success && logs.data.data.length === Math.min(permissionData.logs,10) &&
     logs.data.data.some(row=>row.user_name !== 'Permission Access QA'), {status:logs.status,rows:logs.data?.data?.length});
   const evaluation = await request('backend/controller/ChemicalSpillKitInspectionController.php?action=getEvaluationDetails&id=' + permissionData.csk_inspection.inspection_id);
   record('Super Admin ' + label + ' can read existing CSK evaluation details',
     evaluation.status === 200 && evaluation.data?.success, {status:evaluation.status,branch:evaluation.data?.inspection?.branch});

   currentPage = 'Super Admin ' + label + ': rejected list and badge';
   await navigate('inspector-rejected-inspection.php');
   const rejected = await evaluate('({listing:parseInt(document.querySelector(".card-header .badge")?.textContent),badge:parseInt(document.querySelector("a[href=\\"inspector-rejected-inspection.php\\"] [aria-label]")?.getAttribute("aria-label")||"0")})');
   record('Super Admin ' + label + ' rejected records and badge are independent of employee assignment',
     rejected.listing === permissionData.rejected && rejected.badge === permissionData.rejected_badge, rejected);
   await navigate('inspector-rejected-inspection.php?search=REGRESSION_NO_MATCH_237491&date=30&equipment=Chemical%20Spill%20Kit');
   record('Super Admin ' + label + ' rejected filters retain the unfiltered sidebar count',
     await evaluate('parseInt(document.querySelector(".card-header .badge").textContent) === 0 && parseInt(document.querySelector("a[href=\\"inspector-rejected-inspection.php\\"] [aria-label]")?.getAttribute("aria-label")||"0") === ' + permissionData.rejected_badge),
     'No-match search/date/equipment filters; badge remains unfiltered');
   await navigate('my-inspections.php');
   const historyCount = await evaluate('parseInt(document.querySelector(".card-header .badge")?.textContent)');
   record('Super Admin ' + label + ' can view inspections submitted by other employees', historyCount > 0, {records:historyCount});


   currentPage = 'Super Admin ' + label + ': foreign inventory action visibility';
   await navigate('list-extinguisher.php?branch=' + encodeURIComponent(permissionData.fe_other.branch));
   await evaluate('document.querySelector(".view-extinguisher-btn").click()');
   await waitFor(() => evaluate('document.getElementById("viewEditFireExtinguisherBtn").closest(".modal").classList.contains("show") && document.getElementById("viewExtinguisherCode").value !== ""'), 8000);
   record('Super Admin ' + label + ' sees existing foreign-record Edit and Archive actions',
     await evaluate('!document.getElementById("viewEditFireExtinguisherBtn").hidden && !!document.getElementById("viewDeleteFireExtinguisherBtn")'),
     'Detail modal opened through real GET; no write action clicked');
   await navigate('archived-extinguisher.php');
   record('Super Admin ' + label + ' has the existing Restore control',
     await evaluate('!!document.getElementById("restoreDeletedFireExtinguisherBtn")'),
     'Rendered control only; no archived records/actions changed');
   for (const status of ['pending','approved','rejected']) {
     currentPage = 'Super Admin ' + label + ': unified ' + status + ' filter';
     await navigate('admin-pending-inspections.php?branch=all&status=' + status);
     const statuses = await evaluate('Array.from(document.querySelectorAll("#inspectionTableBody tr")).map(row=>row.dataset.status.toLowerCase())');
     record('Super Admin ' + label + ' unified ' + status + ' filter shows matching records',
       statuses.length > 0 && statuses.every(value=>value===status), {statuses});
   }
   currentPage = 'Super Admin ' + label + ': inspection search/date filters';
   await navigate('admin-pending-inspections.php?branch=all&search=REGRESSION_NO_MATCH_237491&date_from=2026-01-01&date_to=2026-12-31');
   record('Super Admin ' + label + ' inspection search/date filters remain active',
     await evaluate('document.querySelectorAll("#inspectionTableBody tr").length === 0 && document.getElementById("branchFilter").value === "all"'),
     'No-match query with valid date range and all-branch scope');

   const reportChecks = [
     ['CSK inventory PDF', 'backend/controller/GenerateChemicalSpillKitInventoryReportController.php', '%PDF-'],
     ['FE inventory PDF', 'backend/controller/GenerateInventoryReportController.php', '%PDF-'],
     ['Expiration PDF', 'helpers/generate-expiring-extinguisher-report.php', '%PDF-'],
     ['FE QR PDF', 'helpers/print-qr.php?equipment=fire_extinguisher&branch=all&items=' +
       encodeURIComponent(JSON.stringify([{code:permissionData.fe_other.extinguisher_code,branch:permissionData.fe_other.branch}])), '%PDF-'],
     ['CSK QR PDF', 'helpers/print-qr.php?equipment=chemical_spill_kit&branch=all&items=' +
       encodeURIComponent(JSON.stringify([{code:permissionData.csk.chemical_spill_kit_code,branch:permissionData.csk.branch}])), '%PDF-']
   ];
   if (permissionData.approved_ids.length) {
     reportChecks.push(['Approved inspection PDF','helpers/generate-report.php?ids=' + permissionData.approved_ids.join(','),'%PDF-']);
     reportChecks.push(['Inspection spreadsheet','backend/controller/GenerateInspectionReportCsvController.php?ids=' + permissionData.approved_ids.join(','),'PK']);
   }
   for (const [name, relative, magic] of reportChecks) {
     currentPage = 'Super Admin ' + label + ': ' + name;
     const report = await evaluate('(async()=>{const response=await fetch('+JSON.stringify(relative)+');const bytes=new Uint8Array(await response.arrayBuffer());return {status:response.status,type:response.headers.get("content-type"),size:bytes.length,magic:String.fromCharCode(...bytes.slice(0,5)),url:response.url};})()');
     record('Super Admin ' + label + ' generates ' + name, report.status === 200 && report.magic.startsWith(magic), report);
   }
 }

 // Viewing history/logs must not require Super Admin to impersonate an inspector.
 await setSession(createSession('Super Admin', '', false, ''));
 for (const page of ['my-inspections.php','inspector-rejected-inspection.php','activity-log.php']) {
   currentPage = 'Super Admin blank employee: ' + page;
   await navigate(page);
   record('Super Admin reads ' + page + ' without an employee ownership filter',
     await evaluate('!location.pathname.includes("401")') && !output.phpErrors.some(error=>error.page===page), 'Read only; actor attribution on writes was not bypassed');
 }

 // Confirm the existing restricted guards and branches still apply to other roles.
 for (const [role, assignedBranch, page, denied] of [
   ['Admin','Laguna','my-inspections.php',true],
   ['Admin','Laguna','QR-generator.php',false],
   ['Inspector','Laguna','QR-generator.php',true],
   ['Inspector','Laguna','maintenance.php',true],
   ['Viewer','Laguna','QR-generator.php',true],
   ['Viewer','Laguna','my-inspections.php',true],
   ['Admin','Laguna','super-admin-monitoring.php',true],
   ['Inspector','Laguna','super-admin-monitoring.php',true]
 ]) {
   await setSession(createSession(role, assignedBranch));
   currentPage = role + ': preserved guard ' + page;
   await navigate(page);
   record(role + ' existing guard for ' + page,
     await evaluate('location.pathname.includes("401")') === denied, {denied});
 }
 await setSession(createSession('Admin','Laguna'));
 currentPage = 'Admin preserved dashboard default';
 await navigate('dashboard.php');
 const adminScope = await request('backend/controller/DashboardDataController.php');
 record('Admin still defaults to its assigned dashboard branch',
   adminScope.data?.data?.filters.branch === 'Laguna', {branch:adminScope.data?.data?.filters.branch});
 await setSession(createSession('Inspector','Laguna'));
 currentPage = 'Inspector preserved dashboard scope';
 const inspectorScope = await request('backend/controller/DashboardDataController.php?branch=all');
 record('Inspector cannot widen dashboard scope using URL branch',
   inspectorScope.data?.data?.filters.branch === 'Laguna' && inspectorScope.data?.data?.filters.can_select_branch === false,
   {branch:inspectorScope.data?.data?.filters.branch});
 await setSession(createSession('Inspector',''));
 const missing = await request('backend/controller/DashboardDataController.php?branch=all');
 record('Inspector without assigned branch retains existing denial', missing.status === 403, {status:missing.status});
 await setSession(createSession('Viewer','Laguna'));
 const viewer = await request('backend/controller/DashboardDataController.php?branch=all');
 record('Unrecognized roles cannot use Super Admin dashboard access', viewer.status === 403, {status:viewer.status});

  await setSession(expired);
  currentPage = "expired session";
  await navigate("chemical-spill-kit-inventory.php");
  record(
    "Expired session is denied",
    (await evaluate("location.pathname")).includes("401"),
    await evaluate("location.pathname"),
  );
  currentPage = "mocked browser fixture";
  await navigate("tests/csk-materials.html");
  await waitFor(
    () => evaluate("!!document.documentElement.dataset.testStatus"),
    8000,
  );
  record(
    "Existing native-DOM fixture",
    await evaluate('document.documentElement.dataset.testStatus === "passed"'),
    await evaluate('document.getElementById("results").textContent'),
  );
}
let currentPage = "startup";
run()
  .catch((error) => {
    output.fatal = error.stack;
    console.log("RUNNER ERROR: " + error.stack);
    process.exitCode = 1;
  })
  .finally(async () => {
    try {
      if (socket?.readyState === WebSocket.OPEN) await send("Browser.close");
    } catch {}
    if (browser && !browser.killed) browser.kill();
    for (const id of sessionIds) {
      try {
        phpRun(
          "session_id('" +
            id +
            "'); session_start(); session_unset(); session_destroy();",
        );
      } catch (error) {
        output.cleanupError = error.message;
      }
    }
    // Never return a green run while browser/backend errors were captured.
    record(
      "No uncaught browser exceptions",
      output.exceptions.length === 0,
      output.exceptions,
    );
    record(
      "No browser console errors",
      output.consoleErrors.length === 0,
      output.consoleErrors,
    );
    record(
      "No rendered PHP errors",
      output.phpErrors.length === 0,
      output.phpErrors,
    );
    const brokenAssets = output.httpErrors.filter(
      (error) =>
        error.status >= 500 ||
        (error.status === 404 &&
          /\.(css|js|png|ico|json)(?:\?|$)/i.test(error.url)),
    );
    record(
      "No failed assets or server errors",
      brokenAssets.length === 0,
      brokenAssets,
    );
    const failedRequests = output.networkFailures.filter(
      (failure) => !failure.canceled,
    );
    record(
      "No uncanceled network failures",
      failedRequests.length === 0,
      failedRequests,
    );
    if (browserProfile) {
      const tempRoot = path.resolve(os.tmpdir()) + path.sep;
      const resolvedProfile = path.resolve(browserProfile);
      if (
        !resolvedProfile.startsWith(tempRoot) ||
        !path.basename(resolvedProfile).startsWith("sms-regression-browser-")
      ) {
        throw new Error(
          "Refusing cleanup outside the temporary regression profile.",
        );
      }
      try {
        fs.rmSync(resolvedProfile, {
          recursive: true,
          force: true,
          maxRetries: 8,
          retryDelay: 250,
        });
      } catch (error) {
        output.cleanupError = error.message;
      }
    }
    if (resultsArgument)
      fs.writeFileSync(
        resultsArgument.slice("--results=".length),
        JSON.stringify(output, null, 2),
      );
    console.log("REGRESSION_RESULTS=" + JSON.stringify(output));
    if (output.checks.some((check) => check.result === "FAILED"))
      process.exitCode = 1;
  });
