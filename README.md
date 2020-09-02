[**English version**][ext0]

# Moduł PayU dla Magento 1.6.0+
``Moduł jest wydawany na licencji GPL.``

**Jeżeli masz jakiekolwiek pytania lub chcesz zgłosić błąd zapraszamy do kontaktu z naszym [wsparciem technicznym][ext8].**

* Jeżeli używasz Magneto w wersji >2.0.6, 2.1, 2.2 proszę skorzystać z [pluginu dla wersji >2.0.6, 2.1, 2.2][ext6]
* Jeżeli używasz Magneto w wersji 2.3 proszę skorzystać z [pluginu dla wersji 2.3][ext9]
* Jeżeli używasz Magneto w wersji 2.4 proszę skorzystać z [pluginu dla wersji 2.4][ext10]

## Spis treści

1. [Cechy](#cechy)
1. [Wymagania](#wymagania)
1. [Instalacja](#instalacja)
1. [Konfiguracja](#konfiguracja)
    * [Parametry](#parametry)
1. [Informacje o cechach](#informacje-o-cechach)
    * [Kolejność metod płatności](#kolejność-metod-płatności)

## Cechy
Moduł płatności PayU dodaje do Magento opcję płatności PayU.

Możliwe są następujące operacje:
  * Utworzenie płatności (wraz z rabatami)
  * Odebranie lub odrzucenie płatności (w przypadku wyłączonego autoodbioru)
  * Utworzenie zwrotu online (pełnego lub częściowego)

Moduł dodaje dwie metody płatności:

![methods][img0]
  * **Zapłać przez PayU** - wybór metody płatności i przekierowanie do banku / formatkę kartową lub przekierowanie na stronę wyboru metod płatności w PayU
  * **Zapłać kartą** - bezpośrednie przekierowanie na formularz płatności kartą

## Wymagania

**Ważne:** Moduł ten działa tylko z punktem płatności typu `REST API` (Checkout), jeżeli nie posiadasz jeszcze konta w systemie PayU [**zarejestruj się w systemie produkcyjnym**][ext4] lub [**zarejestruj się w systemie sandbox**][ext5]

Do prawidłowego funkcjonowania modułu wymagane są następujące rozszerzenia PHP: [cURL][ext1] i [hash][ext2].

## Instalacja

### Opcja 1
**Przeznaczona dla użytkowników z dostępem poprzez FTP do instalacji Magento**

1. Pobierz moduł z [repozytorium GitHub][ext3] jako plik zip
1. Rozpakuj pobrany plik
1. Połącz się z serwerem ftp i skopiuj katalogi `app`, `lib` oraz `skin` z rozpakowanego pliku do katalogu głównego swojego sklepu Magento
1. W celu aktualizacji listy dostępnych wtyczek należy wyczyścić cache:
    * Przejdź do strony administracyjnej swojego sklepu Magento [http://adres-sklepu/admin].
    * Przejdź do **System** > **Cache Management**.
    * Naciśnij przycisk **Flush Magento Cache**.

### Opcja 2
**Z użyciem skryptu modman**

Moduł PayU zawiera konfigurację umożliwiającą instalację poprzez skrypt `modman`.
W celu instalcji z użyciem `modman` proszę skozystać z dokumentacji skryptu `modman`.

#### UWAGA
Jeżeli używasz opcji kompilacji po przejściu do **System** > **Tools** > **Compilation** należy nacisnąć przycisk **Run Compilation Process**.

Dodatkowo jeżeli aktualizujesz moduł ze starszej wersji należy z katalogu `includes/src` usunąć katalog `OpenPayu` oraz wszystkie pliki zaczynające się na `OpenPayU`

## Konfiguracja

1. Przejdź do strony administracyjnej swojego sklepu Magento [http://adres-sklepu/admin].
1. Przejdź do  **System** > **Configuration**.
3. Na stronie **Configuration** w menu po lewej stronie w sekcji **Sales** wybierz **Payment Methods**.
4. Na liście dostępnych metod płatności należy wybrać **PayU** lub **PayU - karty** w celu konfiguracji parametrów wtyczki.
5. Naciśnij przycisk `Save config`.

### Parametry

#### Główne parametry

| Parameter | Opis |
|---------|-----------|
| Czy włączyć wtyczkę? | Określa czy metoda płatności będzie dostępna w sklepie na liście płatności. |
| Tryb testowy (Sandbox) | Określa czy płatności będą realizowane na środowisku testowym (sandbox) PayU. |

#### Parametry dla metody `Zapłać przez PayU`

| Parameter | Opis |
|---------|-----------|
| Wyświetlaj metody płatności | Określa czy ma być wyświetlana lista bramek płatności podczas procesu zamówienia w Magento |
| Kolejność metod płatności | Określa kolejnośc wyświetlanych metod płatności [więcej informacji](#kolejność-metod-płatności). |

#### Parametry punktu płatności (POS)

| Parameter | Opis |
|---------|-----------|
| Id punktu płatności| Identyfikator POS-a z systemu PayU |
| Drugi klucz MD5 | Drugi klucz MD5 z systemu PayU |
| OAuth - client_id | client_id dla protokołu OAuth z systemu PayU |
| OAuth - client_secret | client_secret for OAuth z systemu PayU |

#### Parametry punktu płatności (POS) - Tryb testowy (Sandbox)
Dostępne gdy parametr `Tryb testowy (Sandbox)` jest ustawiony na `Tak`.

| Parameter | Opis |
|---------|-----------|
| Id punktu płatności| Identyfikator POS-a z systemu PayU |
| Drugi klucz MD5 | Drugi klucz MD5 z systemu PayU |
| OAuth - client_id | client_id dla protokołu OAuth z systemu PayU |
| OAuth - client_secret | client_secret for OAuth z systemu PayU |

## Informacje o cechach

### Kolejność metod płatności
W celu ustalenia kolejności wyświetlanych ikon matod płatności należy podać symbole metod płatności oddzielając je przecinkiem. [Lista metod płatności][ext7].

<!--LINKS-->

<!--topic urls:-->

<!--external links:-->
[ext0]: README.EN.md
[ext1]: http://php.net/manual/en/book.curl.php
[ext2]: http://php.net/manual/en/book.hash.php
[ext3]: https://github.com/PayU-EMEA/plugin_magento
[ext4]: https://www.payu.pl/oferta-handlowa
[ext5]: https://secure.snd.payu.com/boarding/?pk_campaign=Plugin-Github&pk_kwd=Magento#/form
[ext6]: https://github.com/PayU-EMEA/plugin_magento_2
[ext7]: http://developers.payu.com/pl/overview.html#paymethods
[ext8]: https://www.payu.pl/pomoc
[ext9]: https://github.com/PayU-EMEA/plugin_magento_23
[ext10]: https://github.com/PayU-EMEA/plugin_magento_24

<!--images:-->
[img0]: readme_images/methods.png
