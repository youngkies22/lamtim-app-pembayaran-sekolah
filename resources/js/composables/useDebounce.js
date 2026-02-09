import { ref, watch } from 'vue';

/**
 * Composable for debouncing values
 * @param {any} initialValue - Initial value
 * @param {number} delay - Debounce delay in milliseconds
 */
export function useDebounce(initialValue = '', delay = 300) {
    const value = ref(initialValue);
    const debouncedValue = ref(initialValue);
    let timeout = null;

    watch(value, (newValue) => {
        if (timeout) {
            clearTimeout(timeout);
        }
        timeout = setTimeout(() => {
            debouncedValue.value = newValue;
        }, delay);
    });

    return {
        value,
        debouncedValue,
    };
}

/**
 * Creates a debounced function
 * @param {Function} fn - Function to debounce
 * @param {number} delay - Debounce delay in milliseconds
 */
export function debounce(fn, delay = 300) {
    let timeout = null;
    return function (...args) {
        if (timeout) {
            clearTimeout(timeout);
        }
        timeout = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}
