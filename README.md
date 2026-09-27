# Toos Boerlage

Nederlandstalige galerie en webshop, gebouwd met PHP zonder framework.

```bash
php -S localhost:8000 router.php
```

De catalogus staat in `includes/catalog.json`. Prijzen, status en teksten zijn daar aan te passen. Afbeeldingen zijn nog een plaatsaanduiding.

Apache gebruikt de schone URL’s uit `.htaccess`. Lokaal regelt `router.php` dezelfde paden.

Betaling, formulierverzending en de nieuwsbrief zijn nog niet gekoppeld. Er worden geen bestellingen of betalingen opgeslagen.
