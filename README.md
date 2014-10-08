php-reverse-proxy
=================

This is a tiny 'reverse proxy' PHP script with a file-based cache.


Motivation
----------

While reverse proxy can be implemented as a server (nginx, squid, etc) or a server module (apache mod_proxy), installing or enabling them requires having root access to the host that is not available for end users of web-hosting services in general.

For such end users, this implements a reverse proxy as a PHP script.


Installation
------------

Simply upload `proxy.php` and `lib/` directory to your web site.


Usage
-----

Suppose your `proxy.php` is accessible as `http://www.aaa.com/bbb/proxy.php`. To retrive the content of `http://www.ccc.com/ddd.html` by the script, visit `http://www.aaa.com/bbb/proxy.php?dst=http://www.ccc.com/ddd.html`. Here `www.aaa.com` and `www.ccc.com` serve as a frontend and backend servers respectively.

By using Apache `mod_rewrite`, you can map a directory of the backend server to the frontend server as follows.
```/.htaccess
RewriteRule ^eee/?(.*)$ /bbb/proxy.php?dst=http://www.ccc.com/eee/$1 [L]
```
Here all accesses to `http://www.aaa.com/eee/*` are redirected to `http://www.ccc.com/eee/*`.


Limitations
-----------

To keep the script as simple as possible, this code supports GET access without query strings only. That is, this DOES NOT support GET parameters such as `&foo=bar` and POST method.

