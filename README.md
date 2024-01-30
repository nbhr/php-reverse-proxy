# php-reverse-proxy

This is a tiny 'reverse proxy' PHP script with a file-based cache.


## Motivation

While reverse proxy can be implemented as a server (nginx, squid, etc) or a server module (apache mod_proxy), installing or enabling them requires having root access to the host that is not available for end users of web-hosting services in general.

For such end users, this implements a reverse proxy as a PHP script.


## License

This script is licensed under the Apache License 2.0. Please note that `Cache_Lite` in `lib/` is not by the author. It is a part of PEAR.


## Installation

Modify the line of `$url = "http://www.example.com/";` to your target host, and then upload `proxy.php` and `lib/` directory to your web site. If you do not need caching mechanism, use `proxy-simple.php` instead. `proxy-simple.php` does not require files in `lib/`.


## Usage

Suppose your `proxy.php` is accessible as `http://www.aaa.com/bbb/proxy.php`, and you specified `http://www.ccc.com/` as `$url` in the script. To retrive `http://www.ccc.com/ddd.html` by the script, visit `http://www.aaa.com/bbb/proxy.php?ddd.html`. Here `www.aaa.com` and `www.ccc.com` serve as a frontend and backend servers respectively; meaning that `www.ccc.com` is not necessarily reachable from you.

By using Apache `mod_rewrite`, you can map a directory of the backend server to the frontend server as follows.
```/.htaccess
RewriteRule ^eee/?(.*)$ /bbb/proxy.php?/eee/$1 [L]
```

Here all accesses to `http://www.aaa.com/eee/*` are redirected to `http://www.ccc.com/eee/*`. Besides, the flag `[QSA]` will not work as expected. See the limitation below.


## Customization

### Proxy

If your frontend server requires an HTTP proxy to access the backend server, you can uncomment the following part in `proxy.php` to modify the default parameter for `stream_get_contents()`.

```php:proxy.php
/* # 0. Configure the proxy */
/*
stream_context_set_default(
        array("http" => array(
                        "proxy" => "tcp://your.proxy.com:8080",
                        "request_fulluri" => TRUE,
                        ),
        )
);
*/
```


### `dst` parameter

This reverse proxy is designed to allow users to access local servers by definition, and the script is implemented to use the hard-coded target host (i.e., `$url` above) for security; meaning that the script accesses the backend server which is specified explictly by yourself.

This behavior can be changed by commenting out the line of `$url = ...`.  The script parses `dst=` in the query string to know the backend server.
```/.htaccess
RewriteRule ^eee/?(.*)$ /bbb/proxy.php?dst=http://www.ccc.com/eee/$1 [L]
```

This can be useful if you want to change the target host dynamically (by `mod_rewrite` for example), but can be a point of Server-Side Request Forgery (SSRF) flaws if the script is called directly with user-specified `dst` parameter.  That is, if an attacker knows where your `proxy.php` is installed, they can directly access `proxy.php?dst=...` with specifying any target accessible from your server.  Use this feature if and only if you know what you are doing.


## Limitations

To keep the script as simple as possible, all the query strings are all proxied, as they are to the destination.

So the call `http://www.aaa.com/bbb/proxy.php?bbb=444&dst=http://www.ccc.com/ddd.html&aaa=333` becames `http://www.ccc.com/ddd.html?bbb=444&aaa=333`
