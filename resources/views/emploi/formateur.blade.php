@extends('layouts.app')

@section('content')

{{-- Success Toast (reusable) --}}
<div id="toast" style="display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999; min-width:300px; padding:12px 20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); font-size:14px; font-weight:500; text-align:center;">
</div>

<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.backgroundColor = type === 'success' ? '#22c55e' : '#ef4444';
    toast.style.color = 'white';
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
}
</script>

<div>
    {{-- Header --}}
    <div class="px-3 pt-2 pb-3">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Emploi du temps Formateur</h1>
                    <p class="text-xs text-gray-500">Gestion des horaires et salles pour les formateurs</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Date Picker --}}
                <div class="flex items-center gap-2 px-3 py-1.5 bg-white border-2 border-blue-500 rounded-lg shadow-sm">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input 
                        type="date" 
                        id="selectedDate"
                        onchange="window.formateurTimetable.onDateChanged()"
                        class="text-sm font-medium text-gray-700 border-none focus:ring-0 focus:outline-none bg-transparent cursor-pointer"
                        style="width: 140px;">
                    <div class="flex gap-1 ml-2 border-l pl-2">
                        <button onclick="window.formateurTimetable.navigateDate(-1)" class="p-1 hover:bg-gray-100 rounded transition-colors" title="Semaine précédente">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button onclick="window.formateurTimetable.goToToday()" class="px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded transition-colors">
                            Aujourd'hui
                        </button>
                        <button onclick="window.formateurTimetable.navigateDate(1)" class="p-1 hover:bg-gray-100 rounded transition-colors" title="Semaine suivante">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Status Indicator --}}
                <div id="statusBadge" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span id="statusText">Nouveau</span>
                </div>

                {{-- Import --}}
                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors text-xs font-medium cursor-pointer" title="Importer un fichier CSV">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Importer
                    <input type="file" accept=".csv" class="hidden" onchange="window.formateurTimetable.importCSV(event)">
                </label>

                {{-- Export --}}
                <button onclick="window.formateurTimetable.exportExcel()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition-colors text-xs font-medium" title="Exporter en Excel">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter Excel
                </button>

                {{-- Save --}}
                <button id="saveBtn" onclick="window.formateurTimetable.saveData()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors text-xs font-medium" title="Enregistrer l'emploi du temps">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span id="saveBtnText">Enregistrer</span>
                </button>

                {{-- Reset --}}
                <button onclick="window.formateurTimetable.clearAll()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors text-xs font-medium" title="Réinitialiser l'emploi du temps">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Réinitialiser
                </button>
            </div>
        </div>

        {{-- Date Display --}}
        <div class="mt-3 px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-800">Emploi du temps du</span>
                        <span id="dateDisplay" class="text-sm font-bold text-blue-700"></span>
                    </div>
                    <span id="dayName" class="px-2 py-0.5 bg-blue-600 text-white text-xs font-medium rounded-full"></span>
                </div>
                <div id="lastSavedDisplay" style="display:none;" class="text-xs text-gray-600">
                    <span>Dernière sauvegarde: </span>
                    <span id="lastSavedTime" class="font-medium"></span>
                </div>
            </div>
        </div>

        {{-- Formateur Filter --}}
        <div class="mt-3 px-4 py-2 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-sm font-semibold text-gray-800">Filtrer par formateur:</span>
                <select id="formateurFilter" onchange="window.formateurTimetable.onFormateurFilterChanged()" class="px-3 py-1.5 bg-white border border-green-300 rounded-md text-sm font-medium text-gray-700 focus:ring-2 focus:ring-green-500 focus:border-green-500" :disabled="window.formateurTimetable.loading">
                    <option value="all">Tous les formateurs</option>
                </select>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="mt-3 px-4 py-2 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border border-purple-200">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="text-sm font-semibold text-gray-800">Rechercher:</span>
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Tapez un nom de formateur ou groupe..."
                    oninput="window.formateurTimetable.onSearchInput()"
                    class="flex-1 px-3 py-1.5 bg-white border border-purple-300 rounded-md text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
        </div>
    </div>

    {{-- Timetable --}}
    <div class="px-2 relative">
        {{-- Loading Overlay --}}
        <div id="loadingOverlay" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 hidden">
            <div class="flex flex-col items-center gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-sm font-medium text-gray-700">Chargement de l'emploi du temps...</span>
            </div>
        </div>

        <style>
            .edt-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 10px; }
            .edt-table th, .edt-table td { border: 1px solid #d1d5db; text-align: center; vertical-align: middle; }
            .edt-table .day-header { color: white; font-weight: 700; font-size: 11px; padding: 6px 2px; letter-spacing: 0.5px; }
            .edt-day-lundi { background-color: #3b82f6; }
            .edt-day-mardi { background-color: #8b5cf6; }
            .edt-day-mercredi { background-color: #f97316; }
            .edt-day-jeudi { background-color: #f59e0b; }
            .edt-day-vendredi { background-color: #22c55e; }
            .edt-day-samedi { background-color: #ec4899; }
            .edt-table .slot-header { background: #f3f4f6; font-weight: 600; padding: 3px 1px; font-size: 10px; color: #374151; }
            .edt-table .trainer-cell { width: 170px; min-width: 170px; max-width: 170px; background: #1e293b; color: white; font-weight: 700; font-size: 10px; padding: 6px 8px; vertical-align: middle; text-align: left; white-space: normal; word-break: break-word; overflow-wrap: break-word; }
            .edt-table .type-cell { background: #e2e8f0; font-weight: 600; font-size: 9px; color: #475569; padding: 1px 2px; white-space: nowrap; }
            .edt-table .data-cell { padding: 0; height: 28px; cursor: pointer; position: relative; transition: background-color 0.1s; }
            .edt-table .data-cell:hover { background-color: #eff6ff; }
            .edt-cell-group { background-color: #dbeafe; color: #1e40af; }
            .edt-cell-module { background-color: #fef3c7; color: #92400e; }
            .edt-cell-salle { background-color: #e0e7ff; color: #3730a3; }
            .edt-cell-salle-teams { background-color: transparent; color: blue; }
            .custom-dropdown { position: relative; z-index: 2; }
            .custom-dropdown .dropdown-display {
                color: #1f2937;
                background-color: #ffffff;
                opacity: 1;
                border: 1px solid #cbd5e1;
                font-weight: 600;
                justify-content: flex-start;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .edt-cell-salle .dropdown-display {
                color: #312e81;
                background-color: #eef2ff;
            }
            .custom-dropdown .dropdown-options {
                z-index: 2000;
                opacity: 1;
                top: calc(100% + 2px);
                left: 0;
                padding: 4px 0;
                min-width: 180px;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                background-color: #ffffff;
                box-shadow: 0 12px 24px rgba(15, 23, 42, 0.16);
            }
            .custom-dropdown .dropdown-option {
                color: #1f2937;
                background-color: #ffffff;
                font-size: 11px;
                line-height: 1.25;
                padding: 6px 10px;
                white-space: nowrap;
            }
            .custom-dropdown .dropdown-option:hover {
                background-color: #eef2ff;
                color: #1e3a8a;
            }
            .custom-dropdown .dropdown-option.dropdown-option-empty {
                color: #64748b;
                font-style: italic;
            }
            .edt-select { width: 100%; height: 100%; border: none; font-size: 9px; padding: 0 1px; background: transparent; cursor: pointer; text-align: center; appearance: none; -webkit-appearance: none; }
            .edt-select:focus { outline: 2px solid #3b82f6; outline-offset: -1px; background: white; }
            .edt-table .formateur-col { width: 170px; min-width: 170px; max-width: 170px; }
            .edt-table .type-col { width: 42px; }
        </style>

        <table class="edt-table" id="timetableGrid">
            <thead>
                <tr>
                    <th class="bg-slate-800 text-white day-header formateur-col" rowspan="2" style="writing-mode: horizontal-tb; font-size: 9px; padding: 4px 2px;">Form.</th>
                    <th class="bg-slate-800 text-white day-header type-col" rowspan="2" style="writing-mode: horizontal-tb; font-size: 9px;">Type</th>
                    <th class="day-header edt-day-lundi" colspan="4">Lundi</th>
                    <th class="day-header edt-day-mardi" colspan="4">Mardi</th>
                    <th class="day-header edt-day-mercredi" colspan="4">Mercredi</th>
                    <th class="day-header edt-day-jeudi" colspan="4">Jeudi</th>
                    <th class="day-header edt-day-vendredi" colspan="4">Vendredi</th>
                    <th class="day-header edt-day-samedi" colspan="4">Samedi</th>
                </tr>
                <tr>
                    <th class="slot-header">S1</th><th class="slot-header">S2</th><th class="slot-header">S3</th><th class="slot-header">S4</th>
                    <th class="slot-header">S1</th><th class="slot-header">S2</th><th class="slot-header">S3</th><th class="slot-header">S4</th>
                    <th class="slot-header">S1</th><th class="slot-header">S2</th><th class="slot-header">S3</th><th class="slot-header">S4</th>
                    <th class="slot-header">S1</th><th class="slot-header">S2</th><th class="slot-header">S3</th><th class="slot-header">S4</th>
                    <th class="slot-header">S1</th><th class="slot-header">S2</th><th class="slot-header">S3</th><th class="slot-header">S4</th>
                    <th class="slot-header">S1</th><th class="slot-header">S2</th><th class="slot-header">S3</th><th class="slot-header">S4</th>
                </tr>
            </thead>
            <tbody id="timetableBody">
                {{-- Will be populated by JavaScript --}}
            </tbody>
        </table>
    </div>
</div>

<script>
// Global timetable object
window.formateurTimetable = {
    // Helper to determine session type from salle_id
    getTypeSession(salleId) {
        if (salleId === 'teams') return 'distance';
        if (salleId === 'efm') return 'efm';
        return 'presentiel';
    },

    selectedDate: '',
    selectedFormateur: 'all', // Add formateur filter
    searchTerm: '', // Add search term
    timetableExists: false,
    timetableId: null,
    lastSaved: null,
    timetable: {},
    timetableCache: {}, // Shared cache for all data
    loading: false, // Loading state
    searchDebounceTimer: null,
    
    // data from database; populated by API calls
    trainers: [],
    /** @type {Record<string, Array<{id:number,label:string}>>} groupes filtered per formateur (cascade) */
    trainerGroups: {},
    allGroups: [],
    salles: [],   // {id,label}
    modules: [],  // {id,label}
    /** labels learned from intersection API (for CSV export) */
    moduleLabelById: {},
    rowTypes: ['group', 'module', 'salle'],

    todayLocal: function() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return today;
    },

    parseLocalDate: function(dateStr) {
        if (!dateStr || typeof dateStr !== 'string') return this.todayLocal();
        const [y, m, d] = dateStr.split('-').map(n => parseInt(n, 10));
        if (!y || !m || !d) return this.todayLocal();
        return new Date(y, m - 1, d);
    },

    formatLocalDate: function(dateObj) {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    },

    getSelectedDayIndex: function() {
        const jsDay = this.parseLocalDate(this.selectedDate).getDay();
        if (jsDay >= 1 && jsDay <= 6) return jsDay - 1;
        return 0;
    },

    getSelectedDayName: function() {
        const days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        return days[this.getSelectedDayIndex()] || 'Lundi';
    },
    
    init: async function() {
        this.selectedDate = this.formatLocalDate(this.todayLocal());
        document.getElementById('selectedDate').value = this.selectedDate;
        try {
            // fetch reference data before rendering
            await Promise.all([
                this.loadTrainers(),
                this.loadSalles(),
                this.loadModules(),
                this.loadAllGroups(),
            ]);
            await this.loadFilteredGroupsForAllTrainers();
            this.renderTable();
            await this.loadTimetableForDate();
            this.updateDateDisplay();
        } catch (e) {
            console.error('Formateur timetable init failed:', e);
            this.trainers = this.trainers || [];
            this.renderTable();
            this.updateDateDisplay();
            showToast('Impossible de charger la page emploi formateur.', 'error');
        }
    },

    onDateChanged: function() {
        this.selectedDate = this.formatLocalDate(this.parseLocalDate(document.getElementById('selectedDate').value));
        document.getElementById('selectedDate').value = this.selectedDate;
        this.loadTimetableForDate();
    },

    onFormateurFilterChanged: function() {
        this.selectedFormateur = document.getElementById('formateurFilter').value;
        this.filterAndRenderTimetable();
    },

    onSearchInput: function() {
        this.searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        if (this.searchDebounceTimer) {
            clearTimeout(this.searchDebounceTimer);
        }
        this.searchDebounceTimer = setTimeout(() => {
            this.filterAndRenderTimetable();
        }, 200);
    },

    filterAndRenderTimetable: function() {
        // Filter the cached data based on selectedFormateur and searchTerm
        const allEntries = Array.isArray(this.timetableCache[this.selectedDate]) ? this.timetableCache[this.selectedDate].slice() : [];
        const sortedEntries = allEntries.sort((a, b) => {
            const compare = (x, y) => {
                if (x === y) return 0;
                if (x === null || x === undefined) return 1;
                if (y === null || y === undefined) return -1;
                return String(x).localeCompare(String(y), 'fr', { numeric: true });
            };
            let result = compare(a.formateur_id, b.formateur_id);
            if (result !== 0) return result;
            result = compare(a.groupe_id, b.groupe_id);
            if (result !== 0) return result;
            result = compare(a.jour, b.jour);
            if (result !== 0) return result;
            return compare(a.creneau, b.creneau);
        });
        
        let filteredEntries = this.selectedFormateur === 'all' 
            ? sortedEntries 
            : sortedEntries.filter(entry => String(entry.formateur_id) === String(this.selectedFormateur));
        
        if (this.searchTerm) {
            filteredEntries = filteredEntries.filter(entry => {
                const trainer = this.trainers.find(t => String(t.id) === String(entry.formateur_id));
                const group = this.allGroups.find(g => String(g.id) === String(entry.groupe_id));
                const trainerName = trainer ? (trainer.name || '').toLowerCase() : '';
                const groupName = group ? (group.label || '').toLowerCase() : '';
                return trainerName.includes(this.searchTerm) || groupName.includes(this.searchTerm);
            });
        }
        
        // Populate timetable with filtered entries
        this.resetTimetable();
        filteredEntries.forEach(entry => {
            if (String(entry.date || '').slice(0, 10) !== this.selectedDate) return;
            const dayIndex = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'].indexOf(entry.jour || '');
            const slotIndex = parseInt(entry.creneau?.substring(1) || '', 10) - 1;
            
            if (dayIndex >= 0 && slotIndex >= 0) {
                const trainerId = entry.formateur_id;
                if (!this.timetable[trainerId]) {
                    this.timetable[trainerId] = { group: {}, module: {}, salle: {} };
                }
                this.setCellValue(trainerId, 'group', dayIndex, slotIndex, entry.groupe_id ? String(entry.groupe_id) : '');
                this.setCellValue(trainerId, 'module', dayIndex, slotIndex, entry.module_id ? String(entry.module_id) : '');
                const salleValue = entry.type_session === 'distance' ? 'teams' : entry.type_session === 'efm' ? 'efm' : (entry.salle_id ? String(entry.salle_id) : '');
                this.setCellValue(trainerId, 'salle', dayIndex, slotIndex, salleValue);
            }
        });
        
        // Set timetableExists
        this.timetableExists = filteredEntries.length > 0;
        
        this.renderTable();
        this.refreshAllModuleDropdownsFormateur();
        this.refreshSalleDropdowns();
        this.updateStatusBadge();
    },

    getOccupiedSalleIdsForSlot: function(dayIdx, slotIdx, excludeTrainerId) {
        const key = `${dayIdx}-${slotIdx}`;
        const ids = new Set();
        Object.keys(this.timetable || {}).forEach(tid => {
            if (String(tid) === String(excludeTrainerId)) return;
            const salleId = this.timetable[tid]?.salle?.[key];
            if (salleId) ids.add(String(salleId));
        });
        return ids;
    },

    getSalleOptionsForSlot: function(dayIdx, slotIdx, trainerId) {
        const occupied = this.getOccupiedSalleIdsForSlot(dayIdx, slotIdx, trainerId);
        const salles = this.salles.filter(s => !occupied.has(String(s.id)));
        
        // Add special options at the beginning
        return [
            { id: 'teams', label: ' Teams(Distance)' },
            { id: 'efm', label: 'EFM' },
            ...salles
        ];
    },

    clearSalleFromOtherTrainersForSlot: function(dayIdx, slotIdx, excludeTrainerId, salleId) {
        if (!salleId || salleId === 'teams' || salleId === 'efm') return; // Don't clear Teams or EFM as they are not physical salles
        const key = `${dayIdx}-${slotIdx}`;
        Object.keys(this.timetable || {}).forEach(tid => {
            if (String(tid) === String(excludeTrainerId)) return;
            if (String(this.timetable[tid]?.salle?.[key] || '') === String(salleId)) {
                this.setCellValue(tid, 'salle', dayIdx, slotIdx, '');
            }
        });
    },

    refreshSalleDropdownsForSlot: function(dayIdx, slotIdx) {
        this.trainers.forEach(trainer => {
            const select = document.querySelector(
                `#timetableBody select.edt-select[data-trainer-id="${trainer.id}"][data-row-type="salle"][data-day="${dayIdx}"][data-slot="${slotIdx}"]`
            );
            if (!select) return;

            const currentValue = String(this.getCellValue(trainer.id, 'salle', dayIdx, slotIdx) || '');
            const options = this.getSalleOptionsForSlot(dayIdx, slotIdx, trainer.id);
            select.innerHTML = '';

            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            select.appendChild(emptyOption);

            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = String(opt.id);
                option.textContent = opt.label;
                if (opt.id === 'teams') {
                    option.style.color = 'blue';
                } else if (opt.id === 'efm') {
                    option.style.backgroundColor = '#22c55e';
                    option.style.color = 'white';
                    option.style.fontWeight = '700';
                }
                select.appendChild(option);
            });

            const td = select.closest('td');
            if (currentValue && options.some(x => String(x.id) === currentValue)) {
                select.value = currentValue;
                this.updateCellStyling(td, select, 'salle', currentValue);
            } else {
                this.setCellValue(trainer.id, 'salle', dayIdx, slotIdx, '');
                select.value = '';
                this.updateCellStyling(td, select, 'salle', '');
            }
        });
    },

    refreshSalleDropdowns: function() {
        for (let d = 0; d < 6; d++) {
            for (let s = 0; s < 4; s++) {
                this.refreshSalleDropdownsForSlot(d, s);
            }
        }
    },

    createCustomSalleDropdown: function(trainerId, day, slot, options, currentValue) {
        const container = document.createElement('div');
        container.className = 'custom-dropdown';
        container.dataset.trainerId = String(trainerId);
        container.dataset.rowType = 'salle';
        container.dataset.day = String(day);
        container.dataset.slot = String(slot);
        container.style.position = 'relative';
        container.style.width = '100%';
        
        const display = document.createElement('div');
        display.className = 'dropdown-display';
        display.style.cursor = 'pointer';
        display.style.minHeight = '28px';
        display.style.height = '28px';
        display.style.padding = '2px 4px';
        display.style.border = '1px solid #d1d5db';
        display.style.borderRadius = '4px';
        display.style.backgroundColor = 'white';
        display.style.display = 'flex';
        display.style.alignItems = 'center';
        display.style.width = '100%';
        
        const updateDisplay = (value) => {
            display.innerHTML = '';
            if (!value) {
                display.textContent = '';
                display.style.backgroundColor = 'white';
                display.style.color = '#1f2937';
                display.style.borderRadius = '';
                display.style.padding = '2px 4px';
                return;
            }
            
            if (value === 'efm') {
                display.textContent = 'EFM';
                display.style.color = 'white';
                display.style.backgroundColor = '#22c55e';
                display.style.borderRadius = '4px';
                display.style.padding = '2px 4px';
            } else if (value === 'teams') {
                display.textContent = ' Teams(Distance)';
                display.style.color = 'blue';
                display.style.backgroundColor = 'transparent';
                display.style.borderRadius = '4px';
                display.style.padding = '2px 4px';
            } else {
                const salle = this.salles.find(s => String(s.id) === String(value));
                display.textContent = salle ? salle.label : value;
                display.style.color = '#1f2937';
                display.style.backgroundColor = 'white';
                display.style.borderRadius = '4px';
                display.style.padding = '2px 4px';
            }
        };
        
        updateDisplay(currentValue);
        
        const dropdown = document.createElement('div');
        dropdown.className = 'dropdown-options';
        dropdown.style.display = 'none';
        dropdown.style.position = 'absolute';
        dropdown.style.top = 'calc(100% + 2px)';
        dropdown.style.left = '0';
        dropdown.style.backgroundColor = 'white';
        dropdown.style.border = '1px solid #cbd5e1';
        dropdown.style.borderRadius = '8px';
        dropdown.style.boxShadow = '0 12px 24px rgba(15, 23, 42, 0.16)';
        dropdown.style.zIndex = '1000';
        dropdown.style.maxHeight = '220px';
        dropdown.style.overflowY = 'auto';
        dropdown.style.padding = '4px 0';
        dropdown.style.minWidth = '180px';
        
        // Empty option
        const emptyOption = document.createElement('div');
        emptyOption.className = 'dropdown-option';
        emptyOption.dataset.value = '';
        emptyOption.textContent = 'Effacer la salle';
        emptyOption.classList.add('dropdown-option-empty');
        emptyOption.style.padding = '6px 10px';
        emptyOption.style.cursor = 'pointer';
        emptyOption.style.backgroundColor = 'white';
        emptyOption.onmouseover = () => emptyOption.style.backgroundColor = '#f3f4f6';
        emptyOption.onmouseout = () => emptyOption.style.backgroundColor = 'white';
        emptyOption.onclick = () => {
            const v = '';
            this.setCellValue(trainerId, 'salle', day, slot, v);
            updateDisplay(v);
            this.updateCellStyling(container.parentElement, display, 'salle', v);
            dropdown.style.display = 'none';
            if (v && v !== 'teams' && v !== 'efm') {
                this.clearSalleFromOtherTrainersForSlot(day, slot, trainerId, v);
            }
            this.refreshSalleDropdownsForSlot(day, slot);
        };
        dropdown.appendChild(emptyOption);
        
        options.forEach(opt => {
            const option = document.createElement('div');
            option.className = 'dropdown-option';
            option.dataset.value = String(opt.id);
            option.style.padding = '6px 10px';
            option.style.cursor = 'pointer';
            option.style.backgroundColor = 'white';
            option.onmouseover = () => option.style.backgroundColor = '#f3f4f6';
            option.onmouseout = () => option.style.backgroundColor = 'white';
            
            if (opt.id === 'efm') {
                option.textContent = 'EFM';
                option.style.color = 'white';
                option.style.backgroundColor = '#22c55e';
            } else if (opt.id === 'teams') {
                option.textContent = opt.label;
                option.style.color = 'blue';
            } else {
                option.textContent = opt.label;
            }
            
            option.onclick = () => {
                const v = String(opt.id);
                this.setCellValue(trainerId, 'salle', day, slot, v);
                updateDisplay(v);
                this.updateCellStyling(container.parentElement, display, 'salle', v);
                dropdown.style.display = 'none';
                if (v && v !== 'teams' && v !== 'efm') {
                    this.clearSalleFromOtherTrainersForSlot(day, slot, trainerId, v);
                }
                this.refreshSalleDropdownsForSlot(day, slot);
            };
            dropdown.appendChild(option);
        });
        
        display.onclick = () => {
            const isVisible = dropdown.style.display === 'block';
            // Hide all other dropdowns
            document.querySelectorAll('.dropdown-options').forEach(d => d.style.display = 'none');
            dropdown.style.display = isVisible ? 'none' : 'block';
        };
        
        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
        
        container.appendChild(display);
        container.appendChild(dropdown);
        
        return container;
    },

    renderTable: function() {
        const tbody = document.getElementById('timetableBody');
        tbody.innerHTML = '';
        
        // Filter trainers based on selected formateur
        let filteredTrainers = this.selectedFormateur === 'all' 
            ? this.trainers 
            : this.trainers.filter(t => String(t.id) === this.selectedFormateur);
        
        // Apply search filter if there's a search term
        if (this.searchTerm) {
            filteredTrainers = filteredTrainers.filter(trainer => {
                const trainerName = trainer.name.toLowerCase();
                // Check if trainer name matches
                if (trainerName.includes(this.searchTerm)) return true;
                
                // Check if any of their groups match the search
                return this.trainerGroups[trainer.id]?.some(group => 
                    group.label.toLowerCase().includes(this.searchTerm)
                );
            });
        }
        
        if (!filteredTrainers.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 26;
            td.className = 'p-3 text-sm text-gray-500';
            td.textContent = this.selectedFormateur === 'all' && !this.searchTerm
                ? 'Aucun formateur trouvé. Veuillez vérifier vos données et recharger la page.'
                : 'Aucun formateur trouvé pour ce filtre.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        filteredTrainers.forEach(trainer => {
            this.rowTypes.forEach((rowType, rIdx) => {
                const tr = document.createElement('tr');
                
                if (rIdx === 0) {
                    const trainerCell = document.createElement('td');
                    trainerCell.className = 'trainer-cell';
                    trainerCell.rowSpan = 3;
                    trainerCell.textContent = trainer.name;
                    tr.appendChild(trainerCell);
                }
                
                const typeCell = document.createElement('td');
                typeCell.className = 'type-cell';
                typeCell.textContent = rowType === 'group' ? 'Groupe' : rowType === 'module' ? 'Module' : 'Salle';
                tr.appendChild(typeCell);
                
                for (let d = 0; d < 6; d++) {
                    for (let s = 0; s < 4; s++) {
                        const td = document.createElement('td');
                        td.className = 'data-cell';
                        
                        const select = document.createElement('select');
                        select.className = 'edt-select';
                        select.dataset.trainerId = String(trainer.id);
                        select.dataset.rowType = rowType;
                        select.dataset.day = String(d);
                        select.dataset.slot = String(s);
                        
                        
                        if (rowType === 'group') {
                            select.onchange = async (e) => {
                                const v = e.target.value;
                                this.setCellValue(trainer.id, 'group', d, s, v);
                                this.updateCellStyling(td, select, 'group', v);
                                await this.updateModuleOptionsForTrainerCell(trainer.id, d, s, v, { silent: false });
                            };
                        } else if (rowType === 'salle') {
                            select.onchange = (e) => {
                                const v = e.target.value;
                                this.setCellValue(trainer.id, 'salle', d, s, v);
                                this.updateCellStyling(td, select, 'salle', v);
                                if (v && v !== 'teams' && v !== 'efm') {
                                    this.clearSalleFromOtherTrainersForSlot(d, s, trainer.id, v);
                                }
                                this.refreshSalleDropdownsForSlot(d, s);
                            };
                        } else {
                            select.onchange = (e) => {
                                this.setCellValue(trainer.id, rowType, d, s, e.target.value);
                                this.updateCellStyling(td, select, rowType, e.target.value);
                            };
                        }

                        const emptyOption = document.createElement('option');
                        emptyOption.value = '';
                        select.appendChild(emptyOption);

                        let options = [];
                        if (rowType === 'group') {
                            options = this.trainerGroups[trainer.id] || [];
                            const currentGroupId = this.getCellValue(trainer.id, 'group', d, s);
                            if (currentGroupId && !options.some(opt => String(opt.id) === String(currentGroupId))) {
                                options = options.concat([{ id: currentGroupId, label: this.findGroupLabelById(currentGroupId) }]);
                            }
                            if (options.length === 0) {
                                const ph = document.createElement('option');
                                ph.value = '';
                                ph.disabled = true;
                                ph.textContent = 'Aucun groupe disponible';
                                select.appendChild(ph);
                            }
                        } else if (rowType === 'module') {
                            const selectedGroupId = this.getCellValue(trainer.id, 'group', d, s);
                            if (!selectedGroupId) {
                                const ph = document.createElement('option');
                                ph.value = '';
                                ph.disabled = true;
                                ph.textContent = 'Sélectionnez un groupe d\'abord';
                                select.appendChild(ph);
                            }
                            options = [];
                        } else if (rowType === 'salle') {
                            options = this.getSalleOptionsForSlot(d, s, trainer.id);
                        }

                        options.forEach(opt => {
                            const option = document.createElement('option');
                            option.value = String(opt.id);
                            option.textContent = opt.label;
                            if (opt.id === 'teams') {
                                option.style.color = 'blue';
                            } else if (opt.id === 'efm') {
                                option.style.backgroundColor = '#22c55e';
                                option.style.color = 'white';
                                option.style.fontWeight = '700';
                            }
                            select.appendChild(option);
                        });

                        const value = this.getCellValue(trainer.id, rowType, d, s);
                        select.value = value;
                        this.updateCellStyling(td, select, rowType, value);
                        
                        td.appendChild(select);
                        tr.appendChild(td);
                    }
                }
                
                tbody.appendChild(tr);
            });
        });
    },
    
    updateCellStyling: function(td, element, rowType, value) {
        td.classList.remove('edt-cell-group', 'edt-cell-module', 'edt-cell-salle', 'edt-cell-salle-teams', 'edt-cell-salle-efm');
        element.classList.remove('edt-cell-group', 'edt-cell-module', 'edt-cell-salle', 'edt-cell-salle-teams', 'edt-cell-salle-efm');
        element.style.color = '';
        element.style.backgroundColor = '';
        
        if (!value) {
            return;
        }

        const className = `edt-cell-${rowType}`;
        td.classList.add(className);
        element.classList.add(className);

        if (rowType === 'salle' && value === 'teams') {
            td.classList.add('edt-cell-salle-teams');
            element.classList.remove('edt-cell-salle');
            element.classList.add('edt-cell-salle-teams');
            element.style.color = 'blue';
            element.style.backgroundColor = 'transparent';
        } else if (rowType === 'salle' && value === 'efm') {
            td.classList.remove('edt-cell-salle-teams', 'edt-cell-salle');
            element.classList.remove('edt-cell-salle', 'edt-cell-salle-teams', 'edt-cell-salle-efm');
            element.style.color = 'white';
            element.style.backgroundColor = '#22c55e';
            element.style.borderRadius = '4px';
            element.style.padding = '2px 4px';
        }
    },
    
    getCellKey: function(dayIdx, slotIdx) {
        return dayIdx + '-' + slotIdx;
    },
    
    getCellValue: function(trainerId, rowType, dayIdx, slotIdx) {
        const key = this.getCellKey(dayIdx, slotIdx);
        if (this.timetable[trainerId] && this.timetable[trainerId][rowType] && this.timetable[trainerId][rowType][key]) {
            return this.timetable[trainerId][rowType][key];
        }
        return '';
    },
    
    setCellValue: function(trainerId, rowType, dayIdx, slotIdx, value) {
        const key = this.getCellKey(dayIdx, slotIdx);
        if (!this.timetable[trainerId]) {
            this.timetable[trainerId] = { group: {}, module: {}, salle: {} };
        }
        if (!this.timetable[trainerId][rowType]) {
            this.timetable[trainerId][rowType] = {};
        }
        this.timetable[trainerId][rowType][key] = value;
    },

    /* loaders */
    async loadTrainers() {
        try {
            const r = await fetch('/api/formateurs', { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error('Failed to load trainers: ' + r.status);
            const list = await r.json();
            const arr = (list.data || list || []).map(f => ({
                id: f.id,
                name: `${f.nom || ''} ${f.prenom || ''}`.trim() || `#${f.id}`,
                modules: (f.modules || []).map(m => m.codeModule || m.nomModule || `#${m.id}`),
            }));
            this.trainers = arr;
            
            // Populate formateur filter dropdown
            const filterSelect = document.getElementById('formateurFilter');
            filterSelect.innerHTML = '<option value="all">Tous les formateurs</option>';
            this.trainers.forEach(trainer => {
                const option = document.createElement('option');
                option.value = trainer.id;
                option.textContent = trainer.name;
                filterSelect.appendChild(option);
            });
        } catch (e) {
            console.error('Failed to load trainers:', e);
            this.trainers = [];
        }
    },

    async loadFilteredGroupsForAllTrainers() {
        this.trainerGroups = {};
        if (!this.trainers.length) return;
        await Promise.all(this.trainers.map(async (t) => {
            try {
                const r = await fetch(`/api/groupes-for-formateur/${t.id}`, { headers: { 'Accept': 'application/json' } });
                if (!r.ok) throw new Error('groupes-for-formateur');
                const j = await r.json();
                const data = j.data || [];
                this.trainerGroups[t.id] = data.map(g => {
                    const rawName = (g.nomGroupe || g.name || `G${g.id}`).toString();
                    const cleanName = rawName.split('/').pop().trim();
                    return { id: g.id, label: cleanName };
                });
            } catch (e) {
                console.error('loadFilteredGroupsForTrainer', t.id, e);
                this.trainerGroups[t.id] = [];
            }
        }));
    },

    async loadAllGroups() {
        try {
            const r = await fetch('/api/groupes?all=1', { headers: { 'Accept': 'application/json' } });
            const j = await r.json();
            const data = j.data || j || [];
            this.allGroups = data.map(g => {
                const rawName = (g.nomGroupe || g.name || `G${g.id}`).toString();
                const cleanName = rawName.split('/').pop().trim();
                return { id: g.id, label: cleanName };
            });
        } catch (e) {
            console.error('loadAllGroups', e);
            this.allGroups = [];
        }
    },

    findGroupLabelById(groupeId) {
        if (!groupeId) return '';
        const ag = (this.allGroups || []).find(x => String(x.id) === String(groupeId));
        if (ag) return ag.label;
        for (const tid of Object.keys(this.trainerGroups)) {
            const g = this.trainerGroups[tid].find(x => String(x.id) === String(groupeId));
            if (g) return g.label;
        }
        return String(groupeId);
    },

    getModuleDisplayLabel(moduleId) {
        if (!moduleId) return '';
        const m = this.modules.find(x => String(x.id) === String(moduleId));
        if (m) return m.label;
        return this.moduleLabelById[moduleId] || String(moduleId);
    },

    async updateModuleOptionsForTrainerCell(trainerId, dayIdx, slotIdx, groupeId, { silent = false } = {}) {
        const sel = document.querySelector(
            `#timetableBody select.edt-select[data-trainer-id="${trainerId}"][data-row-type="module"][data-day="${dayIdx}"][data-slot="${slotIdx}"]`
        );
        if (!sel) return;
        const td = sel.closest('td');
        sel.innerHTML = '';
        const empty = document.createElement('option');
        empty.value = '';
        sel.appendChild(empty);

        const currentModuleId = this.getCellValue(trainerId, 'module', dayIdx, slotIdx);

        if (!groupeId) {
            if (currentModuleId) {
                const label = this.getModuleDisplayLabel(currentModuleId) || `Module ${currentModuleId}`;
                const option = document.createElement('option');
                option.value = String(currentModuleId);
                option.textContent = label;
                sel.appendChild(option);
                sel.value = String(currentModuleId);
            } else {
                const ph = document.createElement('option');
                ph.value = '';
                ph.disabled = true;
                ph.textContent = 'Sélectionnez un groupe d\'abord';
                sel.appendChild(ph);
            }
            this.updateCellStyling(td, sel, 'module', sel.value);
            return;
        }

        try {
            const params = new URLSearchParams({ formateur_id: String(trainerId), groupe_id: String(groupeId) });
            const r = await fetch('/api/modules-for-formateur-groupe?' + params, { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error('modules-for-formateur-groupe');
            const j = await r.json();
            const data = j.data || [];
            data.forEach(m => {
                const label = (m.codeModule || m.code_module || m.nomModule || m.intitule_module || `M${m.id}`).toString();
                this.moduleLabelById[m.id] = label;
                const o = document.createElement('option');
                o.value = String(m.id);
                o.textContent = label;
                sel.appendChild(o);
            });
            if (data.length === 0) {
                const ph = document.createElement('option');
                ph.value = '';
                ph.disabled = true;
                ph.textContent = 'Aucun module (liez modules au groupe ou au formateur)';
                sel.appendChild(ph);
                if (!silent) {
                    showToast('Aucun module disponible : assignez des modules au groupe ou au formateur.', 'error');
                }
            }
            if (currentModuleId && !data.some(m => String(m.id) === String(currentModuleId))) {
                const label = this.getModuleDisplayLabel(currentModuleId) || `Module ${currentModuleId}`;
                const option = document.createElement('option');
                option.value = String(currentModuleId);
                option.textContent = label;
                sel.appendChild(option);
            }
            sel.value = currentModuleId || '';
            this.updateCellStyling(td, sel, 'module', sel.value);
        } catch (e) {
            console.error(e);
            if (!silent) showToast('Erreur lors du chargement des modules', 'error');
        }
    },

    async refreshAllModuleDropdownsFormateur() {
        for (const trainer of this.trainers) {
            for (let d = 0; d < 6; d++) {
                for (let s = 0; s < 4; s++) {
                    const gid = this.getCellValue(trainer.id, 'group', d, s);
                    if (gid) {
                        await this.updateModuleOptionsForTrainerCell(trainer.id, d, s, gid, { silent: true });
                    } else {
                        await this.updateModuleOptionsForTrainerCell(trainer.id, d, s, '', { silent: true });
                    }
                }
            }
        }
    },

    async loadSalles() {
        try {
            const r = await fetch('/api/salles', { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error('Failed to load salles: ' + r.status);
            const list = await r.json();
            this.salles = (list.data || list || []).map(s => {
                const centre = s.centre || {};
                const short = (centre.shortName || centre.nomCourt || centre.short || '').toString().toUpperCase();
                const name = (s.nomSalle || s.nom || s.name || `S${s.id}`).toString();
                return { id: s.id, label: short ? `${short}/${name}` : name };
            });
        } catch (e) {
            console.error('Failed to load salles:', e);
            this.salles = [];
        }
    },

    async loadModules() {
        try {
            const r = await fetch('/api/modules', { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error('Failed to load modules: ' + r.status);
            const list = await r.json();
            this.modules = (list.data || list || []).map(m => ({
                id: m.id,
                label: (m.codeModule || m.code || m.nomModule || m.intitule || `M${m.id}`).toString()
            }));
        } catch (e) {
            console.error('Failed to load modules:', e);
            this.modules = [];
        }
    },
    
    resetTimetable: function() {
        this.timetableExists = false;
        this.timetableId = null;
        this.lastSaved = null;
        this.timetable = {};
        
        this.trainers.forEach(t => {
            this.timetable[t.id] = { group: {}, module: {}, salle: {} };
        });
        
        this.updateStatusBadge();
        document.getElementById('lastSavedDisplay').style.display = 'none';
    },
    
    updateLoadingOverlay: function() {
        const overlay = document.getElementById('loadingOverlay');
        const formateurFilter = document.getElementById('formateurFilter');
        const searchInput = document.getElementById('searchInput');
        if (this.loading) {
            overlay.classList.remove('hidden');
            formateurFilter.disabled = true;
            searchInput.disabled = true;
        } else {
            overlay.classList.add('hidden');
            formateurFilter.disabled = false;
            searchInput.disabled = false;
        }
    },
    
    updateDateDisplay: function() {
        const date = this.parseLocalDate(this.selectedDate);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('dateDisplay').textContent = date.toLocaleDateString('fr-FR', options);
        document.getElementById('dayName').textContent = date.toLocaleDateString('fr-FR', { weekday: 'long' }).toUpperCase();
    },
    
    async loadTimetableForDate({ forceRefresh = false } = {}) {
        this.selectedDate = document.getElementById('selectedDate').value;
        this.selectedDate = this.formatLocalDate(this.parseLocalDate(this.selectedDate));
        document.getElementById('selectedDate').value = this.selectedDate;
        this.updateDateDisplay();

        const hasCache = Array.isArray(this.timetableCache[this.selectedDate]);
        if (!hasCache || forceRefresh) {
            this.loading = true;
            this.updateLoadingOverlay();
        }

        try {
            if (!hasCache || forceRefresh) {
                const response = await fetch(`/api/emploi-groupe/load?date=${this.selectedDate}`, { headers: { 'Accept': 'application/json' } });
                if (response.ok) {
                    const payload = await response.json();
                    this.timetableCache[this.selectedDate] = Array.isArray(payload) ? payload : (payload.data || []);
                } else {
                    this.timetableCache[this.selectedDate] = [];
                }
                this.filterAndRenderTimetable();
                showToast('Emploi du temps chargé', 'success');
            } else {
                this.filterAndRenderTimetable();
                this.revalidateTimetableForDate({ silent: true });
            }
        } catch (error) {
            console.error('Error loading timetable:', error);
            this.resetTimetable();
            this.renderTable();
            this.updateStatusBadge();
        } finally {
            if (!hasCache || forceRefresh) {
                this.loading = false;
                this.updateLoadingOverlay();
            }
        }
    },
    
    async saveData() {
        if (this.saving) return;
        this.saving = true;

        const saveBtn = document.getElementById('saveBtn');
        const saveBtnText = document.getElementById('saveBtnText');
        
        saveBtn.disabled = true;
        saveBtnText.textContent = 'Enregistrement...';
        
        try {
            // Collect all filled cells
            const entries = [];
            const selectedDayIndex = this.getSelectedDayIndex();
            const selectedDayName = this.getSelectedDayName();
            
            // For each trainer
            for (const trainerId in this.timetable) {
                const trainer = this.trainers.find(t => t.id == trainerId);
                if (!trainer) continue;
                
                // For each day (0-5)
                for (let dayIndex = 0; dayIndex < 6; dayIndex++) {
                    // For each slot (0-3)
                    for (let slotIndex = 0; slotIndex < 4; slotIndex++) {
                        const groupeId = this.timetable[trainerId]['group']?.[dayIndex + '-' + slotIndex];
                        const moduleId = this.timetable[trainerId]['module']?.[dayIndex + '-' + slotIndex];
                        let salleId = this.timetable[trainerId]['salle']?.[dayIndex + '-' + slotIndex];
                        
                        if (groupeId || moduleId || salleId) {
                            // Handle special selections
                            const typeSession = this.getTypeSession(salleId);
                            const finalSalleId = (typeSession === 'distance' || typeSession === 'efm') ? null : salleId;

                            entries.push({
                                formateur_id: trainerId,
                                groupe_id: groupeId || null,
                                module_id: moduleId || null,
                                salle_id: finalSalleId,
                                type_session: typeSession,
                                jour: ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'][dayIndex] || selectedDayName,
                                creneau: 'S' + (slotIndex + 1),
                                date: this.selectedDate
                            });
                        }
                    }
                }
            }
            
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta) {
                headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
            }
            
            const payload = {
                date: this.selectedDate,
                entries: entries,
                formateur_ids: this.trainers.map(t => t.id)
            };
            
            console.log('Saving payload:', payload);
            
            const response = await fetch('/api/timetable-formateurs', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(payload)
            });
            
            if (response.ok) {
                const data = await response.json();
                this.timetableExists = true;
                this.lastSaved = new Date().toISOString();
                
                if (this.lastSaved) {
                    const lastSavedDate = new Date(this.lastSaved);
                    document.getElementById('lastSavedTime').textContent = lastSavedDate.toLocaleString('fr-FR');
                    document.getElementById('lastSavedDisplay').style.display = 'block';
                }
                
                this.updateStatusBadge();
                // Optimistic update: update local cache with saved entries for instant UI
                this.timetableCache[this.selectedDate] = entries.map(entry => ({
                    ...entry,
                    formateur_id: entry.formateur_id,
                    groupe_id: entry.groupe_id,
                    module_id: entry.module_id,
                    salle_id: entry.salle_id,
                    jour: entry.jour,
                    creneau: entry.creneau,
                    date: entry.date,
                    type_session: entry.type_session
                }));
                this.filterAndRenderTimetable();
                showToast('Emploi du temps enregistré avec succès !', 'success');

                // Silent revalidation: fetch fresh data from server to ensure consistency (stale-while-revalidate)
                setTimeout(() => this.revalidateTimetableForDate({ silent: true }), 200); // Small delay to allow UI to settle
            } else {
                const errorData = await response.json();
                throw new Error(errorData.message || errorData.error || 'Failed to save');
            }
        } catch (error) {
            console.error('Error saving:', error);
            showToast(error.message || 'Erreur lors de l\'enregistrement', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtnText.textContent = 'Enregistrer';
            this.saving = false;
        }
    },

    async revalidateTimetableForDate({ silent = false } = {}) {
        if (!this.selectedDate) return;
        if (!Array.isArray(this.timetableCache[this.selectedDate])) {
            return this.loadTimetableForDate({ forceRefresh: true });
        }

        try {
            const response = await fetch(`/api/emploi-groupe/load?date=${this.selectedDate}`, { headers: { 'Accept': 'application/json' } });
            if (response.ok) {
                const payload = await response.json();
                const freshData = Array.isArray(payload) ? payload : (payload.data || []);
                this.timetableCache[this.selectedDate] = freshData;
                this.filterAndRenderTimetable();
                if (!silent) showToast('Emploi du temps synchronisé', 'success');
            }
        } catch (e) {
            if (!silent) console.error('Revalidation failed:', e);
        }
    },
    
    exportCSV: function() {
        const rows = [];
        const header = ['Formateur', 'Type'];
        
        for (let d = 0; d < 6; d++) {
            const dayNames = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            for (let s = 1; s <= 4; s++) {
                header.push(dayNames[d] + ' S' + s);
            }
        }
        rows.push(header.join(','));
        
        this.trainers.forEach(trainer => {
            this.rowTypes.forEach(rowType => {
                const row = ['"' + trainer.name + '"', rowType];
                for (let d = 0; d < 6; d++) {
                    for (let s = 0; s < 4; s++) {
                        const val = this.getCellValue(trainer.id, rowType, d, s);
                        let displayVal = '';
                        if (val) {
                            if (rowType === 'group') {
                                displayVal = this.findGroupLabelById(val);
                            } else if (rowType === 'module') {
                                displayVal = this.getModuleDisplayLabel(val);
                            } else if (rowType === 'salle') {
                                const salle = this.salles.find(s => s.id == val);
                                displayVal = salle ? salle.label : val;
                            }
                        }
                        row.push('"' + displayVal + '"');
                    }
                }
                rows.push(row.join(','));
            });
        });
        
        const csvContent = '\uFEFF' + rows.join('\n'); // Add BOM for UTF-8
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'emploi_formateur_' + this.selectedDate + '.csv';
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showToast('Export CSV réussi !', 'success');
    },

    exportExcel: function() {
        if (!this.selectedDate) {
            showToast('Sélectionnez une date avant d\'exporter.', 'error');
            return;
        }

        const params = new URLSearchParams({
            type: 'formateur',
            date: this.selectedDate,
        });

        window.location.href = `/api/timetable-export?${params.toString()}`;
    },
    
    importCSV: function(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const text = e.target.result;
                const lines = text.split('\n');
                let imported = 0;
                
                for (let i = 1; i < lines.length; i++) {
                    const line = lines[i].trim();
                    if (!line) continue;
                    
                    const values = [];
                    const regex = /(".*?"|[^,]+)(?=\s*,|\s*$)/g;
                    let match;
                    while ((match = regex.exec(line)) !== null) {
                        values.push(match[1].replace(/^"(.*)"$/, '$1'));
                    }
                    
                    if (values.length < 26) continue;
                    
                    const trainerName = values[0];
                    const rowType = values[1];
                    
                    const trainer = this.trainers.find(t => t.name === trainerName);
                    if (!trainer) continue;
                    
                    if (!this.timetable[trainer.id]) {
                        this.timetable[trainer.id] = { group: {}, module: {}, salle: {} };
                    }
                    if (!this.timetable[trainer.id][rowType]) {
                        this.timetable[trainer.id][rowType] = {};
                    }
                    
                    let colIdx = 2;
                    for (let d = 0; d < 6; d++) {
                        for (let s = 0; s < 4; s++) {
                            const val = values[colIdx] || '';
                            if (val) {
                                // Find ID from label
                                let id = '';
                                if (rowType === 'group') {
                                    let found = '';
                                    for (const tid of Object.keys(this.trainerGroups)) {
                                        const group = this.trainerGroups[tid].find(g => g.label === val);
                                        if (group) { found = String(group.id); break; }
                                    }
                                    id = found;
                                } else if (rowType === 'module') {
                                    const module = this.modules.find(m => m.label === val);
                                    id = module ? String(module.id) : '';
                                    if (!id) {
                                        const mid = Object.keys(this.moduleLabelById).find(k => this.moduleLabelById[k] === val);
                                        if (mid) id = String(mid);
                                    }
                                } else if (rowType === 'salle') {
                                    const salle = this.salles.find(s => s.label === val);
                                    id = salle ? String(salle.id) : '';
                                }
                                if (id) {
                                    this.timetable[trainer.id][rowType][d + '-' + s] = id;
                                }
                            }
                            colIdx++;
                        }
                    }
                    imported++;
                }
                
                this.renderTable();
                showToast('Import réussi : ' + imported + ' lignes importées !', 'success');
                event.target.value = '';
            } catch (error) {
                showToast("Erreur lors de l'import du CSV", 'error');
                console.error(error);
            }
        };
        reader.readAsText(file);
    },
    
    clearAll: function() {
        if (!confirm('Effacer tout l\'emploi du temps pour cette semaine ?')) return;
        this.resetTimetable();
        this.renderTable();

        // Send empty entries to delete all for this date and formateurs
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) {
            headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
        }
        
        const payload = {
            date: this.selectedDate,
            entries: [], // empty to delete all
            formateur_ids: this.trainers.map(t => t.id)
        };
        
        fetch('/api/timetable-formateurs', {
            method: 'POST',
            headers,
            body: JSON.stringify(payload)
        }).then(r => {
            if (!r.ok) throw new Error('Clear failed');
            return r.json();
        }).then(() => {
            this.timetableExists = false;
            this.updateStatusBadge();
            showToast('Emploi du temps réinitialisé et supprimé de la base de données', 'success');
        }).catch(e => {
            console.error(e);
            showToast('Erreur lors de la réinitialisation', 'error');
        });
    },
    
    navigateDate: function(days) {
        const current = this.parseLocalDate(this.selectedDate);
        current.setDate(current.getDate() + days);
        this.selectedDate = this.formatLocalDate(current);
        document.getElementById('selectedDate').value = this.selectedDate;
        // Clear cache for new date
        if (this.timetableCache[this.selectedDate]) {
            delete this.timetableCache[this.selectedDate];
        }
        this.loadTimetableForDate();
    },
    
    goToToday: function() {
        this.selectedDate = this.formatLocalDate(this.todayLocal());
        document.getElementById('selectedDate').value = this.selectedDate;
        // Clear cache for new date
        if (this.timetableCache[this.selectedDate]) {
            delete this.timetableCache[this.selectedDate];
        }
        this.loadTimetableForDate();
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.formateurTimetable.init();
});

// Add real-time sync on window focus
window.addEventListener('focus', () => {
    if (window.formateurTimetable && window.formateurTimetable.revalidateTimetableForDate) {
        window.formateurTimetable.revalidateTimetableForDate({ silent: true });
    }
});
</script>

@endsection