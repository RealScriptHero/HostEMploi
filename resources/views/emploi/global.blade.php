@extends('layouts.app')

@section('content')

<style>
* { box-sizing: border-box; }

.et-page {
    font-family: 'Inter', system-ui, sans-serif;
    background: #f8fafc;
    min-height: 100vh;
    padding: 20px 20px 40px;
}

/* ── TOP BAR ── */
.et-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.et-title h1 { font-size: 17px; font-weight: 700; color: #0f172a; margin: 0 0 2px; letter-spacing: -.3px; }
.et-title p  { font-size: 12px; color: #94a3b8; margin: 0; }

.et-controls { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

.et-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 13px; border-radius: 8px; font-size: 12px;
    font-weight: 600; border: none; cursor: pointer;
    transition: all .13s; white-space: nowrap; font-family: inherit;
}
.et-btn svg { width: 13px; height: 13px; flex-shrink: 0; }

.et-ctrl-centre {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 12px; background: white;
    border: 1.5px solid #a78bfa; border-radius: 8px;
}
.et-ctrl-centre svg { width: 14px; height: 14px; color: #7c3aed; flex-shrink: 0; }
.et-ctrl-centre select {
    border: none; font-size: 12px; font-weight: 600;
    color: #374151; background: transparent; outline: none;
    cursor: pointer; font-family: inherit;
}

.et-ctrl-date {
    display: flex; align-items: center;
    background: white; border: 1.5px solid #60a5fa;
    border-radius: 8px; overflow: hidden;
}
.et-ctrl-date-icon {
    padding: 7px 8px 7px 10px; display: flex;
    align-items: center; color: #3b82f6; flex-shrink: 0;
}
.et-ctrl-date-icon svg { width: 13px; height: 13px; }
.et-ctrl-date input[type="date"] {
    border: none; font-size: 12px; font-weight: 600;
    color: #1d4ed8; background: transparent; outline: none;
    cursor: pointer; padding: 7px 4px; width: 132px;
    font-family: inherit;
}
.et-ctrl-date-divider { width: 1px; background: #bfdbfe; align-self: stretch; flex-shrink: 0; }
.et-ctrl-date-nav { display: flex; align-items: center; }
.et-ctrl-date-nav button {
    padding: 7px 7px; border: none; background: transparent;
    cursor: pointer; color: #3b82f6; display: flex;
    align-items: center; transition: background .1s;
}
.et-ctrl-date-nav button:hover { background: #eff6ff; }
.et-ctrl-date-nav button svg { width: 13px; height: 13px; }
.et-ctrl-date-today {
    padding: 7px 9px; border: none; background: transparent;
    font-size: 11px; font-weight: 700; color: #2563eb;
    cursor: pointer; transition: background .1s; font-family: inherit;
}
.et-ctrl-date-today:hover { background: #eff6ff; }

.et-status {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 11px; border-radius: 8px;
    font-size: 11px; font-weight: 700; border: 1.5px solid;
}
.et-status svg { width: 12px; height: 12px; }
.et-status-ok  { background: #f0fdf4; color: #15803d; border-color: #86efac; }
.et-status-new { background: #fffbeb; color: #b45309; border-color: #fde68a; }

.et-btn-green   { background: #10b981; color: white; }
.et-btn-green:hover   { background: #059669; }
.et-btn-emerald { background: #059669; color: white; }
.et-btn-emerald:hover { background: #047857; }
.et-btn-blue    { background: #3b82f6; color: white; }
.et-btn-blue:hover    { background: #2563eb; }
.et-btn-blue:disabled { opacity: .5; cursor: not-allowed; }
.et-btn-gray    { background: #64748b; color: white; }
.et-btn-gray:hover    { background: #475569; }

/* ── DATE BANNER ── */
.et-banner {
    display: flex; align-items: center; justify-content: space-between;
    padding: 9px 16px;
    background: linear-gradient(135deg, #f5f3ff 0%, #eff6ff 100%);
    border-radius: 10px; border: 1px solid #c4b5fd; margin-bottom: 14px; font-size: 12px;
}
.et-banner-left { display: flex; align-items: center; gap: 8px; }
.et-banner-left svg { color: #7c3aed; width: 16px; height: 16px; flex-shrink: 0; }
.et-banner .lbl  { font-weight: 600; color: #374151; }
.et-banner .dstr { font-weight: 700; color: #6d28d9; }
.et-banner .pill { padding: 2px 9px; border-radius: 99px; font-size: 10px; font-weight: 700; letter-spacing: .4px; }
.et-banner .pill-day { background: #7c3aed; color: white; }
.et-banner .pill-ctr { background: #ede9fe; color: #6d28d9; }
.et-banner .saved { font-size: 11px; color: #64748b; }
.et-banner .saved strong { color: #374151; }

/* ── TABLE CARD ── */
.et-card {
    position: relative;
    background: white; border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    overflow-x: auto;
    overflow-y: auto;
}

/* ── TIMETABLE ── */
.edt-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 10px; }
.edt-table th, .edt-table td { border: 1px solid #e2e8f0; text-align: center; vertical-align: middle; }

.edt-table .day-hdr {
    color: white; font-weight: 700; font-size: 11px;
    padding: 7px 2px; letter-spacing: .6px;
}
.edt-day-lundi    { background: #3b82f6; }
.edt-day-mardi    { background: #8b5cf6; }
.edt-day-mercredi { background: #f97316; }
.edt-day-jeudi    { background: #f59e0b; }
.edt-day-vendredi { background: #22c55e; }
.edt-day-samedi   { background: #ec4899; }

.edt-table .slot-hdr {
    background: #f8fafc; font-weight: 700; padding: 4px 1px;
    font-size: 10px; color: #64748b; letter-spacing: .2px;
    border-bottom: 2px solid #e2e8f0;
}

.edt-corner { background: #0f172a; color: white; font-weight: 700; font-size: 10px; padding: 4px 2px; }

.edt-group-cell {
    background: #1e293b; color: white; font-weight: 700;
    font-size: 9px; padding: 2px;
    writing-mode: vertical-rl; text-orientation: mixed;
    transform: rotate(180deg); letter-spacing: .5px;
    width: 32px; max-width: 32px; overflow: hidden; white-space: nowrap;
}

.edt-type-cell { font-weight: 600; font-size: 9.5px; padding: 2px 5px; white-space: nowrap; text-align: left; }
.edt-type-formateur { background: #eff6ff; color: #1d4ed8; border-left: 3px solid #3b82f6 !important; }
.edt-type-module    { background: #fffbeb; color: #92400e; border-left: 3px solid #f59e0b !important; }
.edt-type-salle     { background: #f5f3ff; color: #5b21b6; border-left: 3px solid #8b5cf6 !important; }

.edt-data-cell { padding: 0; height: 26px; cursor: pointer; position: relative; transition: background .1s; }
.edt-data-cell:hover { background: #f0f9ff !important; }
.edt-filled-formateur { background: #dbeafe !important; }
.edt-filled-module    { background: #fef9c3 !important; }
.edt-filled-salle     { background: #ede9fe !important; }
.edt-filled-salle-teams { background: transparent !important; color: blue !important; }

.edt-select {
    width: 100%; height: 100%; border: none;
    font-size: 9px; font-weight: 600; padding: 0 2px;
    background: transparent; cursor: pointer;
    text-align: center; appearance: none; -webkit-appearance: none;
    color: #374151; font-family: inherit;
}
.edt-select:focus { outline: 2px solid #6366f1; outline-offset: -2px; background: white; }

.edt-select option {
    background-color: white !important;
    color: #374151 !important;
}
.edt-select option.edt-option-efm {
    background-color: #28a745 !important;
    color: white !important;
    font-weight: 700 !important;
}
.edt-select option.edt-option-efm:hover,
.edt-select option.edt-option-efm:checked {
    background-color: #1f7a33 !important;
    color: white !important;
}

.edt-col-group { width: 32px; }
.edt-col-type  { width: 56px; }

/* ── TOAST ── */
.et-toast {
    position: fixed; top: 18px; left: 50%;
    transform: translateX(-50%) translateY(0);
    z-index: 9999; padding: 11px 22px; border-radius: 99px;
    font-size: 13px; font-weight: 600; color: white;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
    min-width: 240px; text-align: center;
    transition: opacity .25s, transform .25s;
}
.et-toast-hidden  { opacity: 0; pointer-events: none; transform: translateX(-50%) translateY(-8px); }
.et-toast-success { background: linear-gradient(135deg, #10b981, #059669); }
.et-toast-error   { background: linear-gradient(135deg, #ef4444, #dc2626); }
</style>

{{-- Toast --}}
<div id="et-toast" class="et-toast et-toast-hidden"></div>

<script>
let _etTimer;
function etToast(msg, type = 'success') {
    const t = document.getElementById('et-toast');
    t.textContent = msg;
    t.className = 'et-toast et-toast-' + type;
    clearTimeout(_etTimer);
    _etTimer = setTimeout(() => {
        t.className = 'et-toast et-toast-' + type + ' et-toast-hidden';
    }, 3000);
}
</script>

<div x-data="groupeTimetable" x-init="init()" class="et-page">

    {{-- TOP BAR --}}
    <div class="et-topbar">
        <div class="et-title">
            <h1>Emploi du temps Groupe</h1>
            <p>Gestion des horaires par groupe et centre</p>
        </div>

        <div class="et-controls">

            {{-- Centre --}}
            <div class="et-ctrl-centre">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <select x-model="selectedCentre" @change="loadGroupesForCentre()" :disabled="Boolean(selectedGroupeId)">
                    <template x-for="centre in centres" :key="centre.id">
                        <option :value="centre.id" x-text="centre.short + ' — ' + centre.nom"></option>
                    </template>
                </select>
            </div>

            {{-- Date --}}
            <div class="et-ctrl-date">
                <div class="et-ctrl-date-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="date" x-model="selectedDate" @change="onDateChanged()">
                <div class="et-ctrl-date-divider"></div>
                <div class="et-ctrl-date-nav">
                    <button @click="navigateDate(-1)">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button class="et-ctrl-date-today" @click="goToToday()">Aujourd'hui</button>
                    <button @click="navigateDate(1)">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Status --}}
            <div class="et-status" :class="timetableExists ? 'et-status-ok' : 'et-status-new'">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path x-show="timetableExists" fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                          clip-rule="evenodd"/>
                    <path x-show="!timetableExists" fill-rule="evenodd"
                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                          clip-rule="evenodd"/>
                </svg>
                <span x-text="timetableExists ? 'Existant' : 'Nouveau'"></span>
            </div>

            {{-- Import --}}
            <label class="et-btn et-btn-green" style="cursor:pointer;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6H16a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Importer
                <input type="file" accept=".csv" @change="importCSV($event)" style="display:none;">
            </label>

            {{-- Export --}}
            <button @click="exportExcel()" class="et-btn et-btn-emerald">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exporter Excel
            </button>

            {{-- Save --}}
            <button @click="saveData()" :disabled="saving" class="et-btn et-btn-blue">
                <svg x-show="!saving" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <span x-text="saving ? 'Enregistrement...' : 'Enregistrer'"></span>
            </button>

            {{-- Reset --}}
            <button @click="clearAll()" class="et-btn et-btn-gray">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Réinitialiser
            </button>
        </div>
    </div>

    {{-- DATE BANNER --}}
    <div class="et-banner">
        <div class="et-banner-left">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="lbl">Emploi du temps du</span>
            <span class="dstr" x-text="formatDateDisplay(selectedDate)"></span>
            <span class="pill pill-day" x-text="getDayName(selectedDate)"></span>
            <template x-show="selectedCentre">
                <span class="pill pill-ctr" x-text="'Centre : ' + getCentreName(selectedCentre)"></span>
            </template>
        </div>
        <div x-show="lastSaved" class="saved">
            Dernière sauvegarde : <strong x-text="lastSaved"></strong>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="et-card">
        <table class="edt-table" id="timetableGrid">
            <thead>
                <tr>
                    <th class="edt-corner edt-col-group" rowspan="2" style="font-size:9px;">Groupe</th>
                    <th class="edt-corner edt-col-type"  rowspan="2" style="font-size:9px;">Type</th>
                    <th class="day-hdr edt-day-lundi"    colspan="4">Lundi</th>
                    <th class="day-hdr edt-day-mardi"    colspan="4">Mardi</th>
                    <th class="day-hdr edt-day-mercredi" colspan="4">Mercredi</th>
                    <th class="day-hdr edt-day-jeudi"    colspan="4">Jeudi</th>
                    <th class="day-hdr edt-day-vendredi" colspan="4">Vendredi</th>
                    <th class="day-hdr edt-day-samedi"   colspan="4">Samedi</th>
                </tr>
                <tr>
                    <template x-for="d in 6" :key="d">
                        <template x-for="s in 4" :key="s">
                            <th class="slot-hdr" x-text="'S'+s"></th>
                        </template>
                    </template>
                </tr>
            </thead>
            <tbody id="timetableBody">
            </tbody>
        </table>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('groupeTimetable', () => ({
        // Helper to determine session type from salle_id
        getTypeSession(salleId) {
            if (salleId === 'teams') return 'distance';
            if (salleId === 'efm') return 'efm';
            return 'presentiel';
        },

        selectedDate: '',
        selectedCentre: '',
        selectedGroupeId: null,
        timetableExists: false,
        timetableId: null,
        saving: false,
        lastSaved: null,

        centres: [],
        centresById: {},
        groupes: [],
        allGroupes: [],
        groupesByCentre: {},
        groupesById: {},
        loadedAllGroupes: false,
        modules: [],
        salles: [],
        filteredGroupes: [],
        /** @type {Record<string|number, Array<{value:number,text:string}>>} formateurs filtered per groupe */
        formateursByGroupe: {},
        formateursByGroupeCache: {},
        modulesByGroupeFormateur: {},
        allFormateurs: [],
        
        timetable: {},
        timetableCache: {},
        groupRows: {},
        tableBuilt: false,
        noGroupsRow: null,
        rowTypes: ['formateur', 'module', 'salle'],

        updateSelectColor(select) {
            if (select.value === 'efm') {
                select.style.backgroundColor = '#28a745';
                select.style.color = 'white';
                select.style.fontWeight = 'bold';
            } else {
                select.style.backgroundColor = '';
                select.style.color = '';
                select.style.fontWeight = '';
            }
        },

        todayLocal() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return today;
        },

        parseLocalDate(dateStr) {
            if (!dateStr || typeof dateStr !== 'string') return this.todayLocal();
            const [y, m, d] = dateStr.split('-').map(n => parseInt(n, 10));
            if (!y || !m || !d) return this.todayLocal();
            return new Date(y, m - 1, d);
        },

        formatLocalDate(dateObj) {
            const y = dateObj.getFullYear();
            const m = String(dateObj.getMonth() + 1).padStart(2, '0');
            const d = String(dateObj.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },

        getSelectedDayIndex() {
            const jsDay = this.parseLocalDate(this.selectedDate).getDay(); // 0..6
            if (jsDay >= 1 && jsDay <= 6) return jsDay - 1; // Mon..Sat => 0..5
            return 0; // Sunday fallback
        },

        getSelectedDayName() {
            const days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            return days[this.getSelectedDayIndex()] || 'Lundi';
        },

        async init() {
            this.selectedDate = this.formatLocalDate(this.todayLocal());
            const params = new URLSearchParams(window.location.search);
            const groupId = params.get('groupe_id') || params.get('group_id');
            const centreId = params.get('centre_id') || params.get('center_id');
            if (groupId) {
                this.selectedGroupeId = groupId;
            }
            if (centreId) {
                this.selectedCentre = centreId;
            }
            
            // Step 1: Load essential data first
            await Promise.all([
                this.loadCentres(),
                this.loadAllGroupes(),
            ]);
            
            // Step 2: Build table BEFORE filtering (empty structure)
            if (!this.tableBuilt && this.allGroupes.length > 0) {
                this.buildStaticTable();
            }
            
            // Step 3: Load filtered groups and update visibility
            await this.loadGroupes();
            
            // Step 4: Load remaining data in background
            Promise.all([
                this.loadModules(),
                this.loadSalles(),
                this.loadAllFormateurs(),
                this.loadAllTimetablesForCurrentDate(),
            ]);
        },

        async loadAllFormateurs() {
            try {
                const r = await fetch('/api/formateurs', { headers: { 'Accept': 'application/json' } });
                const j = await r.json();
                const list = j.data || j || [];
                this.allFormateurs = list.map(f => ({
                    value: f.id,
                    text: `${f.nom || ''} ${f.prenom || ''}`.trim() || `F${f.id}`,
                }));
            } catch (e) {
                console.error('loadAllFormateurs', e);
                this.allFormateurs = [];
            }
        },

        async loadCentres() {
            // Use API — same as centres page now
            try {
                const r = await fetch('/api/centres', { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                const raw = Array.isArray(d) ? d : (d.data || []);
                const byId = new Map();
                raw.forEach(c => {
                    if (!c || c.id == null || c.id === '') return;
                    const id = String(c.id);
                    if (!byId.has(id)) byId.set(id, c);
                });
                const mapped = Array.from(byId.values()).map(c => ({
                    id:    c.id,
                    nom:   c.nomCentre || c.nom || c.name || '',
                    short: (c.shortName || c.short || c.nomCourt || c.abbreviation || '').toUpperCase()
                })).sort((a, b) => (a.nom || '').localeCompare(b.nom || '', 'fr', { sensitivity: 'base' }));

                this.centres = mapped;
                this.centresById = mapped.reduce((acc, centre) => {
                    acc[String(centre.id)] = centre;
                    return acc;
                }, {});
            } catch(e) {
                console.error('Failed to load centres:', e);
                this.centres = [];
                this.centresById = {};
            }

            // Auto-select first centre by default
            if (this.centres.length > 0 && !this.selectedCentre) {
                this.selectedCentre = this.centres[0].id;
            }
        },

        async loadAllTimetablesForCurrentDate() {
            const date = this.selectedDate;
            if (!this.timetableCache[date]) {
                this.timetableCache[date] = {};
            }
            const centresToLoad = [...this.centres.map(c => c.id), 'all'];
            await Promise.all(centresToLoad.map(async (centreId) => {
                if (this.timetableCache[date][centreId]) return; // already cached
                try {
                    const params = new URLSearchParams({ date });
                    if (centreId !== 'all') {
                        params.set('centre_id', centreId);
                    }
                    const r = await fetch('/api/emploi-groupe/load?' + params, { headers: { 'Accept': 'application/json' } });
                    const payload = r.ok ? (await r.json()) : [];
                    const entries = Array.isArray(payload) ? payload : (payload.data || []);
                    this.timetableCache[date][centreId] = entries;
                } catch (e) {
                    console.error(`Failed to load timetable for centre ${centreId} date ${date}:`, e);
                    this.timetableCache[date][centreId] = [];
                }
            }));
        },

        buildStaticTable() {
            if (this.tableBuilt) return;
            if (this.allGroupes.length === 0) {
                // Defer building until groups are available
                console.log('buildStaticTable: no groups yet, will retry');
                return;
            }
            
            const tbody = document.getElementById('timetableBody');
            const rowLabels = { formateur: 'Formateur', module: 'Module', salle: 'EFP / SALLE' };
            const fragment = document.createDocumentFragment();
            this.groupRows = {};

            this.allGroupes.forEach((groupe) => {
                const groupId = String(groupe.id);
                const rowData = { trs: [], cells: { formateur: {}, module: {}, salle: {} } };

                this.rowTypes.forEach((rowType, rIdx) => {
                    const tr = document.createElement('tr');
                    if (rIdx === 0) {
                        const gc = document.createElement('td');
                        gc.className = 'edt-group-cell';
                        gc.rowSpan = 3;
                        gc.textContent = this.buildGroupLabel(groupe);
                        tr.appendChild(gc);
                    }

                    const tc = document.createElement('td');
                    tc.className = 'edt-type-cell edt-type-' + rowType;
                    tc.textContent = rowLabels[rowType];
                    tr.appendChild(tc);

                    for (let d = 0; d < 6; d++) {
                        for (let s = 0; s < 4; s++) {
                            const td = document.createElement('td');
                            td.className = 'edt-data-cell';

                            const sel = document.createElement('select');
                            sel.className = 'edt-select';
                            sel.dataset.groupeId = groupId;
                            sel.dataset.rowType = rowType;
                            sel.dataset.day = String(d);
                            sel.dataset.slot = String(s);

                            if (rowType === 'formateur') {
                                sel.onchange = async (e) => {
                                    const v = e.target.value;
                                    this.setCellValue(groupId, 'formateur', d, s, v);
                                    this.styleFilled(td, sel, 'formateur', v);
                                    await this.updateModuleOptionsForGroupeCell(groupId, d, s, v, { silent: false });
                                };
                            } else if (rowType === 'salle') {
                                sel.onchange = (e) => {
                                    const v = e.target.value;
                                    if (v && v !== 'teams') {
                                        this.clearSalleFromOtherGroupesForSlot(d, s, groupId, v);
                                    }
                                    this.setCellValue(groupId, 'salle', d, s, v);
                                    this.refreshSalleDropdownsForSlot(d, s);
                                };
                                this.updateSelectColor(sel);
                                sel.addEventListener('change', () => this.updateSelectColor(sel));
                            } else {
                                sel.onchange = (e) => {
                                    this.setCellValue(groupId, rowType, d, s, e.target.value);
                                    this.styleFilled(td, sel, rowType, e.target.value);
                                };
                            }

                            const empty = document.createElement('option');
                            empty.value = '';
                            sel.appendChild(empty);
                            td.appendChild(sel);
                            tr.appendChild(td);
                            rowData.cells[rowType][`${d}-${s}`] = { td, sel };
                        }
                    }

                    rowData.trs.push(tr);
                    fragment.appendChild(tr);
                });

                this.groupRows[groupId] = rowData;
            });

            const emptyRow = document.createElement('tr');
            emptyRow.style.display = 'none';
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = 26;
            emptyCell.style.cssText = 'padding:48px; text-align:center; color:#94a3b8; font-size:13px;';
            emptyCell.textContent = 'Aucun groupe trouvé.';
            emptyRow.appendChild(emptyCell);
            fragment.appendChild(emptyRow);
            this.noGroupsRow = emptyRow;

            tbody.appendChild(fragment);
            this.tableBuilt = true;
            this.updateRowVisibility();
        },

        updateRowVisibility() {
            // If filteredGroupes is empty, show all groups
            const displayGroups = this.filteredGroupes.length > 0 ? this.filteredGroupes : this.allGroupes;
            const visibleGroupIds = new Set(displayGroups.map(g => String(g.id)));
            let anyVisible = false;
            
            console.log('Updating row visibility for groups:', displayGroups.length);

            Object.keys(this.groupRows).forEach((groupId) => {
                const visible = visibleGroupIds.has(groupId);
                if (visible) anyVisible = true;
                this.groupRows[groupId].trs.forEach(tr => {
                    tr.style.display = visible ? '' : 'none';
                });
            });
            
            if (this.noGroupsRow) {
                this.noGroupsRow.style.display = anyVisible ? 'none' : '';
            }
        },

        updateTableValues() {
            return new Promise((resolve) => {
                if (!this.tableBuilt) {
                    resolve();
                    return;
                }
                // If filteredGroupes is empty, show all groups
                const displayGroups = this.filteredGroupes.length > 0 ? this.filteredGroupes : this.allGroupes;
                const visibleGroupIds = new Set(displayGroups.map(g => String(g.id)));

                // Get all groups to update progressively
                const groupsToUpdate = Object.entries(this.groupRows).filter(([groupId]) => 
                    visibleGroupIds.has(groupId)
                );

                // Update group labels immediately
                groupsToUpdate.forEach(([groupId, rowData]) => {
                    const group = this.groupesById[groupId] || this.allGroupes.find(g => String(g.id) === groupId) || { id: groupId };
                    const groupLabelCell = rowData.trs[0].querySelector('.edt-group-cell');
                    if (groupLabelCell) {
                        groupLabelCell.textContent = this.buildGroupLabel(group);
                    }
                });

                // Fill data progressively, row by row
                this.fillTableProgressively(groupsToUpdate, resolve);
            });
        },

        fillTableProgressively(groupsToUpdate, resolveCallback) {
            let groupIndex = 0;
            const fillNextGroup = () => {
                if (groupIndex >= groupsToUpdate.length) {
                    // All groups filled, now refresh module dropdowns
                    this.refreshAllModuleDropdownsGroupe().then(() => {
                        resolveCallback();
                    });
                    return;
                }

                const [groupId, rowData] = groupsToUpdate[groupIndex];
                groupIndex++;

                // Fill all cells for this group (silently)
                this.rowTypes.forEach((rowType) => {
                    Object.entries(rowData.cells[rowType]).forEach(([key, cellData]) => {
                        const [d, s] = key.split('-').map(Number);
                        const value = this.getCellValue(groupId, rowType, d, s) || '';
                        
                        if (rowType === 'formateur') {
                            this.updateFormateurSelectOptions(groupId, d, s, cellData.sel);
                        } else if (rowType === 'salle') {
                            this.updateSalleSelectOptions(groupId, d, s, cellData.sel);
                        }
                        
                        cellData.sel.value = value;
                        if (rowType === 'salle') {
                            this.updateSelectColor(cellData.sel);
                        } else {
                            this.styleFilled(cellData.td, cellData.sel, rowType, value);
                        }
                    });
                });

                // Schedule next group fill (completely silent)
                requestAnimationFrame(fillNextGroup);
            };

            // Start filling immediately and silently
            requestAnimationFrame(fillNextGroup);
        },

        updateFormateurSelectOptions(groupId, dayIdx, slotIdx, sel) {
            const list = this.formateursByGroupe[groupId] || [];
            const curF = String(this.getCellValue(groupId, 'formateur', dayIdx, slotIdx) || '');
            const options = list.length === 0 ? this.allFormateurs : list;
            sel.innerHTML = '';
            const empty = document.createElement('option');
            empty.value = '';
            sel.appendChild(empty);
            if (options.length === 0) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Aucun formateur disponible';
                placeholder.disabled = true;
                sel.appendChild(placeholder);
                return;
            }
            options.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.text;
                sel.appendChild(opt);
            });
            if (curF && !options.some(o => String(o.value) === curF)) {
                const found = this.allFormateurs.find(o => String(o.value) === curF);
                if (found) {
                    const opt = document.createElement('option');
                    opt.value = found.value;
                    opt.textContent = found.text;
                    sel.insertBefore(opt, sel.children[1] || null);
                }
            }
        },

        updateSalleSelectOptions(groupId, dayIdx, slotIdx, sel) {
            const options = this.getSalleOptionsForGroupeSlot(dayIdx, slotIdx, groupId);
            const currentValue = String(this.getCellValue(groupId, 'salle', dayIdx, slotIdx) || '');
            sel.innerHTML = '';
            const empty = document.createElement('option');
            empty.value = '';
            sel.appendChild(empty);
            options.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.text;
                opt.style.backgroundColor = 'white';
                opt.style.color = '#374151';
                if (o.value === 'teams') {
                    opt.style.color = 'blue';
                } else if (String(o.value).toLowerCase() === 'efm') {
                    opt.classList.add('edt-option-efm');
                }
                sel.appendChild(opt);
            });
            if (currentValue && options.some(o => String(o.value) === currentValue)) {
                sel.value = currentValue;
            } else if (currentValue) {
                this.setCellValue(groupId, 'salle', dayIdx, slotIdx, '');
                sel.value = '';
            }
        },

        refreshVisibleFormateurDropdowns() {
            Object.keys(this.groupRows).forEach(groupId => {
                if (!this.filteredGroupes.some(g => String(g.id) === groupId) && this.selectedCentre !== 'all') return;
                const rowData = this.groupRows[groupId];
                Object.entries(rowData.cells.formateur).forEach(([key, cellData]) => {
                    const [d, s] = key.split('-').map(Number);
                    this.updateFormateurSelectOptions(groupId, d, s, cellData.sel);
                    const val = this.getCellValue(groupId, 'formateur', d, s) || '';
                    cellData.sel.value = val;
                    this.styleFilled(cellData.td, cellData.sel, 'formateur', val);
                });
            });
        },

        renderTable() {
            if (!this.tableBuilt) {
                this.buildStaticTable();
            }
            this.updateRowVisibility();
            this.updateTableValues();
        },

        styleFilled(td, select, rowType, value) {
            td.classList.remove('edt-filled-formateur', 'edt-filled-module', 'edt-filled-salle', 'edt-filled-salle-teams', 'edt-filled-salle-efm');
            select.classList.remove('edt-filled-formateur', 'edt-filled-module', 'edt-filled-salle', 'edt-filled-salle-teams', 'edt-filled-salle-efm');
            select.style.color = '';
            select.style.backgroundColor = '';
            if (!value) return;
            if (rowType === 'salle' && value === 'teams') {
                td.classList.add('edt-filled-salle-teams');
            } else if (rowType === 'salle' && value === 'efm') {
                td.classList.remove('edt-filled-salle-teams', 'edt-filled-salle');
            } else {
                td.classList.add('edt-filled-' + rowType);
                select.classList.add('edt-filled-' + rowType);
            }
        },

        async loadGroupeById(groupId) {
            this.filteredGroupes = [];
            this.groupesById = {};

            if (this.loadedAllGroupes) {
                const groupe = this.allGroupes.find(g => String(g.id) === String(groupId));
                if (groupe) {
                    this.selectedCentre = groupe.centre_id ?? '';
                    this.filteredGroupes = [groupe];
                    this.groupesById = { [String(groupId)]: groupe };
                }
            }

            if (!this.filteredGroupes.length) {
                try {
                    const r = await fetch(`/api/groupes/${encodeURIComponent(groupId)}`, { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) throw new Error('Group not found');
                    const groupe = await r.json();
                    this.selectedCentre = groupe.centre_id ?? groupe.centre?.id ?? '';
                    const groupeData = {
                        ...groupe,
                        id: groupe.id,
                        centre_id: groupe.centre_id ?? groupe.centre?.id ?? null,
                        nomGroupe: groupe.nomGroupe || groupe.name || '',
                        code: groupe.code || groupe.codeGroupe || null,
                    };
                    this.filteredGroupes = [groupeData];
                    this.groupesById = { [String(groupId)]: groupeData };
                } catch (e) {
                    console.error('Failed to load groupe by id:', e);
                    this.filteredGroupes = [];
                    this.groupesById = {};
                }
            }

            await this.prefetchFormateursForGroupes();
            this.resetTimetable();
            await this.loadTimetableForDate();
        },

        async prefetchFormateursForGroupes() {
            this.formateursByGroupe = {};
            if (!this.filteredGroupes.length) return;

            const groupsToFetch = this.filteredGroupes.filter(g => !this.formateursByGroupeCache[g.id]);
            await Promise.all(groupsToFetch.map(async (g) => {
                try {
                    const r = await fetch(`/api/formateurs-for-groupe/${g.id}`, { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) throw new Error('formateurs-for-groupe');
                    const j = await r.json();
                    const data = j.data || [];
                    this.formateursByGroupeCache[g.id] = data.map(f => ({
                        value: f.id,
                        text: `${f.nom || ''} ${f.prenom || ''}`.trim() || `F${f.id}`,
                    }));
                } catch (e) {
                    console.error('prefetchFormateursForGroupe', g.id, e);
                    this.formateursByGroupeCache[g.id] = [];
                }
            }));

            this.filteredGroupes.forEach(g => {
                this.formateursByGroupe[g.id] = this.formateursByGroupeCache[g.id] || [];
            });
        },

        async updateModuleOptionsForGroupeCell(groupeId, dayIdx, slotIdx, formateurId, { silent = false } = {}) {
            const sel = document.querySelector(
                `#timetableBody select.edt-select[data-groupe-id="${groupeId}"][data-row-type="module"][data-day="${dayIdx}"][data-slot="${slotIdx}"]`
            );
            if (!sel) return;
            const td = sel.closest('td');
            sel.innerHTML = '';
            const empty = document.createElement('option');
            empty.value = '';
            sel.appendChild(empty);

            if (!formateurId) {
                const ph = document.createElement('option');
                ph.value = '';
                ph.disabled = true;
                ph.textContent = 'Sélectionnez un formateur d\'abord';
                sel.appendChild(ph);
                this.setCellValue(groupeId, 'module', dayIdx, slotIdx, '');
                this.styleFilled(td, sel, 'module', '');
                return;
            }

            const cacheKey = `${groupeId}-${formateurId}`;
            let data = this.modulesByGroupeFormateur[cacheKey];

            if (!data) {
                try {
                    const params = new URLSearchParams({ groupe_id: String(groupeId), formateur_id: String(formateurId) });
                    const r = await fetch('/api/modules-for-groupe-formateur?' + params, { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) throw new Error('modules-for-groupe-formateur');
                    const j = await r.json();
                    data = j.data || [];
                    this.modulesByGroupeFormateur[cacheKey] = data;
                } catch (e) {
                    console.error(e);
                    data = [];
                    this.modulesByGroupeFormateur[cacheKey] = data;
                    if (!silent) etToast('Erreur lors du chargement des modules', 'error');
                }
            }

            data.forEach(m => {
                const o = document.createElement('option');
                o.value = m.id;
                o.textContent = (m.codeModule || m.code_module || m.nomModule || m.intitule_module || `M${m.id}`).toString();
                sel.appendChild(o);
            });
            if (data.length === 0) {
                const ph = document.createElement('option');
                ph.value = '';
                ph.disabled = true;
                ph.textContent = 'Aucun module commun';
                sel.appendChild(ph);
                if (!silent) etToast('Aucun module commun entre ce groupe et ce formateur', 'error');
            }
            const cur = this.getCellValue(groupeId, 'module', dayIdx, slotIdx);
            if (cur && !data.some(m => String(m.id) === String(cur))) {
                this.setCellValue(groupeId, 'module', dayIdx, slotIdx, '');
            }
            sel.value = this.getCellValue(groupeId, 'module', dayIdx, slotIdx);
            this.styleFilled(td, sel, 'module', sel.value);
        },

        async refreshAllModuleDropdownsGroupe() {
            const tasks = [];
            this.filteredGroupes.forEach(g => {
                for (let d = 0; d < 6; d++) {
                    for (let s = 0; s < 4; s++) {
                        const fid = this.getCellValue(g.id, 'formateur', d, s);
                        if (fid) {
                            tasks.push({ groupeId: g.id, dayIdx: d, slotIdx: s, formateurId: fid });
                        }
                    }
                }
            });

            const chunkSize = 10;
            let index = 0;
            while (index < tasks.length) {
                const chunk = tasks.slice(index, index + chunkSize);
                await Promise.all(chunk.map(task =>
                    this.updateModuleOptionsForGroupeCell(task.groupeId, task.dayIdx, task.slotIdx, task.formateurId, { silent: true })
                ));
                index += chunkSize;
                await new Promise(resolve => requestAnimationFrame(resolve));
            }
        },

        async loadModules() {
            try {
                const r = await fetch('/api/modules');
                const d = await r.json();
                this.modules = d.data || d || [];
            } catch(e) { console.error(e); }
        },

        async loadSalles() {
            try {
                const r = await fetch('/api/salles');
                const d = await r.json();
                this.salles = (d.data || d || []).map(s => {
                    const centre = s.centre || {};
                    const short = (centre.shortName || centre.nomCourt || centre.short || '').toString().toUpperCase();
                    const name = (s.nomSalle || s.nom || s.name || `S${s.id}`).toString();
                    return {
                        id: s.id,
                        label: short ? `${short}/${name}` : name,
                    };
                });
            } catch(e) { console.error(e); }
        },

        async loadAllGroupes() {
            try {
                const params = new URLSearchParams({ all: '1' });
                const r = await fetch('/api/groupes?' + params, { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                const list = Array.isArray(d) ? d : (d.data || d || []);

                const normalized = [];
                const centreMap = { all: [] };
                list.forEach(g => {
                    if (!g || g.id == null) return;
                    const group = {
                        ...g,
                        id: g.id,
                        centre_id: g.centre_id ?? g.centre?.id ?? null,
                        nomGroupe: g.nomGroupe || g.name || '',
                        code: g.code || g.codeGroupe || null,
                    };
                    normalized.push(group);
                    const centreId = group.centre_id != null ? String(group.centre_id) : 'none';
                    if (!centreMap[centreId]) centreMap[centreId] = [];
                    centreMap[centreId].push(group);
                    centreMap.all.push(group);
                });

                this.allGroupes = normalized;
                this.groupesByCentre = centreMap;
                this.loadedAllGroupes = true;
            } catch (e) {
                console.error('Failed to load all groupes:', e);
                this.allGroupes = [];
                this.groupesByCentre = { all: [] };
                this.loadedAllGroupes = false;
            }
        },

        getOccupiedSalleIdsForGroupeSlot(dayIdx, slotIdx, excludeGroupeId) {
            const key = `${dayIdx}-${slotIdx}`;
            const ids = new Set();
            for (const gid of Object.keys(this.timetable || {})) {
                if (String(gid) === String(excludeGroupeId)) continue;
                const v = this.timetable[gid]?.salle?.[key];
                if (v != null && String(v).trim() !== '') ids.add(String(v));
            }
            return ids;
        },

        getSalleOptionsForGroupeSlot(dayIdx, slotIdx, groupeId) {
            const occupied = this.getOccupiedSalleIdsForGroupeSlot(dayIdx, slotIdx, groupeId);
            const salles = this.salles
                .filter(s => !occupied.has(String(s.id)))
                .map(s => ({ value: s.id, text: s.label }));
            
            // Add special options at the beginning
            return [
                { value: 'teams', text: 'Teams(Distance)' },
                { value: 'efm', text: 'EFM' },
                ...salles
            ];
        },

        clearSalleFromOtherGroupesForSlot(dayIdx, slotIdx, excludeGroupeId, salleId) {
            if (!salleId || salleId === 'teams' || salleId === 'efm') return; // Don't clear Teams or EFM as they are not physical salles
            const key = `${dayIdx}-${slotIdx}`;
            const sid = String(salleId);
            for (const gid of Object.keys(this.timetable || {})) {
                if (String(gid) === String(excludeGroupeId)) continue;
                const v = this.timetable[gid]?.salle?.[key];
                if (v != null && String(v) === sid) {
                    this.setCellValue(gid, 'salle', dayIdx, slotIdx, '');
                }
            }
        },

        rebuildSalleSelectForGroupeCell(groupeId, dayIdx, slotIdx) {
            const salleSelect = document.querySelector(
                `#timetableBody select.edt-select[data-groupe-id="${groupeId}"][data-row-type="salle"][data-day="${dayIdx}"][data-slot="${slotIdx}"]`
            );
            if (!salleSelect) return;

            const currentValue = String(this.getCellValue(groupeId, 'salle', dayIdx, slotIdx) || '');
            const opts = this.getSalleOptionsForGroupeSlot(dayIdx, slotIdx, groupeId);

            salleSelect.innerHTML = '';
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            salleSelect.appendChild(emptyOption);
            opts.forEach(o => {
                const opt = document.createElement('option');
                opt.value = String(o.value);
                opt.textContent = o.text;
                opt.style.backgroundColor = 'white';
                opt.style.color = '#374151';
                if (o.value === 'teams') {
                    opt.style.color = 'blue';
                } else if (String(o.value).toLowerCase() === 'efm') {
                    opt.classList.add('edt-option-efm');
                }
                salleSelect.appendChild(opt);
            });

            if (currentValue && opts.some(o => String(o.value) === currentValue)) {
                salleSelect.value = currentValue;
            } else if (currentValue) {
                this.setCellValue(groupeId, 'salle', dayIdx, slotIdx, '');
                salleSelect.value = '';
                etToast('La salle n\'est plus disponible sur ce créneau', 'error');
            } else {
                salleSelect.value = '';
            }
            // Apply select color based on final value
            this.updateSelectColor(salleSelect);
        },

        refreshSalleDropdownsForSlot(dayIdx, slotIdx) {
            this.filteredGroupes.forEach(g => {
                this.rebuildSalleSelectForGroupeCell(g.id, dayIdx, slotIdx);
            });
        },

        refreshSalleDropdowns() {
            for (let d = 0; d < 6; d++) {
                for (let s = 0; s < 4; s++) {
                    this.refreshSalleDropdownsForSlot(d, s);
                }
            }
        },

        async loadGroupes() {
            if (this.selectedGroupeId) {
                await this.loadGroupeById(this.selectedGroupeId);
            } else {
                await this.loadGroupesForCentre();
            }
            
            // Always update visibility and render after loading groups
            if (this.tableBuilt) {
                await this.renderTable();
            }
        },

        async loadGroupesForCentre() {
            const centreKey = this.selectedCentre ? String(this.selectedCentre) : 'all';
            this.selectedCentre = centreKey === 'all' ? 'all' : centreKey;
            this.filteredGroupes = [];
            this.groupesById = {};

            if (centreKey === 'all') {
                this.filteredGroupes = this.allGroupes.slice();
            } else {
                this.filteredGroupes = this.allGroupes.filter(g => String(g.centre_id) === centreKey);
            }

            console.log('loadGroupesForCentre selectedCentre:', this.selectedCentre, 'centreKey:', centreKey);
            console.log('Filtered groups count:', this.filteredGroupes.length, 'group ids:', this.filteredGroupes.map(g => g.id));

            this.groupesById = this.filteredGroupes.reduce((acc, groupe) => {
                acc[String(groupe.id)] = groupe;
                return acc;
            }, {});

            // Instant: update visibility immediately
            if (this.tableBuilt) {
                this.updateRowVisibility();
            }

            // Background: load formatours and timetable
            Promise.all([
                this.prefetchFormateursForGroupes(),
            ]).then(() => {
                this.resetTimetable();
                this.loadTimetableForDate();
            });
        },

        // ── Get centre short name by centre_id ───────────────────────────────
        getCentreShort(centreId) {
            const c = this.centresById[String(centreId)];
            return c ? (c.short || c.nom || '') : '';
        },

        // ── Build the full group label: CFMPS-GE101 ─────────────────────────
        buildGroupLabel(groupe) {
            // Return only group name without centre prefix
            const raw = (groupe.nomGroupe || groupe.name || ('G' + groupe.id)).toString();
            const clean = raw.split('/').pop().trim();
            return clean;
        },


        getCellValue(groupeId, rowType, dayIdx, slotIdx) {
            return this.timetable?.[groupeId]?.[rowType]?.[dayIdx + '-' + slotIdx] || '';
        },

        setCellValue(groupeId, rowType, dayIdx, slotIdx, value) {
            if (!this.timetable[groupeId]) this.timetable[groupeId] = { formateur: {}, module: {}, salle: {} };
            if (!this.timetable[groupeId][rowType]) this.timetable[groupeId][rowType] = {};
            this.timetable[groupeId][rowType][dayIdx + '-' + slotIdx] = value;
        },

        resetTimetable() {
            this.timetableExists = false; this.timetableId = null; this.lastSaved = null;
            this.timetable = {};
            this.filteredGroupes.forEach(g => {
                this.timetable[g.id] = { formateur: {}, module: {}, salle: {} };
            });
        },

        onDateChanged() {
            const selected = this.parseLocalDate(this.selectedDate);
            this.selectedDate = this.formatLocalDate(selected);
            
            // Instant: load from cache or use existing data
            this.loadTimetableForDate();
            
            // Background: preload if not cached
            if (!this.timetableCache[this.selectedDate]) {
                this.loadAllTimetablesForCurrentDate();
            }
        },

        async loadTimetableForDate() {
            const date = this.selectedDate;
            const centreKey = this.selectedCentre || 'all';
            const cachedEntries = this.timetableCache[date]?.[centreKey];
            if (!cachedEntries) {
                // If not cached, load it (though we preload, in case)
                await this.loadAllTimetablesForCurrentDate();
            }
            const entries = cachedEntries || this.timetableCache[date]?.[centreKey] || [];

            console.log('loadTimetableForDate', { date, centreKey, selectedCentre: this.selectedCentre, entriesCount: Array.isArray(entries) ? entries.length : 0 });

            this.resetTimetable();
            const weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            const allEntries = Array.isArray(entries) ? entries : [];
            const visibleEntries = [];
            if (this.selectedGroupeId) {
                const searchId = String(this.selectedGroupeId);
                allEntries.forEach(entry => {
                    if (String(entry.groupe_id) === searchId) visibleEntries.push(entry);
                });
            } else {
                visibleEntries.push(...allEntries);
            }

            // Ensure groups from entries exist in filteredGroupes and populate timetable values
            visibleEntries.forEach(entry => {
                const gid = entry.groupe_id;
                if (gid == null) return;

                const already = Boolean(this.groupesById[String(gid)]);
                if (!already) {
                    const g = entry.groupe;
                    const groupeData = {
                        id: g ? g.id : gid,
                        nomGroupe: g ? (g.nomGroupe || g.name || ('Groupe ' + gid)) : ('Groupe ' + gid),
                        centre_id: g ? g.centre_id : null,
                    };
                    // Only add if matches selected centre, or if all centres are selected
                    if (this.selectedCentre === 'all' || !this.selectedCentre || String(groupeData.centre_id) === String(this.selectedCentre)) {
                        this.filteredGroupes.push(groupeData);
                        this.groupesById[String(groupeData.id)] = groupeData;
                    }
                }
            });

            visibleEntries.forEach(entry => {
                const dayName = entry.jour || '';
                const dayIdx = weekDays.indexOf(dayName);
                if (dayIdx === -1) return;

                const creneauRaw = String(entry.creneau || entry.slot || '').trim();
                const slotNumber = parseInt(creneauRaw.replace(/\D/g, ''), 10);
                const slotIdx = Number.isNaN(slotNumber) ? -1 : slotNumber - 1;
                if (slotIdx < 0 || slotIdx >= 4) return;

                const gid = entry.groupe_id;
                if (!this.timetable[gid]) {
                    this.timetable[gid] = { formateur: {}, module: {}, salle: {} };
                }

                this.setCellValue(gid, 'formateur', dayIdx, slotIdx,
                    entry.formateur_id != null && entry.formateur_id !== '' ? String(entry.formateur_id) : '');
                this.setCellValue(gid, 'module', dayIdx, slotIdx,
                    entry.module_id != null && entry.module_id !== '' ? String(entry.module_id) : '');

                const salleValue = String(entry.type_session || '').toLowerCase() === 'distance'
                    ? 'teams'
                    : String(entry.type_session || '').toLowerCase() === 'efm'
                    ? 'efm'
                    : (entry.salle_id ? String(entry.salle_id) : '');
                this.setCellValue(gid, 'salle', dayIdx, slotIdx, salleValue);
            });

            this.timetableExists = allEntries.length > 0;
            await this.renderTable();
            await this.refreshSalleDropdowns();
        },

        async saveData() {
            if (this.saving) return;
            this.saving = true;
            try {
                const selectedDayIndex = this.getSelectedDayIndex();
                const selectedDayName = this.getSelectedDayName();
                const entriesMap = {};

                for (const groupeId in this.timetable) {
                    for (const type in this.timetable[groupeId]) {
                        for (const daySlotKey in this.timetable[groupeId][type]) {
                            const value = this.timetable[groupeId][type][daySlotKey];
                            if (!value) continue;

                            const [dayIdxStr, slotIdxStr] = daySlotKey.split('-');
                            const dayIndex = parseInt(dayIdxStr, 10);
                            const slotIndex = parseInt(slotIdxStr, 10);
                            if (Number.isNaN(dayIndex) || Number.isNaN(slotIndex)) continue;
                            if (dayIndex < 0 || dayIndex > 5 || slotIndex < 0 || slotIndex > 3) continue;

                            const jour = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'][dayIndex] || '';
                            if (!jour) continue;
                            const creneau = `S${slotIndex + 1}`;

                            const entryKey = `${groupeId}-${jour}-${creneau}`;
                            if (!entriesMap[entryKey]) {
                                entriesMap[entryKey] = {
                                    groupe_id: groupeId,
                                    formateur_id: null,
                                    module_id: null,
                                    salle_id: null,
                                    jour,
                                    creneau,
                                    date: this.selectedDate
                                };
                            }

                            if (type === 'formateur') {
                                entriesMap[entryKey].formateur_id = value;
                            } else if (type === 'module') {
                                entriesMap[entryKey].module_id = value;
                            } else if (type === 'salle') {
                                const typeSession = this.getTypeSession(value);
                                entriesMap[entryKey].salle_id = (typeSession === 'distance' || typeSession === 'efm') ? null : value;
                                entriesMap[entryKey].type_session = typeSession;
                            }
                        }
                    }
                }

                const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) headers['X-CSRF-TOKEN'] = meta.content;

                const payload = {
                    date: this.selectedDate,
                    entries: Object.values(entriesMap)
                };
                if (this.selectedCentre && this.selectedCentre !== 'all') {
                    payload.centre_id = Number(this.selectedCentre);
                }

                const r = await fetch('/api/emploi-groupe/save', { 
                    method: 'POST', 
                    headers, 
                    body: JSON.stringify(payload) 
                });
                
                if (!r.ok) {
                    const text = await r.text();
                    throw new Error(text || 'Save failed');
                }
                
                await r.json();
                this.timetableExists = true;
                this.lastSaved = this.formatDateTime(new Date().toISOString());
                // Invalidate exactly the selected centre cache and current date cache
                if (this.timetableCache[this.selectedDate]) {
                    delete this.timetableCache[this.selectedDate][this.selectedCentre || 'all'];
                    delete this.timetableCache[this.selectedDate]['all'];
                }
                await this.loadTimetableForDate();
                console.log('Timetable after save:', this.timetable);
                etToast('Emploi du temps enregistré avec succès!', 'success');
            } catch(e) {
                console.error(e);
                etToast(e.message || "Erreur lors de l'enregistrement", 'error');
            } finally { this.saving = false; }
        },

        navigateDate(days) {
            const d = this.parseLocalDate(this.selectedDate);
            d.setDate(d.getDate() + days);
            this.selectedDate = this.formatLocalDate(d);
            // Clear cache for new date
            if (this.timetableCache[this.selectedDate]) {
                delete this.timetableCache[this.selectedDate];
            }
            this.loadTimetableForDate();
        },

        goToToday() {
            this.selectedDate = this.formatLocalDate(this.todayLocal());
            // Clear cache for new date
            if (this.timetableCache[this.selectedDate]) {
                delete this.timetableCache[this.selectedDate];
            }
            this.loadTimetableForDate();
        },

        formatDateDisplay(dateStr) {
            if (!dateStr) return '';
            return this.parseLocalDate(dateStr).toLocaleDateString('fr-FR', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        },

        getDayName(dateStr) {
            if (!dateStr) return '';
            return this.parseLocalDate(dateStr)
                .toLocaleDateString('fr-FR', { weekday: 'long' })
                .toUpperCase();
        },

        formatDateTime(str) {
            if (!str) return '';
            return new Date(str).toLocaleString('fr-FR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        },

        getCentreName(id) {
            if (id === 'all') {
                return 'Tous les centres';
            }
            const c = this.centresById[String(id)];
            return c ? c.nom : '';
        },

        buildGroupLabel(groupe) {
            const raw = (groupe.nomGroupe || groupe.name || ('G' + groupe.id)).toString();
            const clean = raw.split('/').pop().trim();
            if (this.selectedCentre === 'all') {
                const centreId = groupe.centre_id ?? (groupe.centre ? groupe.centre.id : null);
                if (centreId != null) {
                    const centre = this.centresById[String(centreId)];
                    const prefix = centre ? (centre.short || centre.nom || '').toString().toUpperCase() : '';
                    if (prefix) {
                        return `${prefix} / ${clean}`;
                    }
                }
            }
            return clean;
        },

        clearAll() {
            if (!confirm("Effacer tout l'emploi du temps pour cette date ?")) return;
            this.resetTimetable();
            this.renderTable();

            // Send empty entries to delete all for this centre and date
            const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) headers['X-CSRF-TOKEN'] = meta.content;

            const payload = {
                date: this.selectedDate,
                entries: [] // empty to delete all
            };

            if (this.selectedCentre && this.selectedCentre !== 'all') {
                payload.centre_id = Number(this.selectedCentre);
            }

            fetch('/api/emploi-groupe/save', {
                method: 'POST',
                headers,
                body: JSON.stringify(payload)
            }).then(r => {
                if (!r.ok) throw new Error('Clear failed');
                return r.json();
            }).then(() => {
                this.timetableExists = false;
                this.updateStatusBadge();
                etToast('Emploi du temps réinitialisé et supprimé de la base de données', 'success');
            }).catch(e => {
                console.error(e);
                etToast('Erreur lors de la réinitialisation', 'error');
            });
        },

        exportCSV() {
            const header = ['Groupe', 'Type'];
            ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'].forEach(day => {
                for (let s = 1; s <= 4; s++) header.push(`${day} S${s}`);
            });
            const rows = [header.join(',')];
            this.filteredGroupes.forEach(g => {
                this.rowTypes.forEach(rt => {
                    const row = [`"${this.buildGroupLabel(g)}"`, rt];
                    for (let d = 0; d < 6; d++)
                        for (let s = 0; s < 4; s++)
                            row.push(`"${this.getCellValue(g.id, rt, d, s) || ''}"`);
                    rows.push(row.join(','));
                });
            });
            const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `emploi_groupe_${this.selectedDate}.csv`;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
        },

        exportExcel() {
            if (!this.selectedCentre) {
                return etToast('Sélectionnez un centre avant d\'exporter.', 'error');
            }

            const params = new URLSearchParams({
                type: 'centre',
                date: this.selectedDate,
                centre_id: this.selectedCentre,
            });

            // Include groupe_ids from filtered groups
            if (this.filteredGroupes.length > 0) {
                params.append('groupe_ids', this.filteredGroupes.map(g => g.id).join(','));
            }

            // Include groupe_id if a specific group is selected (from URL parameter)
            if (this.selectedGroupeId) {
                params.append('groupe_id', this.selectedGroupeId);
            }

            window.location.href = `/api/timetable-export?${params.toString()}`;
        },

        importCSV(event) {
            const file = event.target.files[0]; if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const lines = e.target.result.split('\n'); let imported = 0;
                    for (let i = 1; i < lines.length; i++) {
                        const line = lines[i].trim(); if (!line) continue;
                        const vals = (line.match(/(".*?"|[^,]+)(?=\s*,|\s*$)/g) || [])
                            .map(v => v.replace(/^"(.*)"$/, '$1'));
                        if (vals.length < 26) continue;
                        const groupe = this.filteredGroupes.find(g => this.buildGroupLabel(g) === vals[0]);
                        if (!groupe) continue;
                        const rt = vals[1];
                        if (!this.timetable[groupe.id]) this.timetable[groupe.id] = { formateur:{}, module:{}, salle:{} };
                        if (!this.timetable[groupe.id][rt]) this.timetable[groupe.id][rt] = {};
                        let col = 2;
                        for (let d = 0; d < 6; d++)
                            for (let s = 0; s < 4; s++) {
                                if (vals[col]) this.timetable[groupe.id][rt][`${d}-${s}`] = vals[col];
                                col++;
                            }
                        imported++;
                    }
                    this.renderTable();
                    etToast(`Import réussi : ${imported} lignes importées !`, 'success');
                    event.target.value = '';
                } catch(err) { etToast("Erreur lors de l'import du CSV", 'error'); }
            };
            reader.readAsText(file);
        },
    }));
});

// Add real-time sync on window focus
window.addEventListener('focus', () => {
    if (window.globalTimetable && window.globalTimetable.loadTimetableForDate) {
        window.globalTimetable.loadTimetableForDate();
    }
});
</script>

@endsection