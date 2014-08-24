# Shibalike

Shibalike is a PHP library for emulating a limited [Shibboleth](http://en.wikipedia.org/wiki/Shibboleth_%28Internet2%29)
environment, and has components to query attributes for a user, persist them in a session (without interfering with native PHP sessions), and inject them into the `$_SERVER` array as would a web server module.

This allows you to use your own authentication system (based on any mechanism) and
attribute provider to sign users into any app with an existing Shibboleth auth module.

### Design

The design was based on [this blog post](http://www.mrclay.org/2011/04/16/shibalike-a-php-emulation-of-a-shibboleth-environment/), which laid out the initial concept. Unlike Shibboleth, Shibalike's SP ("Service Provider") and IdP ("Identity Provider") junction points must share a common backend storage (filesystem, DB, etc), and have access to each others' browser cookies. This limits the system's abilities to cross app boundaries, but also limits its complexity of implementation and setup.

## Why Shibalike?

If you maintain a PHP app that relies on an institutional Shibboleth IdP:

* To test the app, you need a host with an SP and a blessing from the IdP:
  * Getting SPs set up on developer machines (e.g. laptops) would be a hassle.
  * Getting those laptops blessed by the IdP may be an ordeal, if possible.
* You don't control the IdP's behavior:
  * You can't easily emulate downtime or flaky behavior for testing purposes.
  * You can't easily see how your app handles a switch between shibboleth users.
  * You can't quickly change arbitrary attributes for testing purposes.
  * In a testing environment you can't simply sign in as a different Shibboleth user.
  * You can't hardcode a user for use in unit/integration tests.
  * The IdP can go down!

You could use Shibalike to setup a local "IdP" to be used during testing (e.g. the "login"
might just be a dropdown of test users) or as a backup if the real IdP is down (e.g. the 
login might use LDAP or some other method to authenticate users).

You could even use Shibalike as an adapter between an app and a real Shibboleth system:
Your local "IdP" would use Shibboleth to authenticate users, but you would specify how 
Shibalike presents those attributes to the application. E.g. once
an "admin" user authenticates via Shibboleth, Shibalike could give him/her the option
of signing into the app under a different user, or with altered attributes.

### Why not SimpleSAMLphp?

You might want to checkout [SimpleSAMLphp](http://simplesamlphp.org/), an impressive native PHP implementation of [SAML](http://en.wikipedia.org/wiki/Security_Assertion_Markup_Language) with some direct Shibboleth compatibility. It probably does 100 times as much as Shibalike, but also is heavier and significantly more complex. Shibalike's message passing is via shared backend storage; no XML/encryption/certs required.

## Core Components

* [IdP](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/IdP.php): allows marking a user as authenticated, or logging out the current user
* [SP](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/SP.php): allows injection of User attributes into `$_SERVER`, and optional redirecting of user to your "login" app (using an IdP)
* [AuthRequest](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/AuthRequest.php): value object set by the SP expressing desire to authenticate
* [AuthResult](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/AuthResult.php): value object set by IdP with a valid user's username & attributes
* [IStateManager](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/IStateManager.php) (interface): stores Auth* objects associated with the current browser
* [Attr\IStore](https://github.com/mrclay/shibalike/blob/master/src/Shibalike/Attr/IStore.php) (interface): provides attributes by username (e.g. from a DB)

### UserlandSession

By default, Shibalike persists auth/user data in a 
[non-native session component](https://github.com/mrclay/UserlandSession),
which operates very similarly to native sessions, but which can be used independently of 
them. This allows Shibalike to maintain its state across applications without any 
interference with the native sessions used in those apps.

## Completed Pieces

* Core behavioral pieces (SP, IdP).
* Interfaces for the state manager and attribute store.
* A default state manager, based on `UserLandSession`.
* A simple attribute provider based on Zend Db and a single table.
* A simple attribute provider based on a static array.
* A [basic demo](https://github.com/mrclay/shibalike/tree/master/examples/basic) of a crude but operational usage.

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
DISCLAIMED. IN NO EVENT SHALL THE AFOREMENTIONED PARTIES BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.