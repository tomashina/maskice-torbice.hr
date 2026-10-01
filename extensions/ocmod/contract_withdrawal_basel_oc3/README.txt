AG media — obrazac za jednostrani raskid ugovora (OpenCart 3 / Basel)
====================================================================

Verzija: 1.2.0

Namjena
-------
Modul dodaje javni obrazac za jednostrani raskid ugovora, pregled zahtjeva
prije konačne potvrde, potvrdu e-poštom i zaseban administratorski popis sa
statusima i poviješću obrade.

Kompatibilnost
--------------
- OpenCart 3.0.3.8
- Basel tema s Bootstrapom 3
- hrvatske jezične oznake hr-HR i hr-hr te en-gb

Instalacija / nadogradnja
------------------------
1. U administraciji otvorite Extensions > Installer.
2. Učitajte arhivu agmedia_contract_withdrawal_basel_oc3_v1.2.0.ocmod.zip.
3. Otvorite Extensions > Modifications i kliknite Refresh.
4. Očistite Basel/SASS i OpenCart cache ako se novi izgled ne prikaže odmah.

OCMOD koristi isti kod "dryzen_contract_withdrawal" kao verzija 1.0.2, pa se
postojeća modifikacija nadograđuje umjesto stvaranja druge kopije.

Rute
----
- Obrazac: index.php?route=extension/account/contract_withdrawal
- Administracija: index.php?route=extension/sale/contract_withdrawal

Napomene
--------
- Tablice contract_withdrawal i contract_withdrawal_history odvojene su od
  standardnih OpenCart povrata i automatski se provjeravaju/izrađuju.
- Ako je OpenCart CAPTCHA uključena za stranicu "Returns", koristi se i na
  ovom obrascu. CAPTCHA se provjerava prije pregleda, a sigurna oznaka sesije
  prenosi se do konačne potvrde.
- Obrazac koristi jednokratnu CSRF oznaku sesije koja se mijenja nakon uspješnog
  slanja zahtjeva.
- Administratorska stavka dostupna je korisnicima koji imaju pristup standardnim
  povratima ili izričitu dozvolu za extension/sale/contract_withdrawal.
- Nakon instalacije provjerite slanje poruka na adresu trgovine i adresu kupca.
