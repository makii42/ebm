ebm
===

A lightweight, easy to use build monitor for [jenkins](http://jenkins-ci.org).


Requirements
============

To get it up and running, you need the following server components:

* Some lightweight webserver (Apache, nginx will do fine)
* PHP >=5.4 on the server side for proxy calls, including a decent version of curl.
* [composer](https://getcomposer.org/download/) installed.


How to set it up
================

- Clone it
- Before the build monitor will work, you need to install dependencies via:
  `/EBM_ROOT $ composer --install`
  If you haven't installed composer yet, [Go, get it](https://getcomposer.org/download/)!
- Configure your web server: any basic variant of
    [Silex Webserver Configuration](http://silex.sensiolabs.org/doc/web_servers.html) should work,
    when pointed to `$EBM_ROOT/htdocs`.
- Copy sample config via `$ cp config/config.sample.json config/config.json`
- When done, go to http://yourserver.com/samplejobs, which should bring you to a build monitor
    showing some Jobs form https://builds.apache.org/


How to configure your Jobs
==========================

- Create `$EBM_ROOT/config/kewlbuildz.json` to add your build monitor page with the name `kewlbuildz`.
- Put some job configuration in there:

```json
 {
     "monitor-name": "R34ll7 k3wl bu1ldZ",
     "jobs": {
         "my-3l337-jenkins": [
             "3l337 bu1ld j0b",
             "3l337 d3pl0y j0b",
             "3l337 m37r1cz 4n4lyziz"
         ]
     }
 }
```

- Obviously you have to tell EBM where `my-3l337-jenkins` resides, so you need to add a `"host"` section
   to either the `config.json` or the monitor configuration in `kewlbuildz.json`. I recommend putting it
   into `config.json` if you want to have multiple monitors pulling job data from the same jenkins.

```json
{
    ...
    "hosts": {
        "my-3l337-jenkins": {
            "label":    "OMG 1s th12 J3NK1NZ 3l337",
            "url":      "https://l337bu1ldz.com/",
        }
    }
    ...
}
```

- **NOTICE** By default EBM assumes jenkins to be deployed to path `/jenkins/`.
    - If you use another path, you can replace it by setting `basePath` to either empty string
      (if deployed to `/`), or just put the path you use, e.g. `/ci/`.
- _(You may cheat at `$EMB_ROOT/config/config.sample.json`)_.
- CONGRATZ!!! YOU'RE DONE! You just created the build monitor http://yourserver.com/kewlbuildz


Backlog
=======

Yes, this piece is pretty hacky. What needs to bedone:

* Write more unit tests
* Maybe Selenium Tests?
* provide admin UI to
    * Add connected Jenkins instances and configuration (auth, intervals, ...)
    * Allocate Jobs on "screens"
* Find better name for "screen"
* ... a lot I have not thought about enough.