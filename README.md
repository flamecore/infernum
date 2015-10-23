Infernum – Web Application Engine
=================================

[![Code Climate](http://img.shields.io/codeclimate/github/FlameCore/Infernum.svg)](https://codeclimate.com/github/FlameCore/Infernum)
[![License](https://img.shields.io/badge/license-ISC-blue.svg)](http://www.flamecore.org/projects/infernum)

Infernum is a lightweight Web Application Engine written in PHP 5.5.

**ATTENTION! Infernum is currently under heavy development and therefore not usable in a production environment. Use it at your own risk!**

Complete installation instructions and more information can be found [in our Wiki](https://github.com/FlameCore/Infernum/wiki).

Support: [Chat with us at Gitter](https://gitter.im/FlameCore/Infernum) or drop us a message at <info@flamecore.org>.


Installation
------------

1. Make sure that the directory `cache/` is writable.

     Example: `$ chmod 0777 cache/` or `$ chown www-data cache/`

2. Execute `install.sql` in your database.

3. Copy the file `config.yml.dist` and rename it to `config.yml` and adapt the new file according to your needs.

4. Copy the file `websites/default/settings.yml.dist` and rename it to `settings.yml` and adapt the new file according to your needs.

5. Create your first module. Let's call it `vendor/start` and use it as our frontpage. This can be done in `settings.yml`.

6. Copy the file `websites/default/site.yml.dist` and rename it to `site.yml`. To use more modules you can register them there.


Requirements
------------

* You must have at least PHP version 5.5 installed on your system.


Contributors
------------

If you want to contribute, please see the [CONTRIBUTING](CONTRIBUTING.md) file first.

Thanks to the contributors:

* Christian Neff (secondtruth)
* Martin Lantzsch (LinuxDoku)
* Sebastian Wagner (nobody, sebix)
