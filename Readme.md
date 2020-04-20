# TYPO3 Automatic BE Authentication

## What does it do?

This package automatically starts a backend user session for the open source CMS 
[TYPO3 CMS](https://typo3.org), configured by an environment variable. You can set
a cookie to temporarily disable the automatic login (e.g. for _switch user_). There‘s
a [bookmarklet](#bookmarklet) that does the job for you.

It is based on the Daniel Siepmann‘s 
[great work](https://daniel-siepmann.de/Posts/2018/2018-07-25-auto-login-typo3-backend.html).
If you feel like saying "Thank you" or donating please consider him first! 

## Warning

**Be considerate when using this tool. Always have security in mind.**

**Any usage beyond development on a local machine is strongly discouraged.**

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

Add an initialization call in your `AdditionalConfiguration.php` or in
a file required from there. Make sure to only initialize the service for
`Development` context by either wrapping it with a condition or putting
it to a file only loaded in `Development` context.

```php
if (\TYPO3\CMS\Core\Core\Environment::getContext()->isDevelopment()) {
    \Undkonsorten\TYPO3AutoLogin\Utility\RegisterServiceUtility::registerAutomaticAuthenticationService();   
}
```

Autologin will fail and throw an exception in `Production(/*)` contexts.

### Bookmarklet

There are reasons to temporarily disable the automatic login, e.g. to check user rights
on a local machine. Autologin will prevent the TYPO3 switch user functionality.
To circumvent this there‘s a bookmarklet the (un)sets the cookie `_typo3-auto-login`
for you to prevent autologin.
Just add a new bookmark with the following "URL"
```
javascript:(q=>{let n=window.Notification,s='_typo3-auto-login',d='disable',p='TYPO3 auto login',w=document,a=w.cookie.split(';').some(x=>x.trim()===`${s}=${d}`);w.cookie=`${s}=${a?';expires='+new Date(0).toUTCString():d};path=/;`;n&&n.permission!=='denied'&&n.requestPermission().then(q=>new n(p,{body:`(${a?'✓':'✗'}) ${p} is now ${a?'enabled':'disabled'}. Cookie »${s}« has been ${a?'removed':'set'}.`,icon:'https://extensions.typo3.org/fileadmin/user_upload/ext_icon.png'}));})();
```
and name the files according to your likings.

# Q&A

* **Q**: *My user is not authenticated, what‘s wrong?*

  **A**: Either you have a typo in the username or your environment variable hasn‘t taken
  effect yet. You might need to restart your web server, docker container or the like.
  Or you‘re running `Production` context…
