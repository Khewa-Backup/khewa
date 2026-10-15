INSTALOWANIE
-----------------
Standardowy proces instalacji wspólny dla wszystkich modułów Prestashop dotyczy „Generatora Rabatów”.Jeśli korzystasz z „Multi-store”, przejdź do „Konfiguracja” modułu (przycisk „Konfiguruj”) i wybierz ikonę „Aktywuj moduł dla tego kontekstu sklepu: all shops.”. To pozwoli wygenerować Rabaty dla któregokolwiek ze sklepów pojedynczo lub wszystkie naraz. Jeśli musisz na chwilę przestać korzystać z modułu, użyj przycisku „Wyłącz”.

Ważne: Jeśli z jakiegoś powodu chcesz całkowicie usunąć moduł „Generatora Rabatów”, najpierw ODINSTALUJ go, a następnie Usuń. Procedura ta jest ważna, aby oczyścić tabele w bazie danych, które są związane z modułem.

Ważne: Ponieważ moduł „Generatora Rabatów” rozszerza wbudowane funkcje reguł koszyka, należy wyłączyć opcje „Wyłącz wszystkie nadpisywania” i „Wyłącz moduły nie od PrestaShop” w opcjach „Zaawansowane” > „Wydajność” > „Tryb debugowania”.

OPIS
-----------------
Moduł „Generatora Rabatów” umożliwia generowanie wielu rabatów (reguł koszyka) z unikalnymi kodami promocyjnymi. Możesz generować rabaty dla dowolnego produktu, grupy wybranych produktów, określonej kategorii lub całego zamówienia lub określić inne warunki dostępne dla standardowych reguł koszyka.

Możesz zrozumieć:
- ile rabatów z unikalnymi kodami chcesz wygenerować
- struktura unikalnych kodów (kombinacja cyfr i liter)
- ile razy klient może skorzystać ze zniżki itp.

USTAWIENIA MODUŁU
-----------------
Moduł ten jest montowany bezpośrednio na karcie Katalog>Rabaty i rozwija podstawowe funkcje.

1. W Prestashop 1.7 przejdź do Katalogu > Kody rabatowe i kliknij „Dodaj nową regułę koszyka”. PrestaShop 1.5 - 1.6, przejdź do „Rabaty grupowe” > „Kody rabatowe” kartę menu i naciśnij przycisk „Dodaj nową regułę koszyka.”
2. Aby utworzyć rabaty za pomocą „Generatora Rabatów”, zaznacz pole „Generuj wiele unikalnych rabatów” i wypełnij nowe pola, które pojawią się na stronie.
3. Wypełnij nowe wymagane pola:
    - łączna liczba unikalnych zniżki rabaty ilościowe do generowania.
    - Konfiguracja kodu:
        - Prefiks: Dozwolone są dowolne litery lub cyfry. Następujące znaki są niedozwolone: ^!,;? = + () @ "° {} _ $%. Prefiks jest stabilną częścią twojego kodu, jest wspólny dla wszystkich rabatów.
        - Maska: sekwencja X i / lub Y, która definiuje strukturę kodu. X to dowolna liczba, Y to dowolna litera alfabetu łacińskiego. X i Y będą generowane losowo, aby Twoje kody były unikalne. Przykład: jeśli Prefiks = TEST- i Maska = XXYY, moduł wygeneruje kody takie jak TEST-96FA, TEST-27ME itp.
4. Upewnij się, że wszystkie pozostałe wymagane pola są wypełnione poprawnie.
5. Zapisz formularz. Moduł generuje szereg zniżek, które zostały określone.

EKSPORT LISTY
-----------------
Wszystkie wygenerowane rabaty zostaną wyświetlone w tabeli historii modułów, która znajduje się na dole strony konfiguracji modułu.

Istnieją trzy rodzaje list, do pobrania jako pliki CSV:

- „Wszystkie” - wyświetla wszystkie wygenerowane rabaty, daty rozpoczęcia i zakończenia, rodzaj rabatu. Ta lista jest generowana w momencie tworzenia rabatu i nie ulega zmianie.
- „Używany” - wyświetla tylko te rabaty, które zostały wykorzystane przez klientów, z nazwą i adresem e-mail. Ta lista jest dynamiczna i jest aktualizowana podczas pobierania.
- „Nieużywany” - wyświetla tylko te rabaty, które nie zostały jeszcze wykorzystane. Ta lista jest dynamiczna i jest aktualizowana podczas pobierania

KONTAKTY
-----------------
Pomoc: Proszę używać do komunikowania Addons konta, aby pomóc nam ustalić kolejność ID: https://addons.prestashop.com/en/order-history