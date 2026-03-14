# Pharmacy Duty Roster

## Introduction

Pharmacy Duty Roster (PDR) is a web application that allows you to manage a duty roster for pharmacies.
PDR was launched in 2015 as an alternative to a very simple Excel sheet without formulas.
PDR aims to be user friendly, but at the same time cover all the necessary features.
PDR is constantly being improved. It is open to your requests and wishes.
I hope it meets your expectations.

## Testing PDR

There is a public instance of PDR:
https://martin-mandelkow.de/apotheke/dienstplan-public/
Username: "Besucher"
Passphrase: "1234"
The user "Besucher" has enough permissions to look around. If you want to test more features, just send a mail to public_pdr@martin-mandelkow.de

## Getting PDR

The latest version of PDR is available on GitHub:

```
git clone https://github.com/MaMaKow/dienstplan-apotheke.git
```

## Installing PDR

Make sure you unpack PDR into a directory that your web server has access to.
PHP and the web server must have read access to all files and folders.
It also needs write access to the `upload`, `tmp` and `config` subdirectories.
You may want to change the owner of the directory to the webservers user with e.g:

```
chown -R www-data:www-data /var/www/html/pdr/
```

### Installing Composer
This project uses [Composer](https://getcomposer.org/) for dependency management. If Composer is not already installed, follow the instructions on the official page:

- **Linux/macOS**:
  ```sh
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
- **Windows**:
Download the installer from getcomposer.org/download and follow the instructions.

### Installing the dependencies
Once Composer is installed, you can install all the required packages with the following command

```composer install```

### Launch the application
In the web browser, go to the path where you installed PDR.
Follow the installation wizard instructions.

## License

PDR is open source software under the AGPL license.
Please see the [license file](LICENSE.md) for details!