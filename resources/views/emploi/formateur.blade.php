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
                <select id="formateurFilter" onchange="window.formateurTimetable.onFormateurFilterChanged()" class="px-3 py-1.5 bg-white border border-green-300 rounded-md text-sm font-medium text-gray-700 focus:ring-2 focus:ring-green-500 focus:border-green-500">
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
    <div class="px-2">
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
        return salleId === 'teams' ? 'distance' : 'presentiel';
    },

    selectedDate: '',
    selectedFormateur: 'all', // Add formateur filter
    searchTerm: '', // Add search term
    timetableExists: false,
    timetableId: null,
    lastSaved: null,
    timetable: {},
    
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
        this.renderTable();
        this.loadTimetableForDate();
    },

    onSearchInput: function() {
        this.searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        this.renderTable();
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
        
        // Add Teams option at the beginning
        return [
            { id: 'teams', label: ' Teams(Distance)' },
            ...salles
        ];
    },

    clearSalleFromOtherTrainersForSlot: function(dayIdx, slotIdx, excludeTrainerId, salleId) {
        if (!salleId || salleId === 'teams') return; // Don't clear Teams as it's not a physical salle
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
                        } else {
                            options = this.getSalleOptionsForSlot(d, s, trainer.id);
                        }

                        if (rowType === 'salle') {
                            select.onchange = (e) => {
                                const v = e.target.value;
                                // Only clear from other trainers if it's a real salle (not Teams)
                                if (v && v !== 'teams') {
                                    this.clearSalleFromOtherTrainersForSlot(d, s, trainer.id, v);
                                }
                                this.setCellValue(trainer.id, 'salle', d, s, v);
                                this.updateCellStyling(td, select, 'salle', v);
                                this.refreshSalleDropdownsForSlot(d, s);
                            };
                        }

                        options.forEach(opt => {
                            const option = document.createElement('option');
                            option.value = String(opt.id);
                            option.textContent = opt.label;
                            if (opt.id === 'teams') {
                                option.style.color = 'blue';
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
    
    updateCellStyling: function(td, select, rowType, value) {
        td.classList.remove('edt-cell-group', 'edt-cell-module', 'edt-cell-salle', 'edt-cell-salle-teams');
        select.classList.remove('edt-cell-group', 'edt-cell-module', 'edt-cell-salle', 'edt-cell-salle-teams');
        select.style.color = '';
        select.style.backgroundColor = '';
        
        if (!value) {
            return;
        }

        const className = `edt-cell-${rowType}`;
        td.classList.add(className);
        select.classList.add(className);

        if (rowType === 'salle' && value === 'teams') {
            td.classList.add('edt-cell-salle-teams');
            select.classList.remove('edt-cell-salle');
            select.classList.add('edt-cell-salle-teams');
            select.style.color = 'blue';
            select.style.backgroundColor = 'transparent';
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
    
    updateStatusBadge: function() {
        const badge = document.getElementById('statusBadge');
        const text = document.getElementById('statusText');
        
        if (this.timetableExists) {
            badge.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-green-50 text-green-700 border border-green-200';
            text.textContent = 'Existant';
        } else {
            badge.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200';
            text.textContent = 'Nouveau';
        }
    },
    
    updateDateDisplay: function() {
        const date = this.parseLocalDate(this.selectedDate);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('dateDisplay').textContent = date.toLocaleDateString('fr-FR', options);
        document.getElementById('dayName').textContent = date.toLocaleDateString('fr-FR', { weekday: 'long' }).toUpperCase();
    },
    
    async loadTimetableForDate() {
        this.selectedDate = document.getElementById('selectedDate').value;
        this.selectedDate = this.formatLocalDate(this.parseLocalDate(this.selectedDate));
        document.getElementById('selectedDate').value = this.selectedDate;
        this.updateDateDisplay();

        // Refresh reference data so deleted groups/modules are not shown as stale options
        this.moduleLabelById = {};
        await Promise.all([
            this.loadModules(),
            this.loadAllGroups(),
            this.loadFilteredGroupsForAllTrainers(),
        ]);
        
        try {
            // Reset timetable
            this.resetTimetable();
            
            // Track if any entries were loaded
            let totalEntriesLoaded = 0;
            
            // Filter trainers based on selected formateur
            const filteredTrainers = this.selectedFormateur === 'all' 
                ? this.trainers 
                : this.trainers.filter(t => String(t.id) === this.selectedFormateur);
            
            // For each filtered trainer, load their data
            for (const trainer of filteredTrainers) {
                try {
                    const response = await fetch(`/api/timetable-formateur/${this.selectedDate}?formateur_id=${trainer.id}`);
                    
                    if (response.ok) {
                        const entries = await response.json();
                        
                        // Count entries for status tracking
                        if (entries && entries.length > 0) {
                            totalEntriesLoaded += entries.length;
                        }
                        
                        const selectedDayIndex = this.getSelectedDayIndex();
                        const selectedDayName = this.getSelectedDayName();

                        // Convert entries to timetable format (id-based)
                        entries.forEach(entry => {
                            if (String(entry.date || '').slice(0, 10) !== this.selectedDate) return;
                            const dayIndex = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'].indexOf(entry.jour || '');
                            const slotIndex = parseInt(entry.creneau?.substring(1) || '', 10) - 1;
                            
                            if (dayIndex >= 0 && slotIndex >= 0) {
                                this.setCellValue(trainer.id, 'group', dayIndex, slotIndex, entry.groupe_id ? String(entry.groupe_id) : '');
                                this.setCellValue(trainer.id, 'module', dayIndex, slotIndex, entry.module_id ? String(entry.module_id) : '');
                                const salleValue = entry.type_session === 'distance' ? 'teams' : (entry.salle_id ? String(entry.salle_id) : '');
                                this.setCellValue(trainer.id, 'salle', dayIndex, slotIndex, salleValue);
                            }
                        });
                    }
                } catch (e) {
                    console.error(`Error loading data for trainer ${trainer.id}:`, e);
                }
            }
            
            // Set timetableExists based on whether any entries were loaded
            this.timetableExists = totalEntriesLoaded > 0;
            
            this.renderTable();
            await this.refreshAllModuleDropdownsFormateur();
            this.refreshSalleDropdowns();
            this.updateStatusBadge();
            showToast('Emploi du temps chargé', 'success');
            
        } catch (error) {
            console.error('Error loading timetable:', error);
            this.resetTimetable();
            this.renderTable();
            this.updateStatusBadge();
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
                            // Handle Teams selection
                            const typeSession = this.getTypeSession(salleId);
                            const finalSalleId = typeSession === 'distance' ? null : salleId;

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
                await this.loadTimetableForDate();
                showToast('Emploi du temps enregistré avec succès !', 'success');
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
        this.loadTimetableForDate();
    },
    
    goToToday: function() {
        this.selectedDate = this.formatLocalDate(this.todayLocal());
        document.getElementById('selectedDate').value = this.selectedDate;
        this.loadTimetableForDate();
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.formateurTimetable.init();
});
</script>

@endsection