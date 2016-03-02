![Spaark](./default/images/logo.png)

Spaark Framework
================

Spaark is a web application framework - it has code that runs on both the server and client sides of a web site.

File Structure
--------------

* [_/](./_) - Files that aren't for the main distro
  * [index.php](./_/index.php) - This file should be placed in a web server's web root,
    with all URLs mapping to it. It loads and starts Spaark
  * [test.php](./_/test.php) - A test script to check if:
    1. PHP is installed
    2. The correct version of PHP is installed
* [core/](./core) - The main class files
* [default/](./default) - Contains defaults for the framework:
  * Default config
  * Default pages, css
  * The Spaark icon and logo
  * JavaScript for the client-side of the framework
* [Spaark.php](./Spaark.php) - Loads the core classes used by the framework, which are required before the autoloader can function
* [consts.php](./consts.php) - Constants regarding framework version and paths
* [README.md](./README.md) - This file!

Licence (CC BY-NC-ND)
---------------------

This work is licenced under the [Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License](http://creativecommons.org/licenses/by-nc-nd/4.0/). This allows you to share the work.

<a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" /></a>
