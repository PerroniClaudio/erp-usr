import axios from "axios";
window.axios = axios;

// Configura l'header X-Requested-With
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Funzione per ottenere il token CSRF dal DOM
function getCsrfToken() {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    return token ? token.content : null;
}

// Configura automaticamente il token CSRF per tutte le richieste
const csrfToken = getCsrfToken();
if (csrfToken) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;
} else {
    console.error(
        "CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token"
    );
}

// Interceptor per le richieste: aggiorna sempre il token CSRF prima di ogni richiesta
window.axios.interceptors.request.use(
    function (config) {
        const token = getCsrfToken();
        if (token) {
            config.headers["X-CSRF-TOKEN"] = token;
        }
        return config;
    },
    function (error) {
        return Promise.reject(error);
    }
);

// Interceptor per le risposte: gestisce errori 419 (CSRF token mismatch)
window.axios.interceptors.response.use(
    function (response) {
        return response;
    },
    async function (error) {
        if (error.response && error.response.status === 419) {
            console.warn(
                "CSRF Token Mismatch - Attempting to refresh token..."
            );

            // Tenta di refresh del token se la funzione è disponibile
            if (typeof window.refreshCsrfToken === "function") {
                const newToken = await window.refreshCsrfToken();
                if (newToken) {
                    // Riprova la richiesta originale con il nuovo token
                    const originalRequest = error.config;
                    originalRequest.headers["X-CSRF-TOKEN"] = newToken;
                    return window.axios.request(originalRequest);
                }
            }

            // Se il refresh del token fallisce, ricarica la pagina
            console.error("CSRF Token refresh failed - Reloading page...");
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
