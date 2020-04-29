# Czarna dziura dla niedobrych botów

![cover](.tbstore/images/image-cover.png)

To prosty, darmowy moduł dla platform Prestashop lub thirty bees. Został stworzony na podstawie poniższej [koncepcji](https://perishablepress.com/blackhole-bad-bots/):

1. Instruujesz roboty odwiedzające twoją witrynę, aby nie odwiedzały konkretnego adres URL;
2. Moduł dodaje na wszystkich stronach **ukryty** link do zakazanej strony, widoczny tylko dla robotów. Inni odwiedzający nigdy go nie zobaczą, chyba że prześledzą dokładnie kod źródłowy strony;
3. Gdy ktokolwiek odwiedzi zakazaną stronę, jego adres IP zostaje dodany do czarnej listy;
4. Odwiedzający z adresów IP zgromadzonych na czarnej liście, nie mogą korzystać z witryny;
5. Administrator sklepu otrzymuje email z informacjami WHOIS o zablokowanym odwiedzającym: adres IP, lokalizacja, sieć itd.;

I to wszystko. Zastawiona pułapka nie wpływa na działanie *dobrych* robotów stosujących się do instrukcji z pliku ```robots.txt```. Natomiast *niedobre* roboty indeksujące będą blokowane i nie będą mogły zbierać informacji z twojej witryny.

## Aktywacja modułu

1. Wprowadź zmiany w pliku ```robots.txt``` w głównm katalogu;

Przed instalacją modułu, należy dodać trzy poniższe linie do pliku ```robots.txt``` np. na jego początku. **UWAGA** - ponowne generowanie pliku ```robots.txt``` w panelu administracyjnym usunie wprowadzone ręcznie zmiany!

```
User-agent: *
Disallow: /blackhole/
Disallow: /modules/blackholebots/blackhole/
```

2. Zainstaluj moduł;

3. Przetestuj działanie modułu.

Otwórz w przeglądarce stronę ```https://www.twojadomena.pl/blackhole/``` (w miejsce ```twojadomena.pl``` wstaw prawidłowy adres sklepu). Jeśli wszystko działa poprawnie, moduł zablokuje dostęp do strony. Aby usunąć blokadę, zresetuj moduł w panelu administracyjnym.

## Moderacja

Moduł nie oferuje interfejsu użytkownika, w którym można zarządzać czarną listą zablokowanych adresów IP. W tym celu niezbędna będzie bezpośrednia edycja tabeli bazy danych ```PREFIX_blackholebots_blacklist```.

## Zgodność z oprogramowaniem

- [thirtybees](https://thirtybees.com);
- [prestashop 1.6.x.x](https://www.prestashop.com);
- [prestashop 1.7.x.x](https://www.prestashop.com).

## Autor

Petr Hučík - [datakick](https://www.getdatakick.com)

### Tłumaczenie na język polski

[cienislaw](cienislaw@post.pl)