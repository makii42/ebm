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
- To install all dependencies, run this:```
$ composer --install
``` If you don't have composer installed, [Go, get it](https://getcomposer.org/download/)!
- Create `config/prod.json` to add your jenkins configuration
    - if you want to use multiple environments, just pass `APP_ENV` in into PHP environment
- Set up apache with PHP to point to ```htdocs```
- DONE!
