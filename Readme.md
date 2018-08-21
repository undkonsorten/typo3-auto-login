# TYPO3 Automatic BE Authentication

## What does it do?

This package automatically starts a backend user session for the open source CMS 
[TYPO3 CMS](https://typo3.org), configured by an environment variable.

It is based on the Daniel Siepmann‘s 
[great work](https://daniel-siepmann.de/Posts/2018/2018-07-25-auto-login-typo3-backend.html).
If you feel like saying "Thank you" or donating please consider him first! 

## Warning

**Be considerate when using this tool. Always have security in mind.**

**Any usage beyond development on a local machine is strongly dicouraged.**

Make sure to only require this package with `--dev` option and check your
deployment for measures to make sure the code is never deployed to a production
system.

By using this package you agree to be responsible for any damages arising
from its usage.

## Installation

```bash
composer require --dev undkonsorten/typo3-auto-login
```

Usage without composer has not been tested but might be perfectly possible
if you take care about class (auto) loading by yourself. 

## Usage

To configure username for automatic login set the environment variable 
`$TYPO3_AUTOLOGIN_USERNAME` somewhere in your environment.

Add an intialization call in your `AdditionalConfiguration.php` or in
a file required from there. Make sure to only initialize the service for
`Development` context by either wrapping it with a condition or putting
it to a file only loaded in `Development` context.

```php
if (\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isDevelopment()) {
    \Undkonsorten\TYPO3AutoLogin\Utility\RegisterServiceUtility::registerAutomaticAuthenticationService();   
}
```

Autologin will fail and throw an exception in `Production(/*)` contexts.

# Q&A

* **Q**: *My user is not authenticated, what‘s wrong?*

  **A**: Either you have a typo in the username or your environment variable hasn‘t taken
  effect yet. You might need to restart your web server, docker container or the like.
  Or you‘re running `Production` context…
