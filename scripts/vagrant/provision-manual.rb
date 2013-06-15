#!/usr/bin/env ruby
# This file mimics Vagrant's behaviour and allows a provisioning script to be
# re-run from within the VM itself.
#
# $ cd /vagrant
# $ script/vagrant/provision-manual.rb
#
# You must have Ruby installed on $PATH.

require 'erb'
require 'tempfile'

directory = File.dirname(__FILE__) + '/'

arguments = File.read("#{directory}../../Vagrantfile").
  lines.
  find_all{ |line| line.include?('shell.args') }.
  map{ |line| line.split('=', 2).last.strip.chomp('"').reverse.chomp('"').reverse }.
  first

begin
  script = Tempfile.new 'provision'
  script << ERB.new(
    "<% def import(file); File.read('#{directory}' + file); end %>" +
    File.read("#{directory}provision")
  ).result

  script.chmod 0777
  script.flush.close

  exec "#{script.path} #{arguments}"
ensure
  script.unlink
end
