## Prerequisites

- git
- [nvm](https://github.com/nvm-sh/nvm)
- [maven](https://maven.apache.org/)
- php7.4

## Install

We use multipass to setup the dev environment. Bellow are the optional steps if you are not using multipass
1. run `multipass launch --name edlib -c 4 -m 16G -d 40G`. If you forget to set disk size you can update it using the following command `sudo qemu-img resize /var/snap/multipass/common/data/multipassd/vault/instances/edlib/ubuntu-20.04-server-cloudimg-amd64.img +20G`
2. run `multipass mount <path of edlib folder> edlib:/home/ubuntu/Edlib`. Remember to change "path of edlib folder" with the actuall folder where edlib is located
3. run `multipass shell edlib`

You now have a running shell ready for the setup. You can proceed to the next step.

### Actual install

1. run `./scripts/first-time-setup.sh`
2. While you wait for the script to finish, add the content of `host.example` to /etc/hosts.
3. After the script is done, log out and in so that all changes take effect
