[**English version**][ext0]

# Moduł PayU dla Magento 1.6.0+
``Moduł jest wydawany na licencji GPL.``

**Jeżeli masz jakiekolwiek pytania lub chcesz zgłosić błąd zapraszamy do kontaktu z naszym wsparciem pod adresem: tech@payu.pl.**

* Jeżeli używasz Magneto w wersji 2.x proszę skorzystać z [pluginu dla wersji 2.x][ext6]

## Spis treści

1. [Cechy](#cechy)
1. [Wymagania](#wymagania)
1. [Instalacja](#instalacja)
1. [Konfiguracja](#konfiguracja)
    * [Parametry](#parametry)

## Cechy
Moduł płatności PayU dodaje do Magento opcję płatności PayU.

Możliwe są następujące operacje:
  * Utworzenie płatności (wraz z rabatami)
  * Odebranie lub odrzucenie płatności (w przypadku wyłączonego autoodbioru)
  * Utworzenie zwrotu online (pełnego lub częściowego)

Moduł dodaje dwie metody płatności:
  
![methods][img0]
  * **Zapłać przez PayU** - przekierowanie na stronę wyboru metod płatności w PayU
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
1. Jeżeli używasz opcji kompilacji po przejściu do **System** > **Tools** > **Compilation** należy nacisnąć przycisk **Run Compilation Process**.

### Opcja 2
**Z użyciem Magento Connect**

1. Przejdź do strony administracyjnej swojego sklepu Magento [http://adres-sklepu/admin].
1. Przejdź do **System** > **Magento Connect** > **Magento Connect Manager**.
1. W sekcji **Install New Extensions section** do pola `Paste the extension key to install` należy wkleić `http://connect20.magentocommerce.com/community/PayU_Account` i wcisnąć przycisk `Install`
1. Po chwili pojawi się informacji o wtyczce. W celu instalacji należy nacisnąć przycisk `Proceed`

### Opcja 3
**Z użyciem skryptu modman**

Moduł PayU zawiera konfigurację umożliwiającą instalację poprzez skrypt `modman`.
W celu instalcji z użyciem `modman` proszę skozystać z dokumentacji skryptu `modman`.

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


<!--LINKS-->

<!--topic urls:-->

<!--external links:-->
[ext0]: README.EN.md
[ext1]: http://php.net/manual/en/book.curl.php
[ext2]: http://php.net/manual/en/book.hash.php
[ext3]: https://github.com/PayU/plugin_magento_160
[ext4]: https://secure.payu.com/boarding/#/form&pk_campaign=Plugin-Github&pk_kwd=Magento
[ext5]: https://secure.snd.payu.com/boarding/#/form&pk_campaign=Plugin-Github&pk_kwd=Magento
[ext6]: https://github.com/PayU/plugin_magento_2

<!--images:-->
[img0]: readme_images/methods.png
