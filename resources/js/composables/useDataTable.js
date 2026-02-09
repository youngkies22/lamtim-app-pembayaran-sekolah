import { ref, reactive, watch, onMounted } from 'vue';

export function useDataTable(apiDatatableFunction, initialParams = {}) {
    const autoLoad = initialParams.autoLoad !== false; // Default true, can be disabled
    const data = ref([]);
    const totalRecords = ref(0);
    const loading = ref(false);
    const error = ref(null);

    const params = reactive({
        draw: 0,
        start: 0,
        length: 10, // Per page
        search: { value: '' },
        order: [{ column: 0, dir: 'asc' }],
        columns: [], // Should be populated by the component using this composable
        ...initialParams,
    });

    const fetchData = async (requestParams) => {
        const response = await apiDatatableFunction(requestParams);
        return response.data;
    };

    const loadData = async () => {
        loading.value = true;
        try {
            params.draw++;
            // Convert params to simple format (not DataTables format)
            // Extract search value if it's an object
            const searchValue = typeof params.search === 'object' && params.search !== null 
                ? (params.search.value || '') 
                : (params.search || '');
            
            // Build request params - use simple query params format
            const requestParams = {
                draw: params.draw,
                start: params.start,
                length: params.length,
                search: searchValue, // Send as simple string, not object
                order: params.order,
                columns: params.columns,
            };
            
            // Add any additional filters from params (idKelas, idJurusan, etc.)
            Object.keys(params).forEach(key => {
                if (!['draw', 'start', 'length', 'search', 'order', 'columns'].includes(key)) {
                    const value = params[key];
                    // Only add if value is not null/undefined/empty
                    if (value !== null && value !== undefined && value !== '') {
                        requestParams[key] = value;
                    }
                }
            });
            const response = await fetchData(requestParams);
            data.value = response.data || [];
            totalRecords.value = response.recordsFiltered || response.recordsTotal || 0;
        } catch (e) {
            error.value = e;
            console.error('Error loading datatable:', e);
            data.value = [];
        } finally {
            loading.value = false;
        }
    };

    const handlePageChange = (page) => {
        params.start = (page - 1) * params.length;
        loadData();
    };

    const handlePerpageChange = (newLength) => {
        params.length = newLength;
        params.start = 0; // Reset to first page
        loadData();
    };

    const handleSearch = (searchValue) => {
        params.search.value = searchValue;
        params.start = 0; // Reset to first page
        loadData();
    };

    const handleSort = (column, direction) => {
        params.order = [{ column, dir: direction }];
        loadData();
    };

    // Watch for changes in params (if any custom params are added)
    watch(params, () => {
        // Only reload if significant changes, e.g., not just draw increment
        // This can be optimized further if needed
    }, { deep: true });

    onMounted(() => {
        if (autoLoad) {
            loadData();
        }
    });

    return {
        data,
        totalRecords,
        loading,
        error,
        params,
        loadData,
        handlePageChange,
        handlePerpageChange,
        handleSearch,
        handleSort,
    };
}
