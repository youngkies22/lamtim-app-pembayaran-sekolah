import { ref } from 'vue';
import axios from 'axios';

export function useClosingCheck() {
    const isClosed = ref(false);
    const message = ref('');
    const loading = ref(false);

    /**
     * Check if a specific date is closed
     * @param {string|Date} date - Date to check (YYYY-MM-DD or Date object)
     * @returns {Promise<boolean>} - Returns true if closed, false if open
     */
    const checkDateStatus = async (date) => {
        if (!date) return false;
        
        loading.value = true;
        try {
            // Format date if needed, assuming API handles YYYY-MM-DD
            // If date is Date object, format it. If string, use as is (or strict format)
            let dateStr = date;
            if (date instanceof Date) {
                dateStr = date.toISOString().split('T')[0];
            }

            const response = await axios.get('/api/closing/check', { params: { date: dateStr } });
            isClosed.value = response.data.is_closed;
            message.value = response.data.message;
            return response.data.is_closed;
        } catch (e) {
            console.error('Closing check failed:', e);
            // Default to open if check fails to avoid blocking user due to network error, 
            // but backend will still block if really closed.
            return false;
        } finally {
            loading.value = false;
        }
    };

    return {
        isClosed,
        message,
        loading,
        checkDateStatus
    };
}
