{{--
    Reusable Data Table Component

    @param array $columns - Table columns configuration
    @param mixed $data - Table data (collection or array)
    @param string $emptyMessage - Message when no data
    @param bool $searchable - Enable search
    @param bool $sortable - Enable sorting
    @param string $rowActions - Named slot for row actions
--}}

@props([
    'columns' => [],
    'data' => [],
    'emptyMessage' => 'Tidak ada data',
    'searchable' => true,
    'sortable' => true,
    'striped' => true,
    'hover' => true,
])

<div class="w-full" x-data="dataTable()">
    @if($searchable)
        <div class="mb-4 flex flex-col sm:flex-row justify-between gap-4">
            <div class="form-control w-full sm:w-64">
                <input
                    type="text"
                    placeholder="Cari..."
                    class="input input-bordered input-sm"
                    x-model="searchQuery"
                    @input="filterData()"
                />
            </div>
            <div class="flex gap-2">
                {{ $headerActions ?? '' }}
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="table {{ $striped ? 'table-zebra' : '' }} {{ $hover ? 'hover:bg-base-200' : '' }}">
            <thead>
                <tr>
                    @foreach($columns as $key => $column)
                        <th
                            @if($sortable && ($column['sortable'] ?? true))
                                class="cursor-pointer hover:bg-base-200"
                                @click="sortBy('{{ $key }}')"
                            @endif
                        >
                            <div class="flex items-center gap-2">
                                {{ $column['label'] ?? $key }}
                                @if($sortable && ($column['sortable'] ?? true))
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    @if(isset($actions))
                        <th class="text-right">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($data as $item)
                    <tr class="hover">
                        @foreach($columns as $key => $column)
                            <td>
                                @if(isset($column['render']))
                                    {{ $column['render']($item) }}
                                @elseif(isset($column['slot']))
                                    {{ ${'column_' . $key} ?? data_get($item, $key) }}
                                @else
                                    {{ data_get($item, $key) }}
                                @endif
                            </td>
                        @endforeach
                        @if(isset($actions))
                            <td class="text-right">
                                {{ $actions($item) }}
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + (isset($actions) ? 1 : 0) }}" class="text-center py-8 text-base-content/60">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($data, 'links'))
        <div class="mt-4">
            {{ $data->links() }}
        </div>
    @endif
</div>

<script>
function dataTable() {
    return {
        searchQuery: '',
        sortField: null,
        sortDirection: 'asc',

        filterData() {
            // Client-side filtering - for server-side, emit an event
            const event = new CustomEvent('table-search', { detail: { query: this.searchQuery } });
            this.$el.dispatchEvent(event);
        },

        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }

            const event = new CustomEvent('table-sort', {
                detail: { field: this.sortField, direction: this.sortDirection }
            });
            this.$el.dispatchEvent(event);
        }
    }
}
</script>
