# Shibalike

Shibalike is a PHP library for emulating a [Shibboleth](http://en.wikipedia.org/wiki/Shibboleth_%28Internet2%29)
environment, and has components to query attributes for a user, persist them tied to a
browser cookie, and inject them into the `$_SERVER` array as would a web server module.

This allows you to use your own authentication system (based on any mechanism) and
attribute provider to sign users into any app with an existing Shibboleth auth module.

The design was based on [this blog post](http://www.mrclay.org/2011/04/16/shibalike-a-php-emulation-of-a-shibboleth-environment/), 
which laid out the initial concept.

## Why?

If you maintain a PHP app that relies on an institutional Shibboleth IdP:

* To test the app, you need a host with an SP and a blessing from the IdP:
  * Getting SPs set up on developer machines (e.g. laptops) would be a hassle.
  * Getting those laptops blessed by the IdP may be an ordeal, if possible.
* You don't control the IdP's behavior:
  * You can't easily emulate downtime or flaky behavior for testing purposes.
  * You can't easily see how your app handles a switch between shibboleth users.
  * You can't simply/quickly change arbitrary attributes for testing purposes.
  * In a testing environment you can't simply sign in as a different Shibboleth user.
  * You can't hardcode a user for use in a unit/integration tests.
  * The IdP can go down!

You could use Shibalike to setup a local Shibalike "IdP" to be used during testing (e.g. the "login"
might just be a dropdown of test users) or as a backup if the real IdP is down (e.g. the 
login might use LDAP or some other method to authenticate users).

You could even use Shibalike as an adapter between an app and a real Shibboleth system:
Your local "IdP" would use Shibboleth to authenticate users, but you would specify how 
Shibalike presents those attributes to the application. E.g. once
an "admin" user authenticates via Shibboleth, Shibalike could give him/her the option
of signing into the app under a different user, or with altered attributes.

## Core Components

* [User](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/User.php): a simple value object with a username and array of attributes
* [IStateManager](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/IStateManager.php) (interface): stores a User associated with the current browser
* [Attr\IStore](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/Attr/IStore.php) (interface): provides attributes by username (e.g. from a DB)
* [IdP](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/IdP.php): allows marking a user as authenticated, or logging out the current user
* [SP](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/SP.php): allows injection of User attributes into `$_SERVER`, and optional redirecting of user to your "login" app (using an IdP)

### UserlandSession

By default, Shibalike persists attributes in a 
[non-native session component](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/Util/UserlandSession.php),
which operates very similarly to native sessions, but which can be used independently of 
them. This allows Shibalike to maintain its state across applications without any 
interference with the native sessions used in those apps.

`UserlandSession` is loosely-coupled and introduces no global state, so you can also use it
to bridge state across applications for other purposes, as a non-global replacement for 
native sessions, or just to better understand how native sessions work (more or less).

The [storage interface](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/Util/UserlandSession/IStorage.php)
is similar to the native session's callback interface so that one can easily create a storage 
class based on an existing set of callbacks used in session_set_save_handler().

## Completed Pieces

* Core behavioral pieces (SP, IdP).
* Interfaces for the state manager and attribute store.
* A default state manager, based on `UserLandSession`.
* A simple attribute provider based on Zend Db and a single table.
* A simple attribute provider based on a static array.

* There's a [basic example](https://github.com/mrclay/shibalike/tree/master/examples/basic) demonstrating a crude but operational usage of the system.
* There's a [demonstration of UserlandSession](https://github.com/mrclay/shibalike/blob/master/examples/UserlandSession/simultaneous.php) showing 3 simultaneous sessions, including a native one.

## License

Shibalike has a permissive, "modified BSD" license:

Copyright (c) 2011, Stephen Clay and other collaborators
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
* Neither the name Stephen Clay nor the names of his contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL STEPHEN CLAY OR OTHER COLLABORATORS BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.