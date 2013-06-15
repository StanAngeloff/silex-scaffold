# -*- mode: ruby -*-
# vi: set ft=ruby :

require 'erb'

Vagrant.configure('2') do |config|
  # Every Vagrant virtual environment requires a box to build off of.
  config.vm.box = 'ubuntu-server-cloud12.04-x86'

  # The URL from where the 'config.vm.box' box will be fetched if it
  # doesn't already exist on the user's system.
  config.vm.box_url = 'http://cloud-images.ubuntu.com/vagrant/precise/current/precise-server-cloudimg-i386-vagrant-disk1.box'

  config.vm.define :master do |master|
    # Create a private network, which allows host-only access to the machine
    # using a specific IP.
    master.vm.network :private_network, ip: '192.168.3.101'
  end

  # Enable provisioning through a shell script.
  config.vm.provision :shell do |shell|
    directory    = File.dirname(__FILE__) + '/scripts/vagrant/'
    shell.inline = ERB.new(
      "<% def import(file); File.read('#{directory}' + file); end %>" +
      File.read("#{directory}provision")
    ).result
  end
end
