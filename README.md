# REST PROJECT 

[![Build Status](https://travis-ci.org/PentagonalProject/rest.svg?branch=master)](https://travis-ci.org/PentagonalProject/rest)
[![Coverage Status](https://coveralls.io/repos/github/PentagonalProject/rest/badge.svg?branch=master)](https://coveralls.io/github/PentagonalProject/rest?branch=master)

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

If there was additional file on App/(`Core`) directory, please update composer autoload


```bash
composer dump-autoload --optimize-autoloader
```

## CONTRIBUTE

See [CONTRIBUTE.md](CONTRIBUTE.md)

## VERSION

See [VERSION](VERSION)

## LICENSE

[MIT License](LICENSE)
