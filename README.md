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
- Create ```config/prod.json``` to add your jenkins configuration
- Set up apache with PHP to point to ```htdocs```
- DONE!