@extends('layouts.app')

@section('content')
<div x-data="homePage()" x-init="init(); document.getElementById('main-content-area').style.backgroundColor = '#f5f3f7'" class="min-h-screen pb-12">

    {{-- Header Section --}}
    <div class="mb-6 pt-4">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">
                Bienvenue {{ optional(auth()->user())->prenom ?? optional(auth()->user())->nom ?? 'Utilisateur' }}
            </h1>
            <div class="text-sm text-gray-500 font-normal">
                <span>Accueil</span>
                <span class="mx-1">/</span>
                <span>Admin</span>
            </div>
        </div>
    </div>

    {{-- Summary Cards Row --}}
    <div class="flex gap-6 mb-6">

        {{-- Groupes Card --}}
        <div class="flex-1 bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-gray-600 text-xs font-semibold uppercase tracking-wide mb-3">Groupes</p>
                    <p class="text-4xl font-bold text-gray-900" x-text="classesCount">0</p>
                    <p class="text-xs text-gray-400 mt-2">Classes et groupes actifs</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 flex-shrink-0 ml-4">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Salles Card --}}
        <div class="flex-1 bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-gray-600 text-xs font-semibold uppercase tracking-wide mb-3">Salles</p>
                    <p class="text-4xl font-bold text-gray-900" x-text="sallesCount">0</p>
                    <p class="text-xs text-gray-400 mt-2">Salles disponibles</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 flex-shrink-0 ml-4">
                    <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- EFP Card --}}
        <div class="flex-1 bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-gray-600 text-xs font-semibold uppercase tracking-wide mb-3">EFP</p>
                    <p class="text-4xl font-bold text-gray-900" x-text="departmentsCount">0</p>
                    <p class="text-xs text-gray-400 mt-2">EFP actives</p>
                </div>
                <div class="bg-amber-50 rounded-lg p-4 flex-shrink-0 ml-4">
                    <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Formateurs Card --}}
        <div class="flex-1 bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-gray-600 text-xs font-semibold uppercase tracking-wide mb-3">Formateurs</p>
                    <p class="text-4xl font-bold text-gray-900" x-text="professeursCount">0</p>
                    <p class="text-xs text-gray-400 mt-2">Enseignants actifs</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 flex-shrink-0 ml-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Sections Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Left Column - Quick Navigation --}}
        <div class="lg:col-span-1 bg-white rounded-xl shadow-sm p-6 border border-gray-200 lg:mr-3">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Accès Rapide</h3>
            <div class="space-y-3">

                {{-- Emploi Formateur --}}
                <a href="{{ route('emploi.formateur') }}" class="w-full group block">
                    <div class="flex items-center p-4 bg-white hover:bg-blue-50 rounded-xl transition-all duration-200 border-2 border-gray-100 hover:border-blue-200">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl mr-4 flex-shrink-0 group-hover:shadow-lg transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="text-left flex-1">
                            <p class="font-semibold text-sm text-gray-900 group-hover:text-blue-700">Emploi Formateur</p>
                            <p class="text-xs text-gray-500 mt-0.5">Gérer les emplois du temps</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                {{-- Groupes --}}
                <a href="{{ route('groupes.index') }}" class="w-full group block">
                    <div class="flex items-center p-4 bg-white hover:bg-purple-50 rounded-xl transition-all duration-200 border-2 border-gray-100 hover:border-purple-200">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl mr-4 flex-shrink-0 group-hover:shadow-lg transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="text-left flex-1">
                            <p class="font-semibold text-sm text-gray-900 group-hover:text-purple-700">Groupes</p>
                            <p class="text-xs text-gray-500 mt-0.5">Gérer les classes et filières</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                {{-- Modules --}}
                <a href="{{ route('modules.index') }}" class="w-full group block">
                    <div class="flex items-center p-4 bg-white hover:bg-amber-50 rounded-xl transition-all duration-200 border-2 border-gray-100 hover:border-amber-200">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl mr-4 flex-shrink-0 group-hover:shadow-lg transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="text-left flex-1">
                            <p class="font-semibold text-sm text-gray-900 group-hover:text-amber-700">Modules</p>
                            <p class="text-xs text-gray-500 mt-0.5">Gérer les modules de formation</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-amber-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                {{-- Formateurs --}}
                <a href="{{ route('formateurs.index') }}" class="w-full group block">
                    <div class="flex items-center p-4 bg-white hover:bg-green-50 rounded-xl transition-all duration-200 border-2 border-gray-100 hover:border-green-200">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl mr-4 flex-shrink-0 group-hover:shadow-lg transition-shadow">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="text-left flex-1">
                            <p class="font-semibold text-sm text-gray-900 group-hover:text-green-700">Formateurs</p>
                            <p class="text-xs text-gray-500 mt-0.5">Gérer les enseignants</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>

        {{-- Right Column - Module Progress --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 lg:ml-3 overflow-hidden">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Avancement des Modules</h3>
                <div class="flex items-center gap-2">
                    <div class="text-right">
                        <span class="text-2xl font-bold"
                              :class="overallProgress >= 60 ? 'text-green-600' : overallProgress >= 30 ? 'text-amber-500' : 'text-red-500'"
                              x-text="overallProgress + '%'"></span>
                        <p class="text-xs text-gray-500">Global</p>
                    </div>
                    <div class="w-14 h-14 relative">
                        <svg class="w-14 h-14 -rotate-90" viewBox="0 0 44 44">
                            <circle cx="22" cy="22" r="18" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                            <circle cx="22" cy="22" r="18" fill="none"
                                    :stroke="overallProgress >= 60 ? '#22c55e' : overallProgress >= 30 ? '#f59e0b' : '#ef4444'"
                                    stroke-width="4" stroke-linecap="round"
                                    :stroke-dasharray="(overallProgress / 100 * 113.1) + ' 113.1'"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Module list --}}
            <div class="px-6 py-4 max-h-[320px] overflow-y-auto space-y-3">
                <template x-for="m in modulesList" :key="m.code">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold"
                             :class="{
                                 'bg-blue-100 text-blue-700':    m.filiere === 'DEV',
                                 'bg-purple-100 text-purple-700': m.filiere === 'NET',
                                 'bg-orange-100 text-orange-700': m.filiere === 'RESEAUX',
                                 'bg-gray-100 text-gray-600':    !m.filiere || m.filiere === 'Commun'
                             }"
                             x-text="m.filiere ? m.filiere.substring(0,3) : 'COM'">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-medium text-gray-800 truncate" x-text="m.code + ' — ' + m.title"></p>
                                <span class="text-xs font-semibold ml-2 flex-shrink-0"
                                      :class="m.progress >= 75 ? 'text-green-600' : m.progress >= 40 ? 'text-amber-600' : 'text-red-500'"
                                      x-text="m.progress + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-700"
                                     :class="m.progress >= 75 ? 'bg-green-500' : m.progress >= 40 ? 'bg-yellow-400' : 'bg-red-400'"
                                     :style="'width:' + m.progress + '%'"></div>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="modulesList.length === 0" class="text-center py-6 text-gray-400 text-sm">Aucun module trouvé</div>
            </div>

            {{-- Bottom stats --}}
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span x-text="modulesCompleted + ' Terminés'"></span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                        <span x-text="modulesInProgress + ' En cours'"></span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-red-400"></span>
                        <span x-text="modulesLow + ' En retard'"></span>
                    </span>
                </div>
                <a href="{{ route('modules.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">Voir tout →</a>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center mt-12 pt-8"></div>
</div>

<script>
function homePage() {
    return {
        classesCount: @json($groupes ?? 0),
        sallesCount: @json($salles ?? 0),
        departmentsCount: @json($departements ?? 0),
        professeursCount: @json($formateurs ?? 0),
        modulesList: @json($modulesList ?? []),
        overallProgress: @json($overallProgress ?? 0),
        modulesCompleted: @json($modulesCompleted ?? 0),
        modulesInProgress: @json($modulesInProgress ?? 0),
        modulesLow: @json($modulesLow ?? 0),

        init() {
            this.modulesList.sort((a,b)=>a.progress-b.progress);
            const total = this.modulesList.length;
            if (total) {
                let sum = 0;
                this.modulesCompleted  = 0;
                this.modulesInProgress = 0;
                this.modulesLow        = 0;
                this.modulesList.forEach(m => {
                    sum += m.progress || 0;
                    if      (m.progress >= 75) this.modulesCompleted++;
                    else if (m.progress >= 40) this.modulesInProgress++;
                    else                        this.modulesLow++;
                });
                this.overallProgress = Math.round(sum / total);
            }
        },
    };
}

(function register(){
    const registerFn = () => { Alpine.data('homePage', homePage) };
    if(window.Alpine && Alpine.data){ registerFn(); } else { document.addEventListener('alpine:init', registerFn) }
})();
</script>
@endsection