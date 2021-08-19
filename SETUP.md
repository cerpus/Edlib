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
All commands bellow must be run from the base directory of edlib.

1. Run (VM)`ssh-keygen` and add the public keys to your bitbucket(only Cerpus employees) and github account 
2. run (VM)`./scripts/first-time-setup.sh`. You can proceed to the next step while this runs
3. run (HOST)`multipass exec edlib -- ip -br address show scope global` if you are using multipass to get the IP of the VM
4. run (HOST)`./update-host-file.sh <VM-IP>` to create host file entries for edlib on your host
5. After the script on step 1 is done, log out and in so that all changes take effect
6. Run (VM)`dcu` to start docker-compose. `dcu` is an alias for `docker-compose up -d`
7. Run (VM)`./update-certs.sh` to install the newly generated certificates inside the VM.
