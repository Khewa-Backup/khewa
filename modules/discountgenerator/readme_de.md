INSTALLATION
-----------------
Der Standardprozess der Installation, der für alle Prestashop-Modulen gemeinsam ist, wird zum "Generator der Rabatte" verwendet. Wenn Sie "Multishop" verwenden, gehen Sie zu "Konfiguration" des Moduls (Knopf "Einstellungen") und aktivieren Sie das Kontrollkästchen "Hier folgendes Modul aktivieren: all shops". Auf diese Weise können Sie Rabatte für jeden Shop einzeln oder für alle Shops gleichzeitig generieren. Wenn Sie das Modul für eine Weile nicht mehr verwenden möchten, klicken Sie auf den Knopf "Deaktivieren".

Wichtig: Wenn Sie das Modul "Generator der Rabatte" aus irgendeinem Grund vollständig löschen möchten, müssen Sie es zuerst DEINSTALLIEREN und dann LÖSCHEN. Diese Vorgehensweise ist wichtig, um die dem Modul zugeordneten Datenbanktabellen zu bereinigen.

Wichtig: Da das Modul "Generator der Rabatte" die integrierten Funktionen der Warenkorbregeln erweitert, müssen Sie die Optionen "Nicht von PrestaShop entwickelte Module deaktivieren" und "Alle Overrides deaktivieren" im Abschnitt "Erweiterte Einstellungen" > "Leistung" > "Debug-Modus" deaktivieren.

BESCHREIBUNG
-----------------
Mit dem Modul "Generator der Rabatte" können Sie viele Rabatte (Warenkorbregeln) mit eindeutigen Gutscheincodes generieren. Sie können Rabatte für ein bestimmtes Produkt, eine Gruppe ausgewählter Produkte, eine bestimmte Kategorie oder die gesamte Bestellung generieren oder andere Bedingungen angeben, die für die Standardkorbregeln verfügbar sind.

Sie können bestimmen:
- Wie viele Rabatte mit eindeutigen Codes Sie generieren möchten
- Struktur der eindeutigen Codes (Kombination von Zahlen und Buchstaben)
- wie oft der Rabatt vom Kunden genutzt werden kann usw.

EINSTELLUNGEN VOM MODUL
------------------
Dieses Modul wird direkt auf der Registerkarte Katalog > Rabatte installiert und erweitert die Grundfunktionen.

1. Gehen Sie in Prestashop 1.7 zur "Katalog" > "Rabatte" und klicken Sie auf "Neue Warenkorbregel hinzufügen". Gehen Sie in Prestashop 1.5 - 1.6 zur "Preisregeln" > "Warenkorb Preisregeln" und klicken Sie auf "Neue Warenkorbregel hinzufügen".
2. Um Rabatte mithilfe vom "Generator der Rabatte" zu erstellen, aktivieren Sie das Kontrollkästchen "Erstellen Sie viele einzigartige Rabatte" und füllen Sie die neuen Felder auf der Seite aus.
3. Füllen Sie die neuen Pflichtfelder aus:
  - Die Gesamtzahl der eindeutigen Rabatte: Die Anzahl der zu generierenden Rabatte.
    - Code-Konfiguration:
        - Präfix: beliebige Buchstaben oder Zahlen sind erlaubt. Die folgenden Zeichen sind nicht erlaubt: ^!,;? = + () @ "° {} _ $%. Das Präfix ist ein fester Bestandteil Ihres Codes. Es gilt für alle Rabatte.
        - Maske: Eine Folge von X und/oder Y, die die Struktur des Codes definiert. X ist eine beliebige Zahl, Y ist ein beliebiger Buchstabe des lateinischen Alphabets. X und Y werden zufällig generiert, um Ihre Codes eindeutig zu machen. Beispiel: Wenn Präfix = TEST- und Maske = XXYY, generiert das Modul Codes wie TEST-96FA, TEST-27ME usw.
4. Stellen Sie sicher, dass alle anderen erforderlichen Felder korrekt ausgefüllt sind.
5. Speichern Sie das Formular. Das Modul generiert die Anzahl der von Ihnen angegebenen Rabatte.

EXPORT VON LISTEN
------------------
Alle generierten Rabatte werden in der Tabelle "Modulverlauf" am unteren Rand der Modulkonfigurationsseite angezeigt.

Es gibt drei Arten von Listen, die als CSV-Dateien geladen werden:

- "Alle" - zeigt alle generierten Rabatte, Start- und Enddaten sowie die Art der Rabatte an. Diese Liste wird zum Zeitpunkt der Erstellung des Rabatts erstellt und ändert sich nicht.
- "Gebraucht" - zeigt nur die Rabatte an, die von Kunden verwendet wurden, mit einem Namen und einer E-Mail-Adresse. Diese Liste ist dynamisch und wird beim Herunterladen aktualisiert.
- "Ungebraucht" - zeigt nur die Rabatte an, die noch nicht verwendet wurden. Diese Liste ist dynamisch und wird beim Herunterladen aktualisiert.

KONTAKT
------------------
Support: Verwenden Sie das Addons-Konto, um die Identifizierungsnummer der Bestellung festzulegen: https://addons.prestashop.com/de/order-history.