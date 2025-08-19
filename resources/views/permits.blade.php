<!-- resources/views/permits.blade.php -->
<!doctype html>
<html lang="en">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Permits</title>
<style>
  :root { --bg:#0f172a; --card:#111827; --fg:#e5e7eb; --muted:#9ca3af; --ok:#22c55e; --info:#0ea5e9; --warn:#f59e0b; --bad:#ef4444; --border:#1f2937; --input:#0b1220; --primary:#2563eb; }
  * { box-sizing: border-box; }
  body { margin:0; font:16px/1.45 system-ui, -apple-system, Segoe UI, Roboto; background:var(--bg); color:var(--fg); -webkit-font-smoothing:antialiased; }
  main { max-width: 960px; margin: 40px auto; padding: 0 16px; }
  .card { background:var(--card); border-radius:16px; padding:16px; box-shadow: 0 8px 32px rgba(0,0,0,.35); }

  h1 { font-size:28px; margin:0 0 12px; }

  /* Unified input/select look (applies to search + form fields) */
  .input, select {
    width:100%;
    background:var(--input);
    color:var(--fg);
    border:1px solid var(--border);
    padding:10px 12px;
    border-radius:12px;
    outline:none;
    appearance:none;
    -webkit-appearance:none;
    transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
  }
  .input::placeholder { color: var(--muted); }
  .input:focus, select:focus { border-color:#334155; box-shadow:0 0 0 3px rgba(37,99,235,.25); }

  /* Buttons (modern, rounded, consistent) */
  button { appearance:none; -webkit-appearance:none; outline:none; border:0; cursor:pointer; }
  .btn {
    display:inline-flex; align-items:center; gap:8px;
    padding:9px 14px; border-radius:12px; font-weight:600; letter-spacing:.2px;
    background:var(--primary); color:#fff; border:1px solid transparent;
    box-shadow:0 2px 8px rgba(0,0,0,.25); transition:transform .05s ease, filter .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease;
  }
  .btn:hover { filter:brightness(1.08); box-shadow:0 4px 14px rgba(0,0,0,.28); }
  .btn:active { transform: translateY(1px); }
  .btn.ghost { background:transparent; color:var(--fg); border-color:#334155; }
  .btn.ghost:hover { background:rgba(148,163,184,.08); }
  .btn.approve { background:var(--ok); }
  .btn.sms { background:var(--info); }
  .btn.danger { background:var(--bad); }
  .btn.ghost.danger { color:#fca5a5; border-color:var(--bad); }
  .btn.ghost.danger:hover { background:rgba(239,68,68,.10); }

  .row { display:flex; gap:8px; flex-wrap:wrap; }
  .toolbar { display:flex; gap:8px; align-items:center; margin: 12px 0; }
  .toolbar .input { flex:1; } /* make search box grow */

  table { width:100%; border-collapse: collapse; margin-top:12px; }
  th, td { text-align:left; padding:10px; border-bottom:1px solid var(--border); }
  .pill { display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; }
  .approved { background:rgba(34,197,94,.15); color:#86efac; }
  .pending  { background:rgba(245,158,11,.15); color:#fbbf24; }
  .rejected { background:rgba(239,68,68,.15); color:#fca5a5; }

  .pager { display:flex; gap:8px; justify-content:flex-end; margin-top:8px; }

  /* Action cell layout */
  .actions { display:flex; gap:8px; flex-wrap:wrap; }
</style>
<main>
  <div class="card">
    <h1> Permits</h1>

    <div class="toolbar">
      <form id="newForm" class="row" style="flex:1">
        <input class="input" name="number" placeholder="Number (e.g. PRM-1234)" required>
        <input class="input" name="applicant" placeholder="Applicant" required>
        <input class="input" name="phone_number" placeholder="Phone (+1XXXXXXXXXX)">
        <select class="input" name="status" required>
          <option value="pending">pending</option>
          <option value="approved">approved</option>
          <option value="rejected">rejected</option>
        </select>
        <button class="btn">Create</button>
      </form>
    </div>

    <div class="toolbar">
      <input id="q" class="input" placeholder="Search number/applicant">
      <select id="status" class="input" style="max-width:220px">
        <option value="">All statuses</option>
        <option>pending</option>
        <option>approved</option>
        <option>rejected</option>
      </select>
      <button class="btn ghost" id="applyFilters">Apply</button>
    </div>

    <table>
      <thead>
        <tr><th>ID</th><th>Number</th><th>Applicant</th><th>Status</th><th>Phone</th><th>Actions</th></tr>
      </thead>
      <tbody id="rows"></tbody>
    </table>

    <div class="pager">
      <button class="btn ghost" id="prev">Prev</button>
      <span id="pageInfo" class="muted"></span>
      <button class="btn ghost" id="next">Next</button>
    </div>
  </div>
</main>
<script>
let page = 1;

function badge(status){
  return `<span class="pill ${status}">${status}</span>`;
}

async function load(){
  const q = document.getElementById('q').value.trim();
  const s = document.getElementById('status').value;
  const params = new URLSearchParams({ page });
  if (q) params.set('q', q);
  if (s) params.set('status', s);

  const r = await fetch('/api/permits?'+params, { headers:{Accept:'application/json'} });
  const j = await r.json();

  const rows = j.data.map(p => `
    <tr>
      <td>${p.id}</td>
      <td>${p.number}</td>
      <td>${p.applicant}</td>
      <td>${badge(p.status)}</td>
      <td>${p.phone_number ?? ''}</td>
      <td>
        <div class="actions">
          <button class="btn approve" onclick="approve(${p.id},0)">✓ Approve</button>
          <button class="btn sms" onclick="approve(${p.id},1)">✉ Approve + SMS</button>
          <button class="btn ghost danger" onclick="del(${p.id})">🗑 Delete</button>
        </div>
      </td>
    </tr>`).join('');
  document.getElementById('rows').innerHTML = rows;

  document.getElementById('pageInfo').textContent =
    `Page ${j.meta.current_page} of ${j.meta.last_page}`;
  document.getElementById('prev').disabled = !j.links.prev;
  document.getElementById('next').disabled = !j.links.next;
}

async function approve(id, notify){
  const r = await fetch(`/api/permits/${id}/approve?notify=${notify?1:0}`, {
    method:'POST', headers:{Accept:'application/json'}
  });
  await r.json(); load();
}
async function del(id){
  if(!confirm('Delete this permit?')) return;
  await fetch(`/api/permits/${id}`, { method:'DELETE', headers:{Accept:'application/json'} });
  load();
}

document.getElementById('newForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const r = await fetch('/api/permits', {
    method:'POST',
    headers:{'Content-Type':'application/json', Accept:'application/json'},
    body: JSON.stringify(Object.fromEntries(fd))
  });
  if(r.status===201){ e.target.reset(); page = 1; load(); }
  else {
    const j = await r.json();
    alert('Validation failed:\n' + JSON.stringify(j.errors, null, 2));
  }
});
document.getElementById('applyFilters').addEventListener('click', ()=>{ page=1; load(); });
document.getElementById('prev').addEventListener('click', ()=>{ if(page>1){ page--; load(); }});
document.getElementById('next').addEventListener('click', ()=>{ page++; load(); });

load();
</script>
