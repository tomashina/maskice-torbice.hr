# maskice-torbice.hr

Izvorni kod postojece OpenCart 3 trgovine `maskice-torbice.hr` s Basel temom.

Ovaj repozitorij je namijenjen verzioniranju aplikacijskog koda i sigurnom povlacenju promjena preko cPanel Git Version Controla. Nije potpuna sigurnosna kopija produkcije niti samostalan instalacijski paket.

## Namjerno nije u repozitoriju

- produkcijski `config.php`, `admin/config.php`, `php.ini` i drugi pristupni podaci
- baza podataka i SQL izvozi
- `image/catalog` i generirani `image/cache`
- OpenCart runtime u `storage` i `system/storage`
- korisnicki uploadi, sitemapovi, logovi, arhive i sigurnosne kopije
- lokalne IDE postavke

Ti podaci ostaju na serveru i moraju se zasebno sigurnosno kopirati.

## cPanel deployment

Datoteka `.cpanel.yml` pokrece `tools/deploy-cpanel.sh`. Skripta je ogranicena na produkcijski direktorij:

`/home/maskice2/maskice-torbice.hr/upload`

Skripta:

- kopira samo datoteke koje Git prati
- nikad ne brise produkcijske datoteke
- ne dira konfiguraciju, slike proizvoda, runtime, uploadove ni logove
- preskace datoteke koje su vec jednake
- prije svake stvarne zamjene sprema prethodnu verziju izvan web direktorija u `/home/maskice2/.deploy-backups/maskice-torbice.hr/`

Deployment ne mijenja bazu i ne osvjezava OpenCart OCMOD cache. OCMOD paketi instaliraju se kroz OpenCart administraciju, a nakon instalacije treba osvjeziti Modifications i ocistiti predmemoriju teme.

Prije prvog produkcijskog deploymenta obavezno pregledati Git diff i imati svjezu sigurnosnu kopiju baze i datoteka.

## Pravni OCMOD paketi

Izvor i reproducibilna izrada paketa za obrazac jednostranog raskida ugovora
i zakonsko jamstvo nalaze se u `extensions/ocmod/`. Paketi se instaliraju kroz
OpenCart Installer, nakon čega treba osvježiti Modifications i predmemoriju teme.
