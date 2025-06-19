# Risoluzione errore InvalidStateException di Socialite

## Problema

L'errore `Laravel\Socialite\Two\InvalidStateException` si verifica in produzione quando si usa l'autenticazione Microsoft OAuth.

## Cause principali

1. **Sessioni non persistenti** tra il redirect e il callback
2. **Configurazione incorretta dei cookie** in ambiente HTTPS
3. **URL di callback non corretto**
4. **Problemi con il driver delle sessioni**

## Soluzioni implementate

### 1. Gestione degli errori migliorata

-   Aggiunto try-catch specifico per `InvalidStateException`
-   Logging dettagliato per debugging
-   Redirect con messaggi di errore appropriati

### 2. Middleware personalizzato

-   Creato `EnsureSocialiteSession` middleware
-   Verifica e inizializza le sessioni per le rotte OAuth
-   Recupera lo stato dalla richiesta se necessario

### 3. Rotta alternativa senza stato

-   Rotta `/auth/microsoft/callback-no-state` per emergenze
-   Usa `stateless()` per bypassare la validazione dello stato

### 4. Comando di test

-   `php artisan socialite:test-config` per verificare la configurazione

## Passi per risolvere in produzione

### 1. Verifica la configurazione delle sessioni

```bash
# Nel file .env di produzione:
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.tuodominio.com
```

### 2. Assicurati che la tabella sessions esista

```bash
php artisan migrate
```

### 3. Verifica la configurazione Microsoft OAuth

```bash
# Nel file .env:
MICROSOFT_CLIENT_ID=your_client_id
MICROSOFT_CLIENT_SECRET=your_client_secret
MICROSOFT_REDIRECT_URI=https://tuodominio.com/auth/microsoft/callback
MICROSOFT_TENANT=common
```

### 4. Testa la configurazione

```bash
php artisan socialite:test-config
```

### 5. Pulisci cache e sessioni

```bash
php artisan cache:clear
php artisan config:clear
php artisan session:clear
```

### 6. In caso di emergenza

Se il problema persiste, usa temporaneamente la rotta senza stato:

-   Cambia l'URL di callback in Microsoft Azure da:
    `https://tuodominio.com/auth/microsoft/callback`
-   A:
    `https://tuodominio.com/auth/microsoft/callback-no-state`

## Debugging avanzato

### 1. Attiva i log dettagliati

```bash
# Nel file .env:
LOG_LEVEL=debug
```

### 2. Controlla i log

```bash
tail -f storage/logs/laravel.log
```

### 3. Verifica i cookie del browser

-   Apri DevTools → Application → Cookies
-   Verifica che il cookie di sessione persista tra le richieste

## Problematiche comuni

### 1. Cookie non persistenti in HTTPS

-   Assicurati che `SESSION_SECURE_COOKIE=true` in produzione
-   Verifica che il certificato SSL sia valido

### 2. Dominio dei cookie

-   Imposta `SESSION_DOMAIN` correttamente
-   Per sottodomini usa `.tuodominio.com`

### 3. Load balancer / proxy

-   Se usi un load balancer, assicurati che:
    -   Le sessioni siano condivise (database/redis)
    -   I cookie siano inoltrati correttamente
    -   L'IP del client sia preservato

### 4. Cache delle configurazioni

-   Sempre eseguire `php artisan config:clear` dopo modifiche alla configurazione

## Test rapido

1. Accedi a `/auth/redirect`
2. Completa l'autenticazione Microsoft
3. Verifica che il callback funzioni senza errori
4. Controlla i log per eventuali messaggi di debug
