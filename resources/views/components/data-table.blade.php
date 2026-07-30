@props([
    'columns' => [], // [['key' => 'id', 'label' => 'ID', 'sortable' => true, 'align' => 'left', 'type' => 'text'], ...]
    'data' => [],    // Array or Laravel Collection of rows
    'serverSide' => false,
    'totalCount' => null,
    'loading' => false,
    'pagination' => true,
    'searchable' => true,
    'itemsPerPage' => 10,
    'searchPlaceholder' => 'Search name, email, or details...',
    'emptyTitle' => 'No records found',
    'emptyDescription' => 'We couldn\'t find any records matching your criteria.',
    'keyField' => 'id',
])

@php
    // Determine the base dataset to pass down to JS
    $isCollection = $data instanceof \Illuminate\Support\Collection;
    $isPaginator = $data instanceof \Illuminate\Pagination\LengthAwarePaginator || $data instanceof \Illuminate\Pagination\AbstractPaginator;
    
    $recordsArray = $isPaginator ? $data->items() : ($isCollection ? $data->toArray() : $data);
    $totalRecords = $serverSide ? ($totalCount ?? ($isPaginator ? $data->total() : count($recordsArray))) : count($recordsArray);

    $jsonRecords = json_encode($recordsArray);
    $jsonColumns = json_encode($columns);
@endphp

<div 
    x-data="{
        // Component state
        columns: {{ $jsonColumns }},
        rawRows: {{ $jsonRecords }},
        serverSide: {{ json_encode($serverSide) }},
        keyField: '{{ $keyField }}',
        
        search: new URLSearchParams(window.location.search).get('search') || '',
        sortKey: new URLSearchParams(window.location.search).get('sort') || '',
        sortDesc: (new URLSearchParams(window.location.search).get('direction') || 'asc') === 'desc',
        page: parseInt(new URLSearchParams(window.location.search).get('page')) || 1,
        perPage: parseInt(new URLSearchParams(window.location.search).get('perPage')) || {{ $itemsPerPage }},
        
        selectedIds: [],
        isLoading: {{ json_encode($loading) }},
        activeRowIndex: -1,
        showColumnToggle: false,
        visibleColumns: [],

        init() {
            // Set up debouncing for search reloads
            this.debouncedServerReload = Alpine.debounce((params) => this.serverReload(params), 350);

            // Setup default visible columns
            this.visibleColumns = this.columns.map(c => c.key);
            
            // Watchers for server-side reloading vs client-side reactive pagination
            this.$watch('search', (value) => {
                if (this.serverSide) {
                    this.debouncedServerReload({ search: value, page: 1 });
                } else {
                    this.page = 1;
                    this.selectedIds = [];
                }
            });

            this.$watch('perPage', (value) => {
                if (this.serverSide) {
                    this.serverReload({ perPage: value, page: 1 });
                } else {
                    this.page = 1;
                }
            });

            // Handle browser keyboard events for grid focus
            this.$watch('activeRowIndex', (index) => {
                if (index >= 0) {
                    this.focusRow(index);
                }
            });
        },

        // Client-side filtration, sorting, and pagination getters
        get filteredRows() {
            if (this.serverSide) return this.rawRows;

            let rows = [...this.rawRows];

            // 1. Client search filter
            if (this.search) {
                const query = this.search.toLowerCase();
                rows = rows.filter(row => {
                    return this.columns.some(col => {
                        const val = row[col.key];
                        return val !== null && val !== undefined && String(val).toLowerCase().includes(query);
                    });
                });
            }

            // 2. Client sort
            if (this.sortKey) {
                rows.sort((a, b) => {
                    let valA = a[this.sortKey];
                    let valB = b[this.sortKey];

                    if (typeof valA === 'number' && typeof valB === 'number') {
                        return this.sortDesc ? valB - valA : valA - valB;
                    }

                    valA = valA !== null && valA !== undefined ? String(valA).toLowerCase() : '';
                    valB = valB !== null && valB !== undefined ? String(valB).toLowerCase() : '';

                    if (valA < valB) return this.sortDesc ? 1 : -1;
                    if (valA > valB) return this.sortDesc ? -1 : 1;
                    return 0;
                });
            }

            return rows;
        },

        get pagedRows() {
            if (this.serverSide || !{{ json_encode($pagination) }}) {
                return this.filteredRows;
            }
            const start = (this.page - 1) * this.perPage;
            return this.filteredRows.slice(start, start + this.perPage);
        },

        get totalPages() {
            if (this.serverSide) {
                return {{ $isPaginator ? $data->lastPage() : 1 }};
            }
            return Math.ceil(this.filteredRows.length / this.perPage) || 1;
        },

        get totalItems() {
            if (this.serverSide) return {{ $totalRecords }};
            return this.filteredRows.length;
        },

        get displayStart() {
            if (this.totalItems === 0) return 0;
            return (this.page - 1) * this.perPage + 1;
        },

        get displayEnd() {
            if (this.serverSide) {
                return Math.min(this.page * this.perPage, {{ $totalRecords }});
            }
            return Math.min(this.page * this.perPage, this.filteredRows.length);
        },

        // Client/Server Sorting action
        sortBy(key) {
            if (this.serverSide) {
                const direction = (this.sortKey === key && !this.sortDesc) ? 'desc' : 'asc';
                this.serverReload({ sort: key, direction, page: 1 });
            } else {
                if (this.sortKey === key) {
                    this.sortDesc = !this.sortDesc;
                } else {
                    this.sortKey = key;
                    this.sortDesc = false;
                }
            }
        },

        // Client/Server Pagination navigation
        goToPage(pageNum) {
            if (pageNum < 1 || pageNum > this.totalPages) return;
            if (this.serverSide) {
                this.serverReload({ page: pageNum });
            } else {
                this.page = pageNum;
                this.activeRowIndex = -1;
            }
        },

        // Selection Handlers
        toggleSelectAll() {
            const currentIds = this.pagedRows.map(r => r[this.keyField]);
            const allSelected = currentIds.every(id => this.selectedIds.includes(id));
            if (allSelected) {
                this.selectedIds = this.selectedIds.filter(id => !currentIds.includes(id));
            } else {
                this.selectedIds = [...new Set([...this.selectedIds, ...currentIds])];
            }
        },

        isAllSelected() {
            const currentIds = this.pagedRows.map(r => r[this.keyField]);
            if (currentIds.length === 0) return false;
            return currentIds.every(id => this.selectedIds.includes(id));
        },

        toggleRow(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(x => x !== id);
            } else {
                this.selectedIds.push(id);
            }
        },

        // Column Visibility management
        isColumnVisible(key) {
            return this.visibleColumns.includes(key);
        },

        toggleColumnVisibility(key) {
            if (this.visibleColumns.includes(key)) {
                if (this.visibleColumns.length > 1) {
                    this.visibleColumns = this.visibleColumns.filter(k => k !== key);
                }
            } else {
                this.visibleColumns.push(key);
            }
        },

        // Keyboard navigation helpers
        focusRow(index) {
            this.$nextTick(() => {
                const el = this.$refs.tableBody.querySelector(`[data-row-index='${index}']`);
                if (el) el.focus();
            });
        },

        focusNextRow() {
            if (this.activeRowIndex < this.pagedRows.length - 1) {
                this.activeRowIndex++;
            }
        },

        focusPreviousRow() {
            if (this.activeRowIndex > 0) {
                this.activeRowIndex--;
            }
        },

        // Helper to reload page with updated queries for Server Side mode
        serverReload(params = {}) {
            this.isLoading = true;
            const url = new URL(window.location.href);
            
            // Map keys
            const map = {
                search: params.search !== undefined ? params.search : this.search,
                sort: params.sort !== undefined ? params.sort : this.sortKey,
                direction: params.direction !== undefined ? params.direction : (this.sortDesc ? 'desc' : 'asc'),
                page: params.page !== undefined ? params.page : this.page,
                perPage: params.perPage !== undefined ? params.perPage : this.perPage,
            };

            // Set queries
            if (map.search) url.searchParams.set('search', map.search);
            else url.searchParams.delete('search');

            if (map.sort) {
                url.searchParams.set('sort', map.sort);
                url.searchParams.set('direction', map.direction);
            } else {
                url.searchParams.delete('sort');
                url.searchParams.delete('direction');
            }

            url.searchParams.set('page', map.page);
            url.searchParams.set('perPage', map.perPage);

            window.location.href = url.toString();
        },

        debouncedServerReload: null,
    }"
    class="w-full flex flex-col bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden"
    aria-busy="false"
    :aria-busy="isLoading"
>
    <!-- 1. Header Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between p-4 gap-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
        
        <!-- Left Section: Search Input -->
        @if ($searchable)
            <div class="relative flex-1 max-w-md w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    x-model="search"
                    @keydown.escape="search = ''"
                    class="block w-full pl-10 pr-12 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm transition-all"
                    placeholder="{{ $searchPlaceholder }}"
                />
                <!-- Keyboard Escape Tip -->
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <kbd class="text-[10px] font-mono px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-400">ESC</kbd>
                </div>
            </div>
        @endif

        <!-- Middle & Right Section: Bulk Actions & Columns Trigger -->
        <div class="flex items-center justify-end gap-3 ml-auto">
            <!-- Bulk Actions Slot (visible when items selected) -->
            <div 
                x-show="selectedIds.length > 0" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-950/30 px-3 py-1.5 rounded-xl border border-indigo-100 dark:border-indigo-900/50"
            >
                <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-400">
                    <span x-text="selectedIds.length"></span> selected
                </span>
                
                {{ $bulkActions ?? '' }}
            </div>

            <!-- Column Visibility Toggle Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button 
                    @click="open = !open"
                    class="inline-flex items-center justify-center p-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400 transition-colors"
                    aria-label="Toggle visible columns"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </button>
                <!-- Dropdown list -->
                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="absolute right-0 mt-2 w-56 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg py-2 z-30"
                >
                    <div class="px-3 py-1 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Show Columns</div>
                    <div class="mt-1 divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="col in columns" :key="col.key">
                            <label class="flex items-center px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer text-sm text-slate-700 dark:text-slate-300">
                                <input 
                                    type="checkbox" 
                                    :checked="isColumnVisible(col.key)" 
                                    @change="toggleColumnVisibility(col.key)"
                                    class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 mr-3 h-4 w-4"
                                />
                                <span x-text="col.label"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Grid Table Area -->
    <div class="relative overflow-x-auto w-full">
        <table 
            class="w-full text-left border-collapse table-auto" 
            role="grid"
            :aria-rowcount="totalItems + 1"
            :aria-colcount="visibleColumns.length + 1"
        >
            <!-- Columns Header -->
            <thead class="bg-slate-50/70 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <!-- Pinned Selector Column -->
                    <th class="w-12 px-4 py-3.5 text-center sticky left-0 bg-slate-50 dark:bg-slate-900 z-10">
                        <input 
                            type="checkbox" 
                            :checked="isAllSelected()"
                            @change="toggleSelectAll()"
                            class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5 transition"
                            aria-label="Select all rows on this page"
                        />
                    </th>

                    <!-- Dynamic Headers -->
                    <template x-for="col in columns" :key="col.key">
                        <th 
                            x-show="isColumnVisible(col.key)"
                            class="px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider select-none"
                            :class="col.align === 'right' ? 'text-right' : (col.align === 'center' ? 'text-center' : 'text-left')"
                            :aria-sort="sortKey === col.key ? (sortDesc ? 'descending' : 'ascending') : 'none'"
                        >
                            <template x-if="col.sortable">
                                <button 
                                    @click="sortBy(col.key)"
                                    class="group inline-flex items-center gap-1.5 focus:outline-none hover:text-slate-800 dark:hover:text-slate-200"
                                >
                                    <span x-text="col.label"></span>
                                    <span class="text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 transition-colors">
                                        <svg 
                                            class="h-3.5 w-3.5 transition-transform" 
                                            :class="sortKey === col.key && sortDesc ? 'rotate-180 text-indigo-600 dark:text-indigo-400' : (sortKey === col.key ? 'text-indigo-600 dark:text-indigo-400' : '')"
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </span>
                                </button>
                            </template>
                            <template x-if="!col.sortable">
                                <span x-text="col.label"></span>
                            </template>
                        </th>
                    </template>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody 
                x-ref="tableBody"
                @keydown.arrow-down.prevent="focusNextRow()"
                @keydown.arrow-up.prevent="focusPreviousRow()"
                class="divide-y divide-slate-100 dark:divide-slate-900 bg-white dark:bg-slate-950"
            >
                <!-- A. Skeletons Loading View -->
                <template x-if="isLoading">
                    <template x-for="i in Array.from({length: 4})">
                        <tr class="animate-pulse">
                            <td class="w-12 p-4 text-center">
                                <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto"></div>
                            </td>
                            <template x-for="col in columns">
                                <td x-show="isColumnVisible(col.key)" class="p-4">
                                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-3/4"></div>
                                </td>
                            </template>
                        </tr>
                    </template>
                </template>

                @if ($serverSide)
                    <!-- B1. Server-Side Rows Render -->
                    @forelse($recordsArray as $idx => $row)
                        @php
                            $rowKeyVal = data_get($row, $keyField);
                        @endphp
                        <tr 
                            x-show="!isLoading"
                            class="group hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors focus:bg-slate-50 dark:focus:bg-slate-900 outline-none"
                            :class="selectedIds.includes({{ json_encode($rowKeyVal) }}) ? 'bg-indigo-50/30 dark:bg-indigo-950/10' : ''"
                            data-row-index="{{ $idx }}"
                            tabindex="0"
                            @keydown.space.prevent="toggleRow({{ json_encode($rowKeyVal) }})"
                        >
                            <!-- Selector checkbox -->
                            <td class="w-12 px-4 py-3.5 text-center sticky left-0 bg-white dark:bg-slate-950 group-hover:bg-slate-50/50 dark:group-hover:bg-slate-900/30 transition-colors">
                                <input 
                                    type="checkbox" 
                                    :checked="selectedIds.includes({{ json_encode($rowKeyVal) }})"
                                    @change="toggleRow({{ json_encode($rowKeyVal) }})"
                                    @click.stop
                                    class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5 transition cursor-pointer"
                                />
                            </td>

                            <!-- Dynamic column cell cells -->
                            @foreach($columns as $col)
                                <td 
                                    x-show="isColumnVisible('{{ $col['key'] }}')"
                                    class="px-4 py-3.5 text-sm text-slate-600 dark:text-slate-300"
                                    align="{{ $col['align'] ?? 'left' }}"
                                >
                                    @if(is_callable($col['render'] ?? null))
                                        {!! $col['render']($row) !!}
                                    @else
                                        <div class="truncate max-w-[280px]" title="{{ data_get($row, $col['key']) }}">
                                            {{ data_get($row, $col['key']) }}
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr x-show="!isLoading">
                            <td colspan="{{ count($columns) + 1 }}" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 text-slate-400 mb-4">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ request('search') ? 'No search results found' : $emptyTitle }}</h3>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ request('search') ? 'Try checking for typos or searching for alternative fields.' : $emptyDescription }}</p>
                                    @if(request('search'))
                                        <button 
                                            @click="search = ''; serverReload({ search: '' })"
                                            class="mt-4 inline-flex items-center px-3.5 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition"
                                        >
                                            Clear Search Query
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                @else
                    <!-- B2. Dynamic / Local Rows Render -->
                    <template x-if="!isLoading && pagedRows.length > 0">
                        <template x-for="(row, idx) in pagedRows" :key="row[keyField]">
                            <tr 
                                class="group hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors focus:bg-slate-50 dark:focus:bg-slate-900 outline-none"
                                :class="selectedIds.includes(row[keyField]) ? 'bg-indigo-50/30 dark:bg-indigo-950/10' : ''"
                                :data-row-index="idx"
                                tabindex="0"
                                @keydown.space.prevent="toggleRow(row[keyField])"
                            >
                                <!-- Selector checkbox -->
                                <td class="w-12 px-4 py-3.5 text-center sticky left-0 bg-white dark:bg-slate-950 group-hover:bg-slate-50/50 dark:group-hover:bg-slate-900/30 transition-colors">
                                    <input 
                                        type="checkbox" 
                                        :checked="selectedIds.includes(row[keyField])"
                                        @change="toggleRow(row[keyField])"
                                        @click.stop
                                        class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5 transition cursor-pointer"
                                    />
                                </td>

                                <!-- Dynamic column cell cells -->
                                <template x-for="col in columns" :key="col.key">
                                    <td 
                                        x-show="isColumnVisible(col.key)"
                                        class="px-4 py-3.5 text-sm text-slate-600 dark:text-slate-300"
                                        :class="col.align === 'right' ? 'text-right' : (col.align === 'center' ? 'text-center' : 'text-left')"
                                    >
                                        <!-- Predefined Type: Badge -->
                                        <template x-if="col.type === 'badge'">
                                            <span 
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold tracking-wide border"
                                                :class="row[col.key] === 'active' || row[col.key] === 'approved' || row[col.key] === 'success'
                                                    ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30'
                                                    : (row[col.key] === 'pending' || row[col.key] === 'warning'
                                                        ? 'bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border-amber-100 dark:border-amber-900/30'
                                                        : 'bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/30')"
                                                x-text="row[col.key]"
                                            ></span>
                                        </template>

                                        <!-- Predefined Type: User Profile (Avatar + Name/Subtext) -->
                                        <template x-if="col.type === 'user_profile'">
                                            <div class="flex items-center gap-3">
                                                <div 
                                                    class="h-9 w-9 rounded-full font-bold flex items-center justify-center text-xs tracking-wider border text-white"
                                                    :class="[
                                                        'bg-indigo-600 border-indigo-400',
                                                        'bg-teal-600 border-teal-400',
                                                        'bg-emerald-600 border-emerald-400',
                                                        'bg-violet-600 border-violet-400',
                                                        'bg-pink-600 border-pink-400'
                                                    ][Math.abs(String(row[col.key]).charCodeAt(0) || 0) % 5]"
                                                    x-text="String(row[col.key]).split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase()"
                                                ></div>
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-slate-800 dark:text-slate-100" x-text="row[col.key]"></span>
                                                    <span class="text-xs text-slate-400 dark:text-slate-500" x-text="row[col.emailKey || 'email']"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Predefined Type: Action Row Menu -->
                                        <template x-if="col.type === 'actions'">
                                            <div class="flex items-center justify-end gap-1.5" @click.stop>
                                                <button 
                                                    @click="$dispatch('action-edit', row)"
                                                    class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                                                    title="Edit item"
                                                >
                                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>
                                                <button 
                                                    @click="$dispatch('action-delete', row)"
                                                    class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                                                    title="Delete item"
                                                >
                                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>

                                        <!-- Standard Render (Text/Truncated) -->
                                        <template x-if="!col.type || col.type === 'text'">
                                            <div 
                                                x-text="row[col.key]" 
                                                class="truncate max-w-[280px]" 
                                                :title="row[col.key]"
                                            ></div>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </template>

                    <!-- C. Empty State View -->
                    <template x-if="!isLoading && pagedRows.length === 0">
                        <tr>
                            <td :colspan="visibleColumns.length + 1" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 text-slate-400 mb-4">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200" x-text="search ? 'No search results found' : '{{ $emptyTitle }}'"></h3>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1" x-text="search ? 'Try checking for typos or searching for alternative fields.' : '{{ $emptyDescription }}'"></p>
                                    <template x-if="search">
                                        <button 
                                            @click="search = ''"
                                            class="mt-4 inline-flex items-center px-3.5 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition"
                                        >
                                            Clear Search Query
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                @endif
            </tbody>
        </table>
    </div>

    <!-- 3. Pagination Footer controls -->
    @if ($pagination)
        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 gap-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 select-none">
            <!-- Left: Range Info -->
            <div class="text-sm text-slate-500 dark:text-slate-400">
                Showing <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="displayStart"></span> 
                to <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="displayEnd"></span> 
                of <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="totalItems"></span> entries
            </div>

            <!-- Right: Page Navigation & Page Size Selector -->
            <div class="flex flex-wrap items-center justify-between md:justify-end gap-4">
                <!-- Page size selector dropdown -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Rows per page:</span>
                    <select 
                        x-model="perPage"
                        class="text-xs border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg p-1.5 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                        data-native-select
                    >
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">All</option>
                    </select>
                </div>

                <!-- Page Navigator Buttons -->
                <nav class="inline-flex gap-1.5" aria-label="Pagination">
                    <!-- Prev Button -->
                    <button 
                        @click="goToPage(page - 1)"
                        :disabled="page === 1"
                        class="p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400 disabled:opacity-40 disabled:hover:bg-transparent transition-colors"
                        aria-label="Previous Page"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Fast Number Select list -->
                    <div class="flex items-center gap-1">
                        <template x-for="p in totalPages" :key="p">
                            <button 
                                @click="goToPage(p)"
                                class="h-8.5 w-8.5 rounded-lg text-xs font-semibold transition"
                                :class="page === p 
                                    ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm' 
                                    : 'border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-900'"
                                x-text="p"
                            ></button>
                        </template>
                    </div>

                    <!-- Next Button -->
                    <button 
                        @click="goToPage(page + 1)"
                        :disabled="page === totalPages"
                        class="p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400 disabled:opacity-40 disabled:hover:bg-transparent transition-colors"
                        aria-label="Next Page"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    @endif
</div>
