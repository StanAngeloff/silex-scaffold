BIN_PATH    := bin
VENDOR_PATH := vendor

VAGRANT_MACHINE_NAME := master
VAGRANT_MACHINE_FILE := .vagrant/machines/$(VAGRANT_MACHINE_NAME)/virtualbox/id

.PHONY: default install-ruby-dependencies vagrant-provision install clean

# Do nothing if `make` invoked with no arguments.
default:
	@/bin/echo "No default '$(MAKE)' target configured. Did you mean any of the following:"
	@/bin/echo
	@cat '$(firstword $(MAKEFILE_LIST))' | grep '^[[:alnum:] \-]\+:' | sed -e 's/:.*//g' | sort -u | tr "\\n" ' ' | fold -sw 76 | sed -e 's#^#    #g'
	@/bin/echo
	@exit 1

# Check if a given command is available and exit if it's missing.
required-dependency =                                              \
	/bin/echo -n "Checking if '$(1)' is available... " ;             \
	$(eval COMMAND := which '$(1)')                                  \
	if $(COMMAND) >/dev/null; then                                   \
		$(COMMAND) ;                                                   \
	else                                                             \
		/bin/echo "command failed:" ;                                  \
		/bin/echo ;                                                    \
		/bin/echo "    $$ $(COMMAND)" ;                                \
		/bin/echo ;                                                    \
		/bin/echo "You must install $(2) before you could continue." ; \
		/bin/echo "On Debian-based systems, you may want to try:" ;    \
		/bin/echo ;                                                    \
		/bin/echo "    $$ [sudo] apt-get install $(3)" ;               \
		/bin/echo ;                                                    \
		exit 1;                                                        \
	fi

# Install a RubyGem if it's not present on the system.
install-ruby-gem-if-missing =                                                                      \
	/bin/echo -n "Checking if '$(1)' RubyGem is available... " ;                                     \
	$(eval GEM_VERSION := ruby -rubygems -e "puts Gem::Specification::find_by_name('$(1)').version") \
	if $(GEM_VERSION) 2>&1 1>/dev/null; then                                                         \
		$(GEM_VERSION) ;                                                                               \
	else                                                                                             \
		/bin/echo "nope." ;                                                                            \
		/bin/echo -n "Installing '$(1)' RubyGem... " ;                                                 \
		gem install --remote --no-ri --no-rdoc '$(1)' 1>/dev/null || (                                 \
			/bin/echo                                                                                                               ; \
			/bin/echo "Failed to install '$(1)' RubyGem."                                                                           ; \
			/bin/echo                                                                                                               ; \
			/bin/echo "On Debian-based systems, if you receive permission issues, please run the following commands and try again:" ; \
			/bin/echo "    $$ [sudo] mkdir -p /var/lib/gems"                                                                        ; \
			/bin/echo "    $$ [sudo] chown -R `whoami`:users /var/lib/gems /usr/local"                                              ; \
			/bin/echo                                                                                                               ; \
		) ;              \
		$(GEM_VERSION) ; \
	fi

# Install all RubyGems from Gemfile using Bundler.
ifeq ($(shell bundle check 1>/dev/null 2>&1 && echo $$? || echo 127),0)
RUBY_GEMS = $(VENDOR_PATH)
else
RUBY_GEMS = install-ruby-gems
endif

install-ruby-dependencies: $(RUBY_GEMS)
$(RUBY_GEMS):
	@$(call required-dependency,ruby,Ruby,ruby1.9.3)
	@$(call required-dependency,gem,RubyGems,rubygems1.8)
	@$(call install-ruby-gem-if-missing,bundler)
	@$(call required-dependency,bundle,Bundler,ruby-bundler)
	@/bin/echo -n 'Installing RubyGem project-specific dependencies... '
	@bundle install --path '$(VENDOR_PATH)' --binstubs '$(BIN_PATH)' 1>/dev/null
	@/bin/echo 'OK'

# Set the expected NFS server binary name based on the operating system.
ifeq ($(shell uname -s),Darwin)
NFSD_SERVER := nfsd
else
NFSD_SERVER := rpc.nfsd
endif

# Bring up the virtual machine if it doesn't already exist.
vagrant-provision: install-ruby-dependencies $(VAGRANT_MACHINE_FILE)
$(VAGRANT_MACHINE_FILE):
	@$(call required-dependency,bsdtar,BsdTar,bsdtar)
	@/bin/echo 'Creating default VM set up... please be patient.'
	@/bin/echo 'On Debian-based systems, you may be asked for your root password in order to configure the NFS daemon.'
	@'$(BIN_PATH)/vagrant' up || ( \
		/bin/echo                                                                                                                                                             ; \
		/bin/echo 'Failed to provision your virutal machine.'                                                                                                                 ; \
		/bin/echo                                                                                                                                                             ; \
		/bin/echo 'On Debian-based systems, if you have not installed VirtualBox, please run the following command and try again:'                                            ; \
		/bin/echo                                                                                                                                                             ; \
		/bin/echo "    $$ [sudo] apt-get install build-essential 'linux-headers-*-generic' virtualbox"                                                                        ; \
		/bin/echo                                                                                                                                                             ; \
		/bin/echo "In case the virtual machine booted, but the provisioning failed with 'VM must be created before running this command.', please run the following command:" ; \
		/bin/echo                                                                                                                                                             ; \
		/bin/echo "    $$ $(BIN_PATH)/vagrant reload"                                                                                                                         ; \
		/bin/echo                                                                                                                                                             ; \
	)

# Bootstrap a development environment.
install-development: vagrant-provision

install: install-development

clean:
	@$(call required-dependency,git,Git,git-core)
	@if [ -f '$(VAGRANT_MACHINE_FILE)' ]; then                   \
		'$(MAKE)' --no-print-directory install-ruby-dependencies ; \
		'$(BIN_PATH)/vagrant' destroy --force                    ; \
	fi
	@git clean -dfx


# Update a local repository path from a remote repository.
#
# Parameters:
#   - The subtree name, used to identify branches during the operation.
#   - The remote repository URI, e.g., 'git://github.com/user/repository.git'.
#   - The remote repository branch, e.g., 'master'.
#   - The local repository path.
#   - Optional remote repository path (default: '/').
define update-git-subtree

update-$(1):
	@$$(call required-dependency,git,Git,git-core)
	@$$(eval remote_name := 'update/$(1)')
ifeq ($(5),)
	@$$(eval merge_branch := '$$(remote_name)/$(3)')
else
	@$$(eval merge_branch := 'update/split/$(1)/$(5)')
endif
	@$$(eval origin_branch := $$(shell git rev-parse --abbrev-ref HEAD))
	@/bin/echo -n 'Checking if the Git remote "$$(remote_name)" exists... '
	@if git remote -v | grep '$$(remote_name)' 1>/dev/null 2>&1; then \
		echo 'fetching changes...' ; \
		git fetch '$$(remote_name)' ; \
	else \
		echo 'adding with changes...' ; \
		git remote add -f -t '$(3)' '$$(remote_name)' '$(2)' ; \
	fi
	@if ! git diff-files --quiet; then \
		echo 'Stashing working copy changes before proceeding...' ; \
		git stash save -q 'before: update-$(1)' ; \
	fi
	@if [ -n '$(5)' ]; then \
		echo 'Checking out remote branch "$(3)"...' ; \
		git checkout -q '$$(remote_name)/$(3)' ; \
		echo 'Splitting "$(5)"...' ; \
		git subtree split -P '$(5)' --annotate='(split: $(1)) ' -b '$$(merge_branch)' ; \
		echo 'Checking out previous branch "$$(origin_branch)"...' ; \
		git checkout -q '$$(origin_branch)' ; \
	fi
	@if [ -d '$(4)' ]; then \
		echo 'Updating subtree in "$(4)"...' ; \
		git subtree merge --prefix='$(4)' --squash '$$(merge_branch)' ; \
	else \
		echo 'Adding subtree in "$(4)"...' ; \
		git subtree add --prefix='$(4)' --squash '$$(merge_branch)' ; \
	fi
	@echo 'Cleaning up...'
	@if [ -n '$(5)' ]; then \
		git branch -D '$$(merge_branch)' ; \
	fi
	@git remote rm '$$(remote_name)'
	@if git stash list | grep 'before: update-$(1)' 1>/dev/null 2>&1; then \
		echo 'Applying stashed changes...' ; \
		git stash pop -q "$$$$( git stash list | grep 'before: update-$(1)' | cut -d':' -f1 )" ; \
	fi
endef

$(eval $(call update-git-subtree,vagrant-shell-scripts,git://github.com/StanAngeloff/vagrant-shell-scripts.git,master,scripts/vagrant/shell-scripts))


# vim: ts=2 sw=2 noet
