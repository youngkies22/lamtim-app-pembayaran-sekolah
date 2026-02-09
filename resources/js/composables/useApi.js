import { ref } from 'vue';

/**
 * Composable for handling API requests with loading and error states
 */
export function useApi() {
    const loading = ref(false);
    const error = ref(null);

    /**
     * Wrapper for API requests with automatic loading and error handling
     * @param {Function} requestFn - The API function to call
     * @param {...any} args - Arguments to pass to the API function
     * @returns {Promise<any>} The response data
     */
    const handleRequest = async (requestFn, ...args) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await requestFn(...args);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'Terjadi kesalahan';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Reset error state
     */
    const clearError = () => {
        error.value = null;
    };

    return {
        loading,
        error,
        handleRequest,
        clearError,
    };
}
