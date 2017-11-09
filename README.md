#Pharmacy Duty Roster
## Introduction
Pharmacy Duty Roster (PDR) is a web application that allows to operate a duty roster for pharmacies.
PDR started in 2015 as an alternative to a really simple excel sheet without formulas.
PDR aims to be user-friendly but at the same time cover all necessary features.
PDR continuously strives to improve. It is open to your requests and wishes.
I hope it will fulfill your expectations.

## Getting PDR
The latest release of PDR is available on GitHub:
[PDR](https://github.com/MaMaKow/dienstplan-apotheke/releases/latest)

## Installing PDR
Make sure to unpack PDR to a directory, that your webserver has access to.
PHP and the webserver must have read access to all the files and folders.
It also needs write access to the subdirectories upload, tmp and config.
You might want to change the owner of the directory to the webservers user with e.g.:
```
chown -R www-data:www-data /var/www/html/pdr/`
```

#License
PDR is open source software under the GPL license.
Please see the [license file](LICENSE) for details!
