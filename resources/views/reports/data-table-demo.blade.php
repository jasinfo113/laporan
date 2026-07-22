<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-bold text-2xl text-slate-800 dark:text-slate-100 tracking-tight">
                {{ __('Reusable Data Table Showcase') }}
            </h2>
            
            <!-- Mode Switcher -->
            <div class="inline-flex bg-slate-100 dark:bg-slate-900 p-1 rounded-xl border border-slate-200 dark:border-slate-800">
                <a 
                    href="?mode=client" 
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all {{ $mode === 'client' ? 'bg-white dark:bg-slate-850 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}"
                >
                    Client-Side (Alpine)
                </a>
                <a 
                    href="?mode=server" 
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all {{ $mode === 'server' ? 'bg-white dark:bg-slate-850 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}"
                >
                    Server-Side (Laravel)
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 dark:bg-slate-900/40 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Information Panel -->
            <div class="bg-white dark:bg-slate-950 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row gap-5">
                <div class="flex-1 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100">
                            Current Mode: {{ $mode === 'client' ? 'Client-Side Interactivity' : 'Server-Side Query Orchestration' }}
                        </h4>
                    </div>
                    
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        @if ($mode === 'client')
                            All 45 mock users are rendered at once and passed to Alpine.js. Sorting, searching, and paging occur instantly in the browser without any page reloads or network requests.
                        @else
                            Laravel database helpers paginates and sorts records dynamically via server-side Eloquent emulation. Every search keypress (debounced at 350ms), sort header change, and page transition reloads the view with query strings (`?mode=server&search=...&sort=...`).
                        @endif
                    </p>
                </div>

                <!-- Keyboard Shortcut / Accessibility Quick Reference -->
                <div class="w-full md:w-80 bg-slate-50 dark:bg-slate-900/30 p-4 rounded-xl border border-slate-100 dark:border-slate-850 space-y-2">
                    <h5 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Keyboard Navigation</h5>
                    <ul class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1.5">
                        <li class="flex justify-between">
                            <span>Focus table rows:</span>
                            <kbd class="px-1 border dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-[9px] rounded shadow-sm">Tab</kbd>
                        </li>
                        <li class="flex justify-between">
                            <span>Move between rows:</span>
                            <kbd class="px-1 border dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-[9px] rounded shadow-sm">↓ / ↑ Arrows</kbd>
                        </li>
                        <li class="flex justify-between">
                            <span>Toggle row checkbox:</span>
                            <kbd class="px-1 border dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-[9px] rounded shadow-sm">Space</kbd>
                        </li>
                        <li class="flex justify-between">
                            <span>Reset search input:</span>
                            <kbd class="px-1 border dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-[9px] rounded shadow-sm">Escape</kbd>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Table Component -->
            <div 
                x-data="{
                    activeNotification: '',
                    showNotification(message) {
                        this.activeNotification = message;
                        setTimeout(() => this.activeNotification = '', 4000);
                    }
                }"
                @action-edit.window="showNotification('✏️ Edit triggered for: ' + $event.detail.name)"
                @action-delete.window="showNotification('🗑️ Delete triggered for user ID: ' + $event.detail.id)"
                class="relative"
            >
                <!-- Dynamic Actions Banner Notification -->
                <div 
                    x-show="activeNotification"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="absolute -top-14 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-full shadow-lg border border-slate-800 flex items-center gap-2 z-50"
                >
                    <span x-text="activeNotification"></span>
                    <button @click="activeNotification = ''" class="hover:text-indigo-400 font-bold ml-1">×</button>
                </div>

                <x-data-table 
                    :columns="$columns"
                    :data="$data"
                    :server-side="$serverSide"
                    empty-title="No active staff members"
                    empty-description="Try modifying search queries or add a new team member."
                    key-field="id"
                >
                    <!-- Scoped Bulk Action Buttons -->
                    <x-slot name="bulkActions">
                        <button 
                            @click="alert('Selected user IDs: ' + selectedIds.join(', ')); selectedIds = [];"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold py-1 px-3 rounded-lg shadow-sm transition"
                        >
                            Export
                        </button>
                        <button 
                            @click="alert('Archiving: ' + selectedIds.join(', ')); selectedIds = [];"
                            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-[11px] font-bold py-1 px-3 rounded-lg transition"
                        >
                            Archive
                        </button>
                    </x-slot>
                </x-data-table>
            </div>

            <!-- Developer Integration Guides (Aesthetic tabs & codeblocks) -->
            <div class="bg-white dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                <h4 class="font-bold text-base text-slate-800 dark:text-slate-100">Component Developer Experience (Props API)</h4>
                
                <div x-data="{ tab: 'blade' }" class="space-y-4">
                    <!-- Tab switches -->
                    <div class="flex border-b border-slate-200 dark:border-slate-800">
                        <button 
                            @click="tab = 'blade'"
                            class="px-4 py-2 text-xs font-semibold border-b-2 transition"
                            :class="tab === 'blade' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        >
                            Blade Usage
                        </button>
                        <button 
                            @click="tab = 'columns'"
                            class="px-4 py-2 text-xs font-semibold border-b-2 transition"
                            :class="tab === 'columns' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        >
                            Columns Definition Schema
                        </button>
                    </div>

                    <!-- Blade Codeblock -->
                    <div x-show="tab === 'blade'" class="rounded-xl overflow-hidden bg-slate-900 text-slate-300 p-4 font-mono text-xs leading-relaxed max-h-96 overflow-y-auto">
                        <span class="text-slate-500">// Invoking the component in Laravel Blade</span><br/>
                        &lt;<span class="text-indigo-400">x-data-table</span> <br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-indigo-300">:columns</span>=<span class="text-emerald-400">"$columns"</span> <br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-indigo-300">:data</span>=<span class="text-emerald-400">"$data"</span> <br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-indigo-300">:server-side</span>=<span class="text-emerald-400">"$serverSide"</span> <br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-indigo-300">empty-title</span>=<span class="text-emerald-400">"No Staff Found"</span> <br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-indigo-300">key-field</span>=<span class="text-emerald-400">"id"</span><br/>
                        &gt;<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&lt;<span class="text-indigo-400">x-slot</span> <span class="text-indigo-300">name</span>=<span class="text-emerald-400">"bulkActions"</span>&gt;<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;<span class="text-indigo-450">button</span> <span class="text-indigo-300">@click</span>=<span class="text-emerald-400">"archive(selectedIds)"</span>&gt;Archive&lt;/<span class="text-indigo-450">button</span>&gt;<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&lt;/<span class="text-indigo-400">x-slot</span>&gt;<br/>
                        &lt;/<span class="text-indigo-400">x-data-table</span>&gt;
                    </div>

                    <!-- Column Spec Codeblock -->
                    <div x-show="tab === 'columns'" class="rounded-xl overflow-hidden bg-slate-900 text-slate-300 p-4 font-mono text-xs leading-relaxed max-h-96 overflow-y-auto">
                        <span class="text-slate-500">// PHP Array representing visible columns and special cells</span><br/>
                        $columns = [<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;[<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'key'</span> => <span class="text-emerald-400">'name'</span>,<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'label'</span> => <span class="text-emerald-400">'Name & Email'</span>,<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'sortable'</span> => <span class="text-indigo-400">true</span>,<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'type'</span> => <span class="text-emerald-400">'user_profile'</span>, <span class="text-slate-500">// Custom complex cell</span><br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'emailKey'</span> => <span class="text-emerald-400">'email'</span><br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;],<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;[<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'key'</span> => <span class="text-emerald-400">'status'</span>,<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'label'</span> => <span class="text-emerald-400">'Status'</span>,<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'sortable'</span> => <span class="text-indigo-400">true</span>,<br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="text-emerald-400">'type'</span> => <span class="text-emerald-400">'badge'</span> <span class="text-slate-500">// Auto styles green/amber/rose</span><br/>
                        &nbsp;&nbsp;&nbsp;&nbsp;]<br/>
                        ];
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
