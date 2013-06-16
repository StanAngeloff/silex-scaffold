Silex Scaffold
==============

scripts/vagrant/
----------------

This directory contains the provisioning scripts which [Vagrant] uses to create a new VM from scratch.
The `provision` script uses [vagrant-shell-scripts] to create a VM instance with Nginx, [PHP-FPM] and installs the required dependencies.

When changes are made to that script, `provision-manaual.rb` can be run from within the VM to manually provision and apply the changes.

  [Vagrant]: http://www.vagrantup.com/
  [vagrant-shell-scripts]: https://github.com/StanAngeloff/vagrant-shell-scripts
  [PHP-FPM]: http://php-fpm.org/
