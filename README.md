# Pharmacy Duty Roster
## Introduction
Pharmacy Duty Roster (PDR) is a web application that allows to operate a duty roster for pharmacies.
PDR started in 2015 as an alternative to a really simple excel sheet without formulas.
PDR aims to be user-friendly but at the same time cover all necessary features.
PDR continuously strives to improve. It is open to your requests and wishes.
I hope it will fulfill your expectations.

## Testing PDR
There is a public instance of PDR:
https://martin-mandelkow.de/apotheke/dienstplan-public/  
Username: Besucher  
Password: 1234  
The user Besucher has enough privileges to look around. If you want to test more features, just write a mail to public_pdr@martin-mandelkow.de


## Getting PDR
The latest release of PDR is available on GitHub:
```
git clone https://github.com/MaMaKow/dienstplan-apotheke.git
```

## Installing PDR
Make sure to unpack PDR to a directory, that your webserver has access to.
PHP and the webserver must have read access to all the files and folders.
It also needs write access to the subdirectories `upload`, `tmp` and `config`.
You might want to change the owner of the directory to the webservers user with e.g.:
```
chown -R www-data:www-data /var/www/html/pdr/`
```

## License
PDR is open source software under the AGPL license.
Please see the [license file](LICENSE.md) for details!
