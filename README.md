ebm
===

A lightweight, easy to use build monitor for jenkins


Requirements
============

To get it up and running, you need the following server components:

* Some lightweight webserver (Apache, nginx will do fine)
* PHP >=5.4 on the server side for proxy calls


How to set it up
================

- Clone it
- Before the build monitor will work, you need to install dependencies via:
  ```$ composer --install```
  If you don't have composer installed yet, [Go, get it](https://getcomposer.org/download/)!
- Configure your webserver: any basic variant of
    [Silex Webserver Configuration](http://silex.sensiolabs.org/doc/web_servers.html) should work,
    when pointed to `$EBM_ROOT/htdocs`.
- When done, go to `http://$SERVER/config.sample`, which should bring you to a build monitor
    showing some Jobs form https://builds.apache.org
- Create `$EBM_ROOT/config/{kewlbuildz}.json` to add your build monitor page with the name `kewlbuildz`.
    You may cheat at `$EMB_ROOT/config/config.sample.json`.
- DONE!
