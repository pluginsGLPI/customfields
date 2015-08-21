# GLPI Custom fields plugin

### Latest news 

Further development will now be committed on the official repository of the plugin. 

To download the latest development version, report bug, ask for new feature, or contribute, please go to the Forge : https://forge.indepnet.net/projects/customfields

## Introduction

This plugin enables the management of custom fields for GLPI objects and is
based on the original code by Matt Hoover and Ryan Foster (originally
sponsored by Oregon Dept. of Administrative Services,
State Data Center) located in the [GLPI forge][].

This fork enables the use of this plugin in GLPI 0.84 onwards.

## Installation of the stable release

Download the current release and unpack the archive to a directory
"customfields" inside the GLPI plugin directory. Afterwards use the GLPI web
UI to install and activate the plugin.

Migrations of old version of the plugin are possible by just overwriting the
previous code with this version (better removing the files from the previous
version first) and using the update function in the web UI. (Please backup
your data prior to this!)

## Installation of the testing / development release

Do the same as the stable release, with the master branch.

[GLPI forge]: https://forge.indepnet.net/projects/customfields

