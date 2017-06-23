# REST PROJECT

Being on Development Progress
That fixing `RETIRED` previous project.

## REQUIREMENTS

- Php `>=7.0`
- Php `PDO` extension (for database)
- Php `PCRE` extension

## SUGGESTS

- Php `cURL` Extension (for guzzle)
- Php `openSSL` Extension (for encryption)
- Php `mbString` extension (for back compatibility)
- Php `mbCrypt` extension (for back compatibility)
- Php `iconV` extension (for better sanitation characters conversion)

## INSTALLATION

Use composer to install, go to script directory and run:

```bash
composer install --no-dev --optimize-autoloader
```

If there was additional file on App/(`Core` or `Model`) directory, please update composer autoload


```bash
composer dump-autoload --optimize-autoloader
```

## VERSION
See [VERSION](VERSION)

## LICENSE

[MIT License](LICENSE)
