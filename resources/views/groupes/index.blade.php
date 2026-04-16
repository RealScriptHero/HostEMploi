@extends('layouts.app')

@section('content')

{{-- Toast --}}
<div id="successAlert"
     style="display:none; position:fixed; top:24px; left:50%; transform:translateX(-50%); z-index:9999; background-color:#22c55e; color:white; padding:14px 22px; border-radius:9999px; box-shadow:0 10px 25px rgba(0,0,0,0.2); min-width:280px; max-width:420px; align-items:center; gap:12px;">
    <svg style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span style="font-size:14px; font-weight:500; flex:1;" id="successMessage"></span>
    <button onclick="hideAlert()" style="background:none; border:none; color:white; cursor:pointer; opacity:0.8; display:flex; align-items:center;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<script>
    let alertTimeout;
    function showAlert(message) {
        const alertBox = document.getElementById('successAlert');
        const msg = document.getElementById('successMessage');
        msg.textContent = message;
        alertBox.style.display = 'flex';
        alertBox.style.opacity = '1';
        clearTimeout(alertTimeout);
        alertTimeout = setTimeout(() => hideAlert(), 4000);
    }
    function hideAlert() {
        const alertBox = document.getElementById('successAlert');
        alertBox.style.opacity = '0';
        setTimeout(() => { alertBox.style.display = 'none'; alertBox.style.opacity = '1'; }, 300);
    }
</script>

<style>
* { box-sizing: border-box; }
.gp-wrap { width: 100%; padding: 0 24px 40px; font-family: 'Inter', sans-serif; }
.gp-breadcrumb { display:flex; align-items:center; gap:6px; font-size:13px; color:#9ca3af; margin-bottom:20px; }
.gp-breadcrumb .sep { color:#d1d5db; }
.gp-breadcrumb .cur { color:#374151; font-weight:600; }
.gp-header { display:flex; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:24px; }
.gp-title h1 { font-size:22px; font-weight:700; color:#111827; margin:0 0 4px; }
.gp-title p  { font-size:13px; color:#6b7280; margin:0; }
.gp-controls { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-left:auto; }
.gp-search { position:relative; }
.gp-search input { padding:9px 14px 9px 38px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:13px; color:#374151; outline:none; background:white; width:220px; transition:border-color .15s, box-shadow .15s; }
.gp-search input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.gp-search .ico { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#9ca3af; pointer-events:none; width:15px; height:15px; }
.gp-select { padding:9px 13px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:13px; color:#374151; background:white; outline:none; cursor:pointer; }
.gp-select:focus { border-color:#6366f1; }
.btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:10px; font-size:13px; font-weight:600; border:none; cursor:pointer; transition:all .15s; white-space:nowrap; }
.btn svg { width:15px; height:15px; flex-shrink:0; }
.btn-import { background:#10b981; color:white; } .btn-import:hover { background:#059669; }
.btn-export { background:#059669; color:white; } .btn-export:hover { background:#047857; }
.btn-add    { background:#6366f1; color:white; } .btn-add:hover { background:#4f46e5; box-shadow:0 4px 12px rgba(99,102,241,.3); }
.btn-cancel { background:white; color:#374151; border:1.5px solid #e5e7eb; } .btn-cancel:hover { background:#f9fafb; }
.btn-save   { background:#6366f1; color:white; min-width:110px; justify-content:center; } .btn-save:hover { background:#4f46e5; }
.gp-card { background:white; border-radius:16px; border:1px solid #e8eaf0; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; }
.gp-table { width:100%; border-collapse:collapse; }
.gp-table thead th { padding:14px 20px; text-align:left; font-size:11px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#9ca3af; background:#f9fafb; border-bottom:1px solid #f0f0f5; white-space:nowrap; }
.gp-table thead th.th-right { text-align:right; }
.gp-table thead th.th-center { text-align:center; }
.gp-table tbody tr { border-bottom:1px solid #f3f4f6; transition:background .12s; }
.gp-table tbody tr:last-child { border-bottom:none; }
.gp-table tbody tr:hover { background:#fafbff; }
.gp-table tbody td { padding:15px 20px; vertical-align:middle; }
.g-avatar { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:15px; flex-shrink:0; }
.badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; white-space:nowrap; }
.badge-gray   { background:#f3f4f6; color:#6b7280; }
.badge-blue   { background:#dbeafe; color:#1d4ed8; }
.badge-green  { background:#dcfce7; color:#15803d; }
.badge-orange { background:#fff3e0; color:#c2410c; }
.badge-purple { background:#f3e8ff; color:#7c3aed; }
.badge-indigo { background:#e0e7ff; color:#4338ca; }
.prog-track { height:6px; border-radius:99px; background:#e5e7eb; overflow:hidden; flex:1; max-width:140px; }
.prog-fill  { height:100%; border-radius:99px; transition:width .5s ease; }
.prog-blue { background:#3b82f6; } .prog-yellow { background:#f59e0b; } .prog-green { background:#10b981; }
.act-btn { width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:7px; border:none; cursor:pointer; transition:background .12s; background:transparent; }
.act-btn svg { width:15px; height:15px; }
.act-blue { color:#3b82f6; } .act-blue:hover { background:#dbeafe; }
.act-gray { color:#6b7280; } .act-gray:hover { background:#f3f4f6; color:#111827; }
.act-red  { color:#ef4444; } .act-red:hover  { background:#fee2e2; }
.gp-pagination { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; background:#fafafa; border-top:1px solid #f0f0f5; }
.gp-pagination .info { font-size:13px; color:#6b7280; }
.gp-pagination .info strong { color:#374151; font-weight:600; }
.pg-btns { display:flex; align-items:center; gap:4px; }
.pg-btn { width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; border:1.5px solid #e5e7eb; font-size:13px; font-weight:500; cursor:pointer; background:white; color:#374151; transition:all .12s; }
.pg-btn:hover:not([disabled]) { border-color:#6366f1; color:#6366f1; background:#f5f5ff; }
.pg-btn.pg-active { background:#6366f1; border-color:#6366f1; color:white; }
.pg-btn[disabled] { opacity:.35; cursor:not-allowed; }
.form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.form-input { width:100%; padding:10px 13px; border:1.5px solid #e5e7eb; border-radius:9px; font-size:13px; color:#111827; outline:none; background:white; transition:border-color .15s, box-shadow .15s; font-family:inherit; }
.form-input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
[x-cloak] { display:none !important; }
@keyframes mIn { from{opacity:0;transform:scale(.96) translateY(8px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>

<div x-data="groupsComponent" x-init="init()">
    <div class="gp-wrap">

        <div class="gp-breadcrumb">
            <span class="text-gray-500">Tableau de bord</span>
            <span class="sep">›</span>
            <span class="cur">Groups</span>
        </div>

        <div class="gp-header">
            <div class="gp-title">
                <h1>Groups</h1>
                <p>Manage all training groups and their basic information</p>
            </div>
            <div class="gp-controls">
                <div class="gp-search">
                    <svg class="ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    <input x-model="search" placeholder="Search groups...">
                </div>
                <select x-model="filterCentre" class="gp-select">
                    <option value="">All Centres</option>
                    <template x-for="centre in centres" :key="centre.id">
                        <option :value="centre.id" x-text="centre.nom"></option>
                    </template>
                </select>
                <select x-model="filterFiliere" class="gp-select">
                    <option value="">All Filières</option>
                    <template x-for="filiere in filieres" :key="filiere">
                        <option :value="filiere" x-text="filiere"></option>
                    </template>
                </select>
                <select x-model="filterNiveau" class="gp-select">
                    <option value="">All Niveaux</option>
                    <template x-for="niveau in niveaux" :key="niveau">
                        <option :value="niveau" x-text="niveauLabel(niveau)"></option>
                    </template>
                </select>
                <select x-model="filterGroupe" class="gp-select">
                    <option value="">All Groups</option>
                    <template x-for="group in groups" :key="group.id">
                        <option :value="group.id" x-text="group.name"></option>
                    </template>
                </select>
                <select x-model="filterModule" :disabled="!filterGroupe" class="gp-select" :style="{opacity: filterGroupe ? 1 : 0.5, cursor: filterGroupe ? 'pointer' : 'not-allowed'}">
                    <option value="">Select a group first</option>
                    <template x-show="filterGroupe" x-for="module in availableModules" :key="module.id">
                        <option :value="module.id" x-text="module.nomModule"></option>
                    </template>
                </select>
                <label class="btn btn-import" style="cursor:pointer;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6H16a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Import CSV
                    <input type="file" accept=".csv" @change="importFromCSV" style="display:none;">
                </label>
                <button @click="exportToCSV()" class="btn btn-export">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export
                </button>
                <button @click="openModal()" class="btn btn-add">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Group
                </button>
            </div>
        </div>

        <div class="gp-card">
            <div style="overflow-x:auto;">
                <table class="gp-table">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Centre</th>
                            <th>Filière</th>
                            <th>Niveau</th>
                            <th class="th-center">Effectif</th>
                            <th>Progress</th>
                            <th class="th-right" style="padding-right:22px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(group, idx) in paginatedGroups()" :key="group.id">
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div class="g-avatar" :style="`background:${avatarBg(group.name)}18; color:${avatarBg(group.name)};`">
                                            <span x-text="group.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <span style="font-weight:600; font-size:14px; color:#111827;" x-text="group.name"></span>
                                    </div>
                                </td>
                                <td>
                                    {{-- ★ FIX: show centre name in indigo badge when assigned, gray when not --}}
                                    <span class="badge"
                                          :class="group.centre_nom ? 'badge-indigo' : 'badge-gray'"
                                          x-text="group.centre_nom || 'Non assigné'">
                                    </span>
                                </td>
                                <td><span class="badge" :class="filiereClass(group.filiere)" x-text="group.filiere || '—'"></span></td>
                                <td><span style="font-size:14px; color:#374151;" x-text="niveauLabel(group.niveau)"></span></td>
                                <td style="text-align:center;">
                                    <span style="font-size:14px; font-weight:600; color:#111827;" x-text="group.effectif"></span>
                                    <span style="font-size:12px; color:#9ca3af; margin-left:3px;">trainees</span>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="prog-track">
                                            <div class="prog-fill" :class="progressColorClass(group.progress)" :style="`width:${group.progress}%`"></div>
                                        </div>
                                        <span style="font-size:13px; font-weight:700; min-width:38px; text-align:right;" :style="`color:${progressTextColor(group.progress)}`" x-text="group.progress + '%'"></span>
                                    </div>
                                </td>
                                <td style="text-align:right; padding-right:18px;">
                                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:2px;">
                                        <button @click="viewTimetable(group)" class="act-btn act-blue" title="View Timetable">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </button>
                                        <button @click="editGroup(group)" class="act-btn act-gray" title="Edit">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="deleteGroup(group.id)" class="act-btn act-red" title="Delete">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 3h4a1 1 0 011 1v1H9V4a1 1 0 011-1z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="paginatedGroups().length === 0">
                            <td colspan="7" style="padding:60px 20px; text-align:center; color:#9ca3af; font-size:14px;">
                                No groups found. (Debug: groups array has <span x-text="groups.length"></span> items, paginated has <span x-text="paginatedGroups().length"></span>)
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="gp-pagination" style="display: none;">
                <div class="info">
                    Showing <strong x-text="startItem() + '–' + endItem()"></strong> of <strong x-text="total"></strong> groups
                </div>
                <div class="pg-btns">
                    <button @click="prevPage()" :disabled="page === 1" class="pg-btn">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <template x-for="p in pages()" :key="p">
                        <button @click="goToPage(p)" :class="{'pg-active': page === p}" class="pg-btn" x-text="p"></button>
                    </template>
                    <button @click="nextPage()" :disabled="page === lastPage" class="pg-btn">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div id="groups-modal-fallback"
     style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.5); z-index:50; align-items:center; justify-content:center; padding:16px;">
    <div style="background:white; border-radius:18px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.2); animation:mIn .22s ease;" onclick="event.stopPropagation()">
        <div style="padding:22px 24px 18px; border-bottom:1px solid #f0f0f5; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:white; border-radius:18px 18px 0 0; z-index:1;">
            <h3 id="modal-title" style="font-size:16px; font-weight:700; color:#111827; margin:0;">Add Group</h3>
            <button onclick="closeModalDirect()" style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:7px;border:none;cursor:pointer;background:transparent;color:#6b7280;">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding:22px 24px;">
            <div style="display:flex; flex-direction:column; gap:16px;">
                <div>
                    <label class="form-label">Group Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="m-name" class="form-input" placeholder="e.g. DD202">
                </div>
                <div>
                    <label class="form-label">Centre <span style="color:#ef4444;">*</span></label>
                    <select id="m-centre" class="form-input">
                        <option value="">— Select Centre —</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label class="form-label">Filière <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="m-filiere" class="form-input" placeholder="e.g. DEV, NET, AI Engineering">
                    </div>
                    <div>
                        <label class="form-label">Niveau <span style="color:#ef4444;">*</span></label>
                        <select id="m-niveau" class="form-input">
                            <option value="">— Select —</option>
                            <option value="1">1ère année</option>
                            <option value="2">2ème année</option>
                            <option value="3">3ème année</option>
                            <option value="Technicien">Technicien</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label class="form-label">Number of Trainees</label>
                        <input type="number" id="m-effectif" class="form-input" min="0" max="100" placeholder="e.g. 25">
                    </div>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea id="m-notes" rows="2" class="form-input" style="resize:none;" placeholder="Additional notes..."></textarea>
                </div>
                <div id="m-error" style="display:none; align-items:center; gap:8px; padding:10px 14px; background:#fef2f2; border:1px solid #fecaca; border-radius:9px; font-size:13px; color:#dc2626;">
                    <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="m-error-text"></span>
                </div>
            </div>
        </div>
        <div style="padding:12px 24px 22px; display:flex; gap:10px; justify-content:flex-end;">
            <button onclick="closeModalDirect()" class="btn btn-cancel">Cancel</button>
            <button id="m-save-btn" onclick="saveGroupDirect()" class="btn btn-save">
                <span id="m-save-label">Save Group</span>
            </button>
        </div>
    </div>
</div>

<script>
const registerFn = () => {
    Alpine.data('groupsComponent', () => ({
        search: '',
        page: 1,
        perPage: 20,
        filterFiliere: '',
        filterNiveau: '',
        filterCentre: '',
        filterGroupe: '',
        filterModule: '',
        centres: [],
        modules: [],
        availableModules: [],
        filieres: [],
        niveaux: [],
        groups: [],
        total: 0,
        lastPage: 1,
        currentPage: 1,

        async init() {
            console.log('Initializing groups page...');
            await this.loadCentres();
            console.log('Centres loaded, now loading other data...');
            await Promise.all([
                this.loadFilieres(),
                this.loadNiveaux()
            ]);
            console.log('All reference data loaded, now fetching groups...');
            await this.fetchGroupes();
            console.log('Groups loaded, setting up watchers...');
            
            this.$watch('search',        () => { this.page = 1; this.fetchGroupes(); });
            this.$watch('filterFiliere', () => { this.page = 1; this.fetchGroupes(); });
            this.$watch('filterNiveau',  () => { this.page = 1; this.fetchGroupes(); });
            this.$watch('filterCentre',  () => { this.page = 1; this.fetchGroupes(); });
            this.$watch('filterGroupe',  async () => { 
                if (this.filterGroupe) {
                    await this.loadModulesForGroupe(this.filterGroupe);
                    console.log('Modules loaded for group:', this.filterGroupe, this.availableModules);
                } else {
                    this.availableModules = [];
                }
                this.filterModule = '';
            });
        },

        async loadCentres() {
            try {
                const res = await fetch('/api/centres');
                if (!res.ok) throw new Error();
                const data = await res.json();
                this.centres = (data.data || data || []).map(c => ({
                    id:  c.id,
                    nom: c.nomCentre || c.nom || c.name || 'Centre ' + c.id
                }));
                console.log('Centres loaded:', this.centres);
                populateCentreSelect(this.centres);
            } catch(e) { 
                console.error('Could not load centres', e);
                this.centres = []; 
            }
        },

        async loadFilieres() {
            try {
                const res = await fetch('/api/groupes/filieres');
                if (!res.ok) throw new Error();
                this.filieres = await res.json();
            } catch { this.filieres = []; }
        },

        async loadNiveaux() {
            try {
                const res = await fetch('/api/groupes?all=1');
                if (!res.ok) throw new Error();
                const data = await res.json();
                const allGroups = Array.isArray(data) ? data : (data.data || []);
                const niveaux = Array.from(new Set(
                    allGroups.map(g => String(g.niveau || '').trim()).filter(Boolean)
                ));
                const preferredOrder = ['3', '2', '1', 'Technicien'];
                niveaux.sort((a, b) => {
                    const ia = preferredOrder.indexOf(a);
                    const ib = preferredOrder.indexOf(b);
                    if (ia === -1 && ib === -1) return a.localeCompare(b, 'fr', { sensitivity: 'base' });
                    if (ia === -1) return 1;
                    if (ib === -1) return -1;
                    return ia - ib;
                });
                this.niveaux = niveaux;
            } catch (e) {
                console.error('Could not load niveaux', e);
                this.niveaux = [];
            }
        },

        async loadModulesForGroupe(groupeId) {
            try {
                const res = await fetch(`/api/modules/by-groupe/${groupeId}`);
                if (!res.ok) {
                    console.error('Failed to load modules:', res.status, res.statusText);
                    throw new Error();
                }
                this.availableModules = await res.json();
                console.log('Modules fetched successfully:', this.availableModules);
            } catch (e) { 
                console.error('Error loading modules for groupe:', groupeId, e);
                this.availableModules = []; 
            }
        },

        async fetchGroupes() {
            try {
                const params = new URLSearchParams({
                    search:    this.search || '',
                    filiere:   this.filterFiliere || '',
                    niveau:    this.filterNiveau || '',
                    centre_id: this.filterCentre || '',
                    no_pagination: '1', // Request all groups without pagination
                });
                const res  = await fetch('/api/groupes?' + params.toString());
                if (!res.ok) throw new Error();
                const data = await res.json();

                // Debug: log what the API actually returns
                console.log('API Response:', data);
                console.log('Data array length:', data.data ? data.data.length : 'no data property');

                const centresList = this.centres;
                this.groups = (data.data || []).map(g => {
                    // Try every possible field name the API might use
                    const cid = g.centre_id ?? g.centreId ?? g.id_centre ?? null;

                    // 1. Try nested relation
                    const fromRelation = g.centre
                        ? (g.centre.nomCentre || g.centre.nom || g.centre.name || null)
                        : null;

                    // 2. Fall back to local centres array (== handles string/int mismatch)
                    const fromLocal = cid ? centresList.find(c => c.id == cid) : null;

                    const centre_nom = fromRelation || (fromLocal ? fromLocal.nom : null);

                    return {
                        id:         g.id,
                        name:       g.nomGroupe || g.name || ('G' + g.id),
                        centre_id:  cid,
                        centre_nom: centre_nom,
                        filiere:    g.filiere || '',
                        niveau:     g.niveau  || '',
                        effectif:   g.effectif   ?? 0,
                        progress:   Number(g.advancement ?? g.avancement ?? g.progress ?? 0),
                        active:     g.active ?? true,
                        notes:      g.notes  ?? '',
                    };
                });

                this.total       = data.total      || 0;
                this.lastPage    = data.lastPage    || 1;
                this.currentPage = data.currentPage || 1;
                this.page        = this.currentPage;

                // Debug: log final processed groups
                console.log('Final groups array:', this.groups);
                console.log('Groups count:', this.groups.length);
            } catch(e) { 
                console.error('Could not load groups', e);
                console.error('Error details:', e.message);
            }
        },

        openModal() {
            window._editingGroupId = null;
            document.getElementById('modal-title').textContent    = 'Add Group';
            document.getElementById('m-save-label').textContent   = 'Save Group';
            document.getElementById('m-name').value     = '';
            document.getElementById('m-centre').value   = '';
            document.getElementById('m-filiere').value  = '';
            document.getElementById('m-niveau').value   = '';
            document.getElementById('m-effectif').value = '20';
            document.getElementById('m-notes').value    = '';
            hideModalError();
            document.getElementById('groups-modal-fallback').style.display = 'flex';
            setTimeout(() => document.getElementById('m-name').focus(), 50);
        },

        editGroup(group) {
            window._editingGroupId = group.id;
            document.getElementById('modal-title').textContent    = 'Edit Group';
            document.getElementById('m-save-label').textContent   = 'Update Group';
            document.getElementById('m-name').value     = group.name      || '';
            document.getElementById('m-centre').value   = group.centre_id || '';
            document.getElementById('m-filiere').value  = group.filiere   || '';
            document.getElementById('m-niveau').value   = group.niveau    || '';
            document.getElementById('m-effectif').value = group.effectif  || 0;
            document.getElementById('m-notes').value    = group.notes     || '';
            hideModalError();
            document.getElementById('groups-modal-fallback').style.display = 'flex';
            setTimeout(() => document.getElementById('m-name').focus(), 50);
        },

        async deleteGroup(id) {
            if (!confirm('Are you sure you want to delete this group?')) return;
            try {
                const headers = {};
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) headers['X-CSRF-TOKEN'] = meta.content;
                const res = await fetch(`/api/groupes/${id}`, { method: 'DELETE', headers });
                if (!res.ok) throw new Error();
                await this.fetchGroupes();
                showAlert('Group deleted successfully!');
            } catch { alert('Could not delete group.'); }
        },

        paginatedGroups() { return this.groups; },

        pages() {
            const total = Math.max(1, Math.ceil((this.total || 0) / this.perPage));
            return Array.from({ length: total }, (_, i) => i + 1);
        },

        startItem() { return this.total ? (this.currentPage - 1) * this.perPage + 1 : 0; },
        endItem()   { return Math.min(this.total, this.currentPage * this.perPage); },

        async goToPage(p)  { this.page = p; this.currentPage = p; await this.fetchGroupes(); },
        async prevPage()   { if (this.page > 1) { this.page--; this.currentPage = this.page; await this.fetchGroupes(); } },
        async nextPage()   { if (this.page < this.lastPage) { this.page++; this.currentPage = this.page; await this.fetchGroupes(); } },

        viewTimetable(group) {
            const centreId = group.centre_id || group.centre?.id;
            let url = '/emploi-global?groupe_id=' + group.id;
            if (centreId) {
                url += '&centre_id=' + centreId;
            }
            window.location.href = url;
        },

        niveauLabel(n) {
            if (n === '1' || n === 1) return '1ère année';
            if (n === '2' || n === 2) return '2ème année';
            if (n === '3' || n === 3) return '3ème année';
            if (n === 'Technicien')   return 'Technicien';
            return n || '—';
        },

        filiereClass(f) {
            return { DEV:'badge-blue', NET:'badge-green', RESEAUX:'badge-orange', MULTIMEDIA:'badge-purple' }[f] || 'badge-gray';
        },

        progressColorClass(p) {
            if (p >= 100) return 'prog-green';
            if (p >= 40)  return 'prog-yellow';
            return 'prog-blue';
        },

        progressTextColor(p) {
            if (p >= 100) return '#10b981';
            if (p >= 40)  return '#f59e0b';
            return '#6b7280';
        },

        avatarBg(name) {
            const colors = ['#6366f1','#8b5cf6','#ec4899','#f97316','#22c55e','#14b8a6','#3b82f6','#f43f5e'];
            let h = 0;
            for (let i = 0; i < (name || '').length; i++) h = (name.charCodeAt(i) + ((h << 5) - h));
            return colors[Math.abs(h) % colors.length];
        },

        exportToCSV() {
            const headers = ['Name','Centre','Filiere','Niveau','Effectif','Notes','Active'];
            const rows = [headers.join(',')];
            this.groups.forEach(g => rows.push([
                `"${g.name}"`, `"${g.centre_nom||''}"`, `"${g.filiere}"`, `"${g.niveau}"`,
                g.effectif, `"${(g.notes||'').replace(/"/g,'""')}"`, g.active
            ].join(',')));
            const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'groupes_' + new Date().toISOString().slice(0,10) + '.csv';
            a.click();
        },

        importFromCSV(event) {
            const file = event.target.files[0];
            if (!file) return;
            const self = this;
            const reader = new FileReader();
            reader.onload = async function(e) {
                try {
                    const lines = e.target.result.split('\n');
                    let imported = 0;
                    for (let i = 1; i < lines.length; i++) {
                        const line = lines[i].trim();
                        if (!line) continue;
                        const values = line.match(/(".*?"|[^,]+)(?=\s*,|\s*$)/g);
                        if (!values || values.length < 4) continue;
                        const cl = v => v ? v.replace(/^"(.*)"$/, '$1').replace(/""/g, '"') : '';
                        const centre = self.centres.find(c => c.nom === cl(values[1]));
                        const payload = {
                            nomGroupe:   cl(values[0]),
                            centre_id:   centre ? centre.id : null,
                            filiere:     cl(values[2]),
                            niveau:      cl(values[3]),
                            effectif:    parseInt(values[4]) || 20,
                            notes:       cl(values[5]),
                            active:      values[6] === 'true' || values[6] === '1',
                        };
                        const headers = { 'Content-Type': 'application/json' };
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) headers['X-CSRF-TOKEN'] = meta.content;
                        try {
                            const res = await fetch('/api/groupes', { method: 'POST', headers, body: JSON.stringify(payload) });
                            if (res.ok) imported++;
                        } catch {}
                    }
                    showAlert('Successfully imported ' + imported + ' groups!');
                    await self.fetchGroupes();
                    event.target.value = '';
                } catch { alert('Error importing CSV file.'); }
            };
            reader.readAsText(file);
        },
    }));
};

if (window.Alpine && Alpine.data) { registerFn(); }
else { document.addEventListener('alpine:init', registerFn); }

// ── Modal plain JS helpers ───────────────────────────────────────────────────

window._editingGroupId = null;

function populateCentreSelect(centres) {
    const sel = document.getElementById('m-centre');
    if (!sel) return;
    while (sel.options.length > 1) sel.remove(1);
    centres.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.nom;
        sel.appendChild(opt);
    });
}

function showModalError(msg) {
    document.getElementById('m-error-text').textContent = msg;
    document.getElementById('m-error').style.display = 'flex';
}
function hideModalError() {
    document.getElementById('m-error').style.display = 'none';
}
function closeModalDirect() {
    document.getElementById('groups-modal-fallback').style.display = 'none';
    window._editingGroupId = null;
}

document.getElementById('groups-modal-fallback').addEventListener('click', function(e) {
    if (e.target === this) closeModalDirect();
});

async function saveGroupDirect() {
    hideModalError();
    const name      = document.getElementById('m-name').value.trim();
    const centre_id = document.getElementById('m-centre').value;
    const filiere   = document.getElementById('m-filiere').value;
    const niveau    = document.getElementById('m-niveau').value;
    const effectif  = parseInt(document.getElementById('m-effectif').value) || 0;
    const notes     = document.getElementById('m-notes').value;

    if (!name)      { showModalError('Group name is required.');   return; }
    if (!centre_id) { showModalError('Please select a centre.');   return; }
    if (!filiere.trim())   { showModalError('Please enter a filière.');  return; }
    if (!niveau)    { showModalError('Please select a niveau.');   return; }

    const payload = { nomGroupe: name, centre_id, filiere, niveau, effectif, notes, active: 1 };
    const headers = { 'Content-Type': 'application/json' };
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) headers['X-CSRF-TOKEN'] = meta.content;

    const btn = document.getElementById('m-save-btn');
    btn.disabled = true;
    document.getElementById('m-save-label').textContent = 'Saving…';

    try {
        const isEdit = !!window._editingGroupId;
        const url    = isEdit ? `/api/groupes/${window._editingGroupId}` : '/api/groupes';
        const method = isEdit ? 'PUT' : 'POST';
        const res    = await fetch(url, { method, headers, body: JSON.stringify(payload) });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Failed to save.');
        }

        closeModalDirect();
        showAlert(isEdit ? 'Group updated successfully!' : 'Group created successfully!');

        // Refresh Alpine table
        let alpine = null;
        const comp = document.querySelector('[x-data="groupsComponent"]');
        if (comp) {
            alpine = comp.__x ? comp.__x.$data : (comp._x_dataStack ? comp._x_dataStack[0] : null);
        }

        if (!alpine && window.Alpine && typeof window.Alpine === 'object' && window.Alpine.data) {
            const alt = document.querySelector('[x-data="groupsComponent"]');
            if (alt && alt.__x) alpine = alt.__x.$data;
        }

        if (alpine) {
            if (!isEdit) alpine.page = 1;
            await alpine.fetchGroupes();
            await alpine.loadFilieres(); // Refresh filières list for new entries
        } else {
            console.warn('Could not access Alpine component for groupsComponent; page may need manual refresh.');
        }
    } catch(e) {
        showModalError(e.message || 'An error occurred. Please try again.');
    } finally {
        btn.disabled = false;
        document.getElementById('m-save-label').textContent = window._editingGroupId ? 'Update Group' : 'Save Group';
    }
}
</script>

@endsection