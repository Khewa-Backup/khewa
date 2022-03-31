INSTALLAZIONE
-----------------
Si applica il processo di installazione standard comune a tutti i moduli Prestashop Al "Generatore di Sconti". Se utilizzi "Multinegozio", vai su "Configurazione" del modulo (il pulsante "Configura") e selezioni la casella "Attiva il modulo nel contesto di questo negozio: all shops.". Ciò ti consentirà di generare gli sconti per qualsiasi negozio singolarmente o per tutti contemporaneamente. Se è necessario interrompere l'utilizzo del modulo per un po', utilizzare il pulsante "Disabilita".

Importante: se per qualsiasi motivo desideri rimuovere completamente il modulo "Generatore di Sconti", prima DISINSTALLA e poi ELIMINA. Questa procedura è importante per ripulire le tabelle del database associate al modulo.

Importante: poiché il modulo "Generatore di Sconti" espande le funzioni integrate delle regole del carrello, è necessario disabilitare le opzioni "Disattiva tutti gli override" e "Disattiva moduli non nativi di PrestaShop" nelle opzioni "Parametri Avanzati" > "Prestazioni" > "Modalità di debug".

DESCRIZIONE
-----------------
Il modulo "Generatore di Sconti" ti consente di generare molti sconti (regole del carrello) con codici promozionali univoci. È possibile generare sconti per un prodotto specifico, un gruppo di prodotti selezionati, una categoria specifica o l'intero ordine oppure specificare eventuali altre condizioni disponibili per le regole del carrello standard.

Puoi determinare:
- quanti sconti con codici univoci vuoi generare
- struttura di codici univoci (combinazione di numeri e lettere)
- quante volte lo sconto può essere utilizzato dal cliente, ecc.

IMPOSTAZIONI DEL MODULO
-----------------
Questo modulo è installato direttamente nella scheda Catalogo > Sconti ed estende le funzioni di base.

1. In Prestashop 1.7, vai alla scheda "Catalogo" > "Buoni sconto" e fai clic su "Aggiungi una nuova regola carrello". In Prestashop 1.5 - 1.6, vai al menu "Regole prezzi" > "Regole Carrello" e fai clic su "Aggiungi una nuova regola carrello".
2. Per creare sconti utilizzando il "Generatore di Sconti", seleziona la casella "Genera molti sconti unici" e compila i nuovi campi che verranno visualizzati nella pagina.
3. Compila i nuovi campi richiesti:
    - Il numero totale di sconti unici: il numero di sconti da generare.
    - Configurazione del codice:
        - Prefisso: sono consentite lettere o numeri. Non sono ammessi i seguenti caratteri: ^!,;? = + () @ "° {} _ $%. Il prefisso è una parte stabile del tuo codice, è comune a tutti gli sconti.
        - Maschera: una sequenza di X e / o Y che definisce la struttura del codice. X è qualsiasi numero, Y è qualsiasi lettera dell'alfabeto latino. X e Y verranno generati casualmente per rendere unici i tuoi codici. Esempio: se Prefisso = TEST- e Mask = XXYY, il modulo genererà codici come TEST-96FA, TEST-27ME, ecc.
4. Assicurati che tutti gli altri campi richiesti siano compilati correttamente.
5. Salva il modulo. Il modulo genererà il numero di sconti che hai indicato.

ESPORTAZIONI DEI ELENCHI
-----------------
Tutti gli sconti generati verranno visualizzati nella tabella "Cronologia del modulo" situata nella parte inferiore della pagina di configurazione del modulo.

Esistono tre tipi di elenchi caricati come file CSV:

- “Tutto”: visualizza tutti gli sconti generati, le date di inizio e fine, il tipo di sconto. Questo elenco viene generato al momento della creazione dello sconto e non cambia.
- "Utilizzato": visualizza solo gli sconti utilizzati dai clienti, con un nome e un indirizzo e-mail. Questo elenco è dinamico e viene aggiornato durante il download.
- "Non utilizzato": visualizza solo gli sconti non ancora utilizzati. Questo elenco è dinamico e viene aggiornato durante il download.

CONTATTI
-----------------
Supporto: utilizzare l'account Addons per aiutarci a impostare l'ID ordine: https://addons.prestashop.com/en/order-history.