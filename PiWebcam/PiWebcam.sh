#!/bin/bash
# ______ _ _    _      _                         
# | ___ (_) |  | |    | |                        
# | |_/ /_| |  | | ___| |__   ___ __ _ _ __ ___  
# |  __/| | |/\| |/ _ \ '_ \ / __/ _` | '_ ` _ \ 
# | |   | \  /\  /  __/ |_) | (_| (_| | | | | | |
# \_|   |_|\/  \/ \___|_.__/ \___\__,_|_| |_| |_|
# 
# http://piwebcam.sourceforge.io

# ensure we are running on a supported Raspbian release
if ! grep -q '9 (stretch)' /etc/os-release; then
	source /etc/os-release
	echo
	echo "ERROR: operating system $PRETTY_NAME not supported" 
	echo
	exit 0
fi

#################
# Variables
#################

# exported variables will be made available in the web interface inside $env
export MY_NAME="PiWebcam"
export MY_VERSION="1.1-dev"
# location of this script
export MY_DIR="/boot/$MY_NAME"
export MY_FILE="$MY_DIR/$MY_NAME.sh"
# location of the configuration file
MY_CONFIG="$MY_DIR/$MY_NAME.conf"
# boostrap style of the main panel of the web interface
export MY_WEB_PANEL_STYLE="primary"
# the wlan interface
IFACE="wlan0"
# the default hostname and password
export DEFAULT_NAME=${MY_NAME}-`cat /sys/class/net/$IFACE/address| sed 's/://g'|sed 's/^\w\w\w\w\w\w//'`
export DEFAULT_PASSWORD="$DEFAULT_NAME"
# the network which is created if WiFi AP mode is selected
AP_NETWORK="192.168.4"
# where to mount the data filesystem
DATA_MOUNT_POINT="/mnt"
# where to store pictures and movies
export DATA_DIR="$DATA_MOUNT_POINT/data"
# where to persist our files (logs, notification queue, etc)
PERSIST_DIR="$DATA_MOUNT_POINT/$MY_NAME"
# the directory through which pictures and movies are exposed inside the web root
export PLAYBACK_DIR="playback"
# the size of the root filesystem
ROOT_PARTITION_SIZE="4G"
# if enabled, the installer will not ask user confirmation
SILENT_INSTALL=0
# the partition of the boot/root/data filesystem
BOOT_DEVICE="/dev/mmcblk0p1"
ROOT_DEVICE="/dev/mmcblk0p2"
DATA_DEVICE="/dev/mmcblk0p3"
# title of the notification (prefixed by the hostname)
NOTIFICATION_SUBJECT="Motion Detected"
# where to store the notification queue if internet is not available
NOTIFICATION_QUEUE="$PERSIST_DIR/notification_queue"
# how many notifications to keep in the queue
NOTIFICATION_QUEUE_SIZE=10
# the command to execute to check if we have an internet connection
INTERNET="ping -W 2 -c 1 8.8.8.8|grep time=|wc -l"
# cleanup the filesystem if above the threshold in percentage
ROOT_CLEANUP_THRESHOLD=90
DATA_CLEANUP_THRESHOLD=80
# location of the auth file for web access
WEB_PASSWD_FILE="/etc/lighttpd/$MY_NAME.users"

### location of system configuration files and directories
NETWORK_CONFIG="/etc/dhcpcd.conf"
AP_CONFIG="/etc/hostapd/hostapd.conf"
AP_CONFIG_DEFAULT="/etc/default/hostapd"
WIFI_CONFIG="/etc/wpa_supplicant/wpa_supplicant.conf"
DHCP_CONFIG="/etc/dnsmasq.conf"
CAMERA_CONFIG="/etc/motion/motion.conf"
CAMERA_CONFIG_DEFAULT="/etc/default/motion"
WEB_CONFIG="/etc/lighttpd/lighttpd.conf"
EMAIL_CONFIG="/etc/ssmtp/ssmtp.conf"
PI_CMDLINE="/boot/cmdline.txt"
PI_CONFIG="/boot/config.txt"
STARTUP_FILE="/etc/rc.local"
WEB_CONF_AVAILABLE_DIR="/etc/lighttpd/conf-available"
WEB_CONF_ENABLED_DIR="/etc/lighttpd/conf-enabled"
WEB_ROOT_DIR="/var/www/html"
INITRAMFS_MODULES="/etc/initramfs-tools/modules"
INITRAMFS_HOOKS="/etc/initramfs-tools/hooks/$MY_NAME"
INITRAMFS_PREMOUNT_SCRIPT="/etc/initramfs-tools/scripts/local-premount/${MY_NAME}"
INITRAMFS_BOTTOM_SCRIPT="/etc/initramfs-tools/scripts/init-bottom/${MY_NAME}"
INITRAMFS_IMAGE="/boot/init.gz"

#################
# init
#################

# exit if not run as root
if [[ $EUID -ne 0 && -n $1 ]]; then
	echo 
	echo "ERROR: $MY_NAME must be run as root"
	echo
	exit 0
fi

# load the configuration file
source $MY_CONFIG 2> /dev/null

#################
# Common functions
#################

# print out a banner
function print_banner {
base64 -d <<<"X19fX19fIF8gXyAgICBfICAgICAgXwp8IF9fXyAoXykgfCAgfCB8ICAgIHwgfAp8IHxfLyAvX3wg
fCAgfCB8IF9fX3wgfF9fICAgX19fIF9fIF8gXyBfXyBfX18KfCAgX18vfCB8IHwvXHwgfC8gXyBc
ICdfIFwgLyBfXy8gXyBfIFwKfCB8ICAgfCBcICAvXCAgLyAgX18vIHxfKSB8IChffCAoX3wgfCB8
IHwgfCB8IHwKXF98ICAgfF98XC8gIFwvIFxfX198Xy5fXy8gXF9fX1xfXyxffF98IHxffCB8X3wK
Cgo="
}

# log/echo a message ($1: message)
function log {
	local NOW=`date '+%Y-%m-%d %H:%M:%S'`
	# print out the message
	echo -e "[\e[33m$MY_NAME\e[0m][$NOW] $1"
	# save it in the log file (if the persist directory is available there, otherwise in /var/log)
	local LOG_DIR="/var/log"
	if [ -d $PERSIST_DIR ]; then
		LOG_DIR=$PERSIST_DIR
	fi
	echo "[$MY_NAME][$NOW] $1" >> $LOG_DIR/$MY_NAME.log
}

# append the given string to a file if not already there ($1: string, $2: file)
function append {
	if [[ -n "$1" && -n "$2" ]]; then
		if ! grep -q "^$1" $2; then
			echo "$1" >> $2
		fi
	fi
}

# return true if the filesystem utilization is above a given threshold ($1: mount point, $2: threshold)
function filesystem_full {
	if [[ -n "$1" && -n "$2" ]]; then
		local PERCENTAGE_USED=`df |grep "${1}$"|awk '{print $5}'|sed 's/%//'`
		if [[ -n $PERCENTAGE_USED && $PERCENTAGE_USED -ge $2 ]]; then 
			true
		else
			false
		fi
	fi
	false
}

# enable writing in the boot filesystem
function enable_write_boot {
	# remount boot in read/write
	mount -o remount,rw /boot
}

# disable writing the boot filesystem
function disable_write_boot {
	# sync the filesystem
	sync -f /boot
	# remount boot in read/only
	mount -o remount,ro /boot
}

# enable writing in the root filesystem
function enable_write_root {
	mount -o rw,remount /overlay/lower
}

# disable writing in the root filesystem
function disable_write_root {
	sync /overlay/lower
	mount -o ro,remount /overlay/lower
}

# reset to factory defaults
function factory_reset {
	log "Resetting device"
	# delete existing configuration file
	enable_write_boot
	rm -f $MY_CONFIG
	disable_write_boot
	# stop all the services
	systemctl stop dnsmasq
	systemctl stop hostapd
	systemctl stop motion
	systemctl stop lighttpd
	# format the data partition
	umount -f $DATA_MOUNT_POINT
	mkfs.f2fs -q $DATA_DEVICE
	# reboot
	reboot
}

# factory reset
if [ "$1" = "factory_reset" ]; then
	factory_reset
fi

# reboot
if [ "$1" = "reboot" ]; then
	log "Rebooting the device"
	reboot
fi

# write a default network configuration
function clean_network_config {
	echo "hostname" > $NETWORK_CONFIG
	echo "clientid" >> $NETWORK_CONFIG
	echo "option rapid_commit" >> $NETWORK_CONFIG
	echo "option domain_name_servers, domain_name, domain_search, host_name" >> $NETWORK_CONFIG
	echo "option classless_static_routes" >> $NETWORK_CONFIG
	echo "option ntp_servers" >> $NETWORK_CONFIG
	echo "option interface_mtu" >> $NETWORK_CONFIG
	echo "require dhcp_server_identifier" >> $NETWORK_CONFIG
	echo "slaac private" >> $NETWORK_CONFIG
}

# write a default AP configuration
function clean_ap_config {
	echo "interface=$IFACE" > $AP_CONFIG
	echo "driver=nl80211" >> $AP_CONFIG
	echo "hw_mode=g" >> $AP_CONFIG
	echo "channel=11" >> $AP_CONFIG
	echo "wmm_enabled=0" >> $AP_CONFIG
	echo "macaddr_acl=0" >> $AP_CONFIG
	echo "auth_algs=1" >> $AP_CONFIG
	echo "ignore_broadcast_ssid=0" >> $AP_CONFIG
}

# restart the network services
function restart_network {
	log "Restarting network"
	systemctl daemon-reload
	systemctl stop dhcpcd.service
	ip addr flush dev $IFACE
	systemctl start dhcpcd.service
	systemctl restart networking.service
	sleep 2
}

# load/reload configuration from file
function load_config {
	log "Loading configuration file $MY_CONFIG"
	source $MY_CONFIG 2> /dev/null
}

# save configuration to file
function save_config {
	# set default name if not set
	if [[ -z "$DEVICE_NAME" ]]; then
		DEVICE_NAME="$DEFAULT_NAME"
	fi
	# set default password if not set
	if [[ -z "$DEVICE_PASSWORD" ]]; then
		DEVICE_PASSWORD="$DEFAULT_PASSWORD"
	fi
	# start in AP mode if no mode is set
	if [[ -z "$WIFI_MODE" ]]; then
		WIFI_MODE="AP"
		WIFI_AP_PASSPHRASE="$DEFAULT_PASSWORD"
	fi
	if [[ -z "$NETWORK_REMOTE_ACCESS" ]]; then
		NETWORK_REMOTE_ACCESS=0
	fi
	# set default settings for the camera
	if [[ -z "$CAMERA_RESOLUTION" ]]; then
		CAMERA_RESOLUTION="640x480"
	fi
	if [[ -z "$CAMERA_ROTATE" ]]; then
		CAMERA_ROTATE=0
	fi
	if [[ -z "$CAMERA_FRAMERATE" ]]; then
		CAMERA_FRAMERATE=5
	fi
	if [[ -z "$MOTION_MOVIE" ]]; then
		MOTION_MOVIE=1
	fi
	if [[ -z "$MOTION_THRESHOLD" ]]; then
		MOTION_THRESHOLD=1500
	fi
	if [[ -z "$MOTION_FRAMES" ]]; then
		MOTION_FRAMES=1
	fi
	if [[ -z "$MOTION_EVENT_GAP" ]]; then
		MOTION_EVENT_GAP=60
	fi
	# set default settings for the notifications
	if [[ -z "$EMAIL_ENABLE" ]]; then
		EMAIL_ENABLE=0
	fi
	if [[ -z "$SLACK_ENABLE" ]]; then
		SLACK_ENABLE=0
	fi
	if [[ -z "$DEBUG" ]]; then
		DEBUG=0
	fi
	# write the file
	enable_write_boot
	cat > $MY_CONFIG <<-EOF
### $MY_NAME v$MY_VERSION

# name of the device
DEVICE_NAME='$DEVICE_NAME'
# password of the device (for both web and ssh)
DEVICE_PASSWORD='$DEVICE_PASSWORD'
# timezone of the device that be used for displaying the right time
DEVICE_TIMEZONE='$DEVICE_TIMEZONE'
# country code of the device for connecting to the WiFi network
DEVICE_COUNTRY_CODE='$DEVICE_COUNTRY_CODE'

# WiFi mode for connecting to an existing WiFi network ("CLIENT") or acting as an access point ("AP")
WIFI_MODE='$WIFI_MODE'
# The passphrase to use when connecting to this access point (minimum 8 characters). Leave it empty for no password
WIFI_AP_PASSPHRASE='$WIFI_AP_PASSPHRASE'
# The name of the wireless network (SSID) to connect to
WIFI_CLIENT_SSID='$WIFI_CLIENT_SSID'
# The passphrase to use to connect to the network. Leave it empty for open networks
WIFI_CLIENT_PASSPHRASE='$WIFI_CLIENT_PASSPHRASE'

# Set a static IP address for this device. Leave it empty to use DHCP
NETWORK_IP='$NETWORK_IP'
# Set the default gateway. Leave it empty for having DHCP setting it
NETWORK_GW='$NETWORK_GW'
# Set the DNS server to use. Leave it empty for having DHCP setting it
NETWORK_DNS='$NETWORK_DNS'
# If set the device will be recheable from the Internet through a serveo.net tunnel
NETWORK_REMOTE_ACCESS='$NETWORK_REMOTE_ACCESS'

# The resolution for picture/video (default: 640x480)
CAMERA_RESOLUTION='$CAMERA_RESOLUTION'
# Rotate image this number of degrees (default: 0)
CAMERA_ROTATE='$CAMERA_ROTATE'
# Maximum number of frames to be captured per second (default: 5)
CAMERA_FRAMERATE='$CAMERA_FRAMERATE'

# If set a movie (in addition to the picture) will be recorded upon motion
MOTION_MOVIE='$MOTION_MOVIE'
# Number of changed pixels that triggers motion detection (default: 1500)
MOTION_THRESHOLD='$MOTION_THRESHOLD'
# The minimum number of frames in a row to be considered true motion (default: 1)
MOTION_FRAMES='$MOTION_FRAMES'
# Seconds of no motion that triggers the end of an event (default: 60)
MOTION_EVENT_GAP='$MOTION_EVENT_GAP'

# When a motion is detected, the snapshot is attached to an e-mail message and sent to the configured recipients
EMAIL_ENABLE='$EMAIL_ENABLE'
# The e-mail address the notification has to be sent to. For multiple recipients, separate them with comma
EMAIL_TO='$EMAIL_TO'
# The mail server to use
EMAIL_SERVER='$EMAIL_SERVER'
# Set if the mail server requires SSL/TLS before starting the negotiation
EMAIL_TLS='$EMAIL_TLS'
# Optional username for authenticating against the mail server. Leave it empty for no authentication
EMAIL_USERNAME='$EMAIL_USERNAME'
# Optional password for authenticating against the mail server.
EMAIL_PASSWORD='$EMAIL_PASSWORD'

# When a motion is detected, the snapshot is posted on the configured slack channel
SLACK_ENABLE='$SLACK_ENABLE'
# The token used for authenticating against Slack
SLACK_TOKEN='$SLACK_TOKEN'
# The slack channel to upload the snapshot once motion is detected
SLACK_CHANNEL='$SLACK_CHANNEL'

# enable/disable debug mode to increase the verbosity and print out debug messages for troubleshooting
DEBUG='$DEBUG'
	EOF
	disable_write_boot
}

# print out env variables
if [ "$1" = "env" ]; then
	env
fi

# show configuration file
if [ "$1" = "show_config" ]; then
	cat $MY_CONFIG
fi

# import configuration from file ($2: configuration file)
if [ "$1" = "import_config" ]; then
	if [[ -n "$2" ]]; then
		enable_write_boot
		log "Importing configuration file $2"
		# just overwrite the configuration file
		cp -f $2 $MY_CONFIG
		disable_write_boot
	fi
fi

#################
# Install
#################

# install initramfs modules
function install_initramfs_modules {
	log "Installing initramfs modules"
	append "overlay" $INITRAMFS_MODULES
	append "f2fs" $INITRAMFS_MODULES
}

# install initramfs hooks
function install_initramfs_hooks {
	log "Installing initramfs hooks"
	cat > $INITRAMFS_HOOKS <<-EOF
#!/bin/sh

. /usr/share/initramfs-tools/scripts/functions
. /usr/share/initramfs-tools/hook-functions

# copy the required executables in the initramfs image
copy_exec /sbin/blkid
copy_exec /sbin/mke2fs
copy_exec /sbin/fsck
copy_exec /sbin/fsck.f2fs
copy_exec /sbin/fsck.ext2
copy_exec /sbin/fsck.ext3
copy_exec /sbin/fsck.ext4
copy_exec /sbin/logsave
copy_exec /sbin/findfs
copy_exec /sbin/e2fsck
copy_exec /sbin/resize2fs
copy_exec /sbin/mkfs.f2fs
copy_exec /sbin/fdisk
copy_exec /sbin/partprobe
copy_exec /sbin/dumpe2fs
	EOF
	chmod 755 $INITRAMFS_HOOKS
}

# install initramfs scripts
function install_initramfs_scripts {
	log "Installing initramfs scripts"
	cat > $INITRAMFS_BOTTOM_SCRIPT <<-EOF
#!/bin/sh

# initramfs preamble
PREREQ=""
prereqs()
{
   echo "\$PREREQ"
}

case \$1 in
prereqs)
   prereqs
   exit 0
   ;;
esac

. /scripts/functions

######################
# Initialize
######################
# do not overlay if instructed to do so
if grep -q -E '(^|\s)skipoverlay(\s|$)' /proc/cmdline; then
	log_warning_msg  "Skipping overlay"
	exit 0
fi

######################
# Create an overlay filesystem
######################
# create an overlay base directory
mkdir -p /overlay
# initiate a tmpfs filesystem on it
mount -t tmpfs tmpfs /overlay
# crate the subdirectories required by overlayfs
mkdir -p /overlay/upper
mkdir -p /overlay/work
mkdir -p /overlay/lower

# move the read-only root filesystem into /lower
mount -n -o move \$rootmnt /overlay/lower
# mount the overlayed filesystem as the root mount directory
mount -t overlay overlay -olowerdir=/overlay/lower,upperdir=/overlay/upper,workdir=/overlay/work \$rootmnt
# create an overly directory in the newly mounted root filesystem
mkdir -p \$rootmnt/overlay
# re-bind the overlay directory into its target location
mount -n -o rbind /overlay \$rootmnt/overlay

log_success_msg "Created overlay filesystem"

######################
# Update /etc/fstab
######################
# make a backup copy of fstab
cp \$rootmnt/etc/fstab \$rootmnt/etc/fstab.orig
# create a new fstab file with all the partitions but /
awk '\$2 != "/" {print \$0}' \$rootmnt/etc/fstab.orig > \$rootmnt/etc/fstab
# add the new overlay / partition as it is mounted now
awk '\$2 == "'\$rootmnt'" { \$2 = "/" ; print \$0}' /etc/mtab >> \$rootmnt/etc/fstab

log_success_msg "Updated /etc/fstab"

exit 0
	EOF
	chmod 755 $INITRAMFS_BOTTOM_SCRIPT
	cat > $INITRAMFS_PREMOUNT_SCRIPT <<-EOF
#!/bin/sh

# initramfs preamble
PREREQ=""
prereqs() {
    echo "\$PREREQ"
}
case "\$1" in
    prereqs)
        prereqs
        exit 0
        ;;
esac

. /scripts/functions

######################
# Initialize
######################
# run this script only if instructed to do so
if ! grep -q -E '(^|\s)piwebcam_resize(\s|$)' /proc/cmdline; then
	exit 0
fi

# Variables
DEVICE=/dev/mmcblk0
BOOT_DEVICE=$BOOT_DEVICE
ROOT_DEVICE=$ROOT_DEVICE
DATA_DEVICE=$DATA_DEVICE
ROOT_PARTITION_SIZE=$ROOT_PARTITION_SIZE
DATA_MOUNT_POINT=$DATA_MOUNT_POINT

# partitions information required by this script
ROOT_PARTITION_NUMBER=\${ROOT_DEVICE#/dev/mmcblk0p}
DATA_PARTITION_NUMBER=\${DATA_DEVICE#/dev/mmcblk0p}
ROOT_PARTITION_START=\`fdisk -l|grep \$ROOT_DEVICE|awk '{print \$2}'\`
OLD_PARTUUID=\`fdisk -l \$DEVICE | sed -n 's/Disk identifier: 0x\([^ ]*\)/\1/p'\`

######################
# Resize the root filesystem
######################

log_success_msg "Root filesystem \$ROOT_DEVICE on partition \$ROOT_PARTITION_NUMBER starting at sector \$ROOT_PARTITION_START"
# checking the root filesystem
dumpe2fs -h "\$ROOT_DEVICE"
e2fsck -y -v -f "\$ROOT_DEVICE"
dumpe2fs -h "\$ROOT_DEVICE"
# resize the root filesystem to the given size
log_success_msg "Resizing root filesystem \$ROOT_DEVICE to \$ROOT_PARTITION_SIZE"
resize2fs -f -d 8 "\$ROOT_DEVICE" "\$ROOT_PARTITION_SIZE"
# remove the root partition and re-create it of the given size
log_success_msg "Resizing root partition to \$ROOT_PARTITION_SIZE"
fdisk \$DEVICE <<-EOFF
p
d
\$ROOT_PARTITION_NUMBER
n
p
\$ROOT_PARTITION_NUMBER
\$ROOT_PARTITION_START
+\$ROOT_PARTITION_SIZE
p
w
EOFF
# inform the kernel about the new partitions
partprobe

######################
# Create the data filesystem
######################

ROOT_PARTITION_END=\`fdisk -l|grep \$ROOT_DEVICE|awk '{print \$3}'\`
DATA_PARTITION_START=\$((ROOT_PARTITION_END+1))

# create the data partition just after the root partition
log_success_msg "Creating data partition starting at sector \$DATA_PARTITION_START"
fdisk \$DEVICE <<-EOFF
n
p
\$DATA_PARTITION_NUMBER
\$DATA_PARTITION_START

p
w
EOFF
# inform the kernel about the new partitions
partprobe
# Format the new partition 
log_success_msg "Formatting \$DATA_DEVICE"
mkfs.f2fs -q \$DATA_DEVICE

######################
# Update fstab
######################

# record the new PARTUUID
NEW_PARTUUID=\`fdisk -l \$DEVICE | sed -n 's/Disk identifier: 0x\([^ ]*\)/\1/p'\`
# mount the boot and root filesystems
log_success_msg "Mounting root and boot filesystem"
mkdir rootfs
mkdir bootfs
mount \$BOOT_DEVICE bootfs
mount \$ROOT_DEVICE rootfs
# remove the flag from cmdline.txt
log_success_msg "Fixing cmdline.txt"
sed -i 's/\s*piwebcam_resize\s*//' bootfs/cmdline.txt
# Update PARTUUID
log_success_msg "Updating PARTUUID from \$OLD_PARTUUID to \$NEW_PARTUUID"
sed -i "s/\$OLD_PARTUUID/\$NEW_PARTUUID/g" rootfs/etc/fstab
sed -i "s/\$OLD_PARTUUID/\$NEW_PARTUUID/g" bootfs/cmdline.txt
# add data mount point to fstab
log_success_msg "Updating fstab with mount point \$DATA_MOUNT_POINT"
echo "\$DATA_DEVICE     \$DATA_MOUNT_POINT     f2fs     defaults     0     2" >> rootfs/etc/fstab
# create the mount point if it does not exists
mkdir -p rootfs\$DATA_MOUNT_POINT

# umount the filesystems
log_success_msg "Umounting filesystem"
sync
umount bootfs
umount rootfs

exit 0
	EOF
	chmod 755 $INITRAMFS_PREMOUNT_SCRIPT
}

# create the initramfs image
function install_initramfs {
	log "Installing initramfs image"
	mkinitramfs -o $INITRAMFS_IMAGE
	# make initramfs loading at boot time
	append "initramfs init.gz" $PI_CONFIG
}

# installation and basic configuration
function installer {
	print_banner
	echo "==============================="
	# ask the user confirmation before proceeding
	echo "This device is about to be configured for $MY_NAME."
	if [[ $SILENT_INSTALL == 0 ]]; then
		echo "At the end of the process you will be asked to reboot to make the changes effective."
		echo
		read -p "Do you want to continue? (Y/N)" -r
		echo
		if [[ ! $REPLY =~ ^[Yy]$ ]]; then
			exit
		fi
	fi
	if ! grep  -q "$MY_FILE" $STARTUP_FILE; then
		log "Installing $MY_NAME v$MY_VERSION"
		enable_write_boot
		
		### basic system configuration
		log "Enabling serial output"
		raspi-config nonint do_serial 0	
		log "Enabling SSH"
		raspi-config nonint do_ssh 0
		log "Enabling camera"
		raspi-config nonint do_camera 0
		append "bcm2835-v4l2" /etc/modules
		append "disable_camera_led=1" $PI_CONFIG
		log "Setting hostname to $MY_NAME"
		raspi-config nonint do_hostname $MY_NAME
		
		### install dependencies
		log "Installing dependencies"
		apt-get update
		apt-get -y install busybox dnsmasq hostapd f2fs-tools lighttpd motion php-fpm php-cgi php-gd zip sharutils ssmtp mpack autossh
		apt-get clean
		# disable apt cache
		echo -e 'Dir::Cache::pkgcache "";\nDir::Cache::srcpkgcache "";' | sudo tee /etc/apt/apt.conf.d/00_disable-cache-files
		# stop and disable all the services
		log "Disabling services"
		systemctl stop dnsmasq
		systemctl stop hostapd
		systemctl stop motion
		systemctl stop lighttpd
		systemctl disable hostapd
		systemctl disable dnsmasq
		systemctl disable motion
		
		### create and install initramfs image
		install_initramfs_modules
		install_initramfs_hooks
		install_initramfs_scripts
		install_initramfs
		
		### Startup configuration - run this script with the configure parameter at boot time
		log "Setting to start at boot time"
		local ESCAPED_MY_FILE=`echo $MY_FILE|sed 's|/|\\\/|g'`
		# add a placeholder just before printing the ip address
		sed -i -n 'H;${x;s/^\n//;s/# Print the IP address/#PLACEHOLDER#\n\n&/;p;}' $STARTUP_FILE
		# add this script where the placeholder was placed
		sed -i "s/#PLACEHOLDER#/# Run $MY_NAME\nbash $ESCAPED_MY_FILE configure/" $STARTUP_FILE
		
		### Rename pi user to admin
		log "Renaming pi user to admin"
		cd /tmp
		sed -i 's/pi:x:1000:1000:,,,:\/home\/pi:\/bin\/bash/admin:x:1000:1000:,,,:\/home\/admin:\/bin\/bash/' /etc/passwd
		sed -i -E 's/^pi:(.+)$/admin:\1/' /etc/shadow
		sed -i 's/pi:x:1000:/admin:x:1000:/' /etc/group
		sed -i 's/:pi/:admin/g' /etc/group
		rm -f /etc/sudoers.d/010_pi-nopasswd
		echo "admin ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/010_admin-nopasswd
		mv /home/pi /home/admin
		# set default password
		echo "admin:$MY_NAME" | chpasswd
		
		### Clean-up
		log "Cleaning up wpa_supplicant"
		# generate an empty file in case the device is currently connected to a wifi network
		echo "ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev" > $WIFI_CONFIG
		echo "update_config=1" >> $WIFI_CONFIG
		log "Cleaning up network interfaces"
		echo "source-directory /etc/network/interfaces.d" > /etc/network/interfaces
		log "Making /boot as read-only"
		sed -i 's/\/boot\s\+\S\+\s\+defaults/\/boot    vfat    ro,defaults/' /etc/fstab
		log "Requesting to resize root and create the data partition after rebooting"
		sed -i 's/$/ piwebcam_resize/' $PI_CMDLINE
	
		## Finished!
		echo "==============================="
		echo -e "\e[32mThe device has been configured successfully.\e[0m"
		echo
		echo "A reboot is required to make the changes fully effective."
		echo "Once rebooted, the device will start acting as an Access Point."
		echo "Connect to it and point your browser to http://$MY_NAME.local to finalize the configuration."
		echo
		echo "Credentials:"
		echo "  - WiFI:"
		echo "     - SSID: $DEFAULT_NAME"
		echo "     - Passphrase: $DEFAULT_PASSWORD"
		echo "  - Web:"
		echo "     - Username: admin"
		echo "     - Password: $DEFAULT_PASSWORD"
		echo "  - SSH:"
		echo "     - Username: admin"
		echo "     - Password: $DEFAULT_PASSWORD"
		if [[ $SILENT_INSTALL == 0 ]]; then
			echo
			read -p "Do you want to reboot now? (Y/N)" -r
			echo
			if [[ $REPLY =~ ^[Yy]$ ]]; then
				reboot
			fi
		else
			reboot
		fi
	else
		log "$MY_NAME seems to be already installed. Please re-image the SD card and repeat the installation."
	fi
}

# CLI
if [ "$1" = "install" ]; then
	installer
fi
if [ "$1" = "install-silent" ]; then
	SILENT_INSTALL=1
	installer
fi

#################
# Configure
#################

# configure the system
function configure_system {
	# hostname
	log "Configuring device name to $DEVICE_NAME"
	raspi-config nonint do_hostname "$DEVICE_NAME"
	hostnamectl set-hostname "$DEVICE_NAME"
	/etc/init.d/avahi-daemon restart
	
	# password
	log "Configuring password to $DEVICE_PASSWORD"
	if [[ -n "$DEVICE_PASSWORD" ]]; then
		# change the password with chpasswd
		echo "admin:$DEVICE_PASSWORD" | chpasswd
	else
		# empty password, manipulate directly the shadow file
		sed -i -E 's/^admin:[^:]+:(.+)$/admin::\1/' /etc/shadow
	fi
	# set the web password 
	echo "admin:$DEVICE_PASSWORD" > $WEB_PASSWD_FILE

	# timezone
	if [[ -n "$DEVICE_TIMEZONE" ]]; then
		log "Configuring timezone to $DEVICE_TIMEZONE"
		raspi-config nonint do_change_timezone "$DEVICE_TIMEZONE"
	fi
	
	# country code
	if [[ -n "$DEVICE_COUNTRY_CODE" ]]; then
		log "Configuring country code to $DEVICE_COUNTRY_CODE"
		raspi-config nonint do_wifi_country "$DEVICE_COUNTRY_CODE"
	fi
}

# configure the services
function configure_services {
	### hostapd configuration
	log "Configuring hostapd"
	# enable running as a daemon
	append "DAEMON_CONF=\"$AP_CONFIG\"" $AP_CONFIG_DEFAULT
		
	### dnsmasq configuration
	log "Configuring dnsmasq"
	echo "interface=$IFACE" > $DHCP_CONFIG
	# offer DHCP services on the AP network
	echo "dhcp-range=$AP_NETWORK.2,$AP_NETWORK.50,255.255.255.0,24h" >> $DHCP_CONFIG
	# resolv all hosts to redirect to the device
	echo "address=/#/$AP_NETWORK.1" >> $DHCP_CONFIG
		
	### lighttpd configuration
	log "Configuring lighttpd"
	# enable required modules
	cp $WEB_CONF_AVAILABLE_DIR/05-auth.conf $WEB_CONF_ENABLED_DIR
	cp $WEB_CONF_AVAILABLE_DIR/10-fastcgi.conf $WEB_CONF_ENABLED_DIR
	cp $WEB_CONF_AVAILABLE_DIR/15-fastcgi-php.conf $WEB_CONF_ENABLED_DIR
	# configure h5ai to handle directory indexing
	append "server.error-handler-404   = \"/\"" $WEB_CONFIG
	append 'index-file.names += ("index.html", "index.php", "/_h5ai/public/index.php")' $WEB_CONFIG
	# enable basic authentication
	append "auth.backend = \"plain\"" $WEB_CONFIG
	append "auth.backend.plain.userfile = \"$WEB_PASSWD_FILE\"" $WEB_CONFIG
	append "auth.require = ( \"/\" => (\"method\" => \"basic\", \"realm\" => \"$MY_NAME\", \"require\" => \"valid-user\"),)" $WEB_CONFIG
	# remove lighttpd default index file
	rm -f $WEB_ROOT_DIR/index.lighttpd.html
	# allow www-data user to run this script as root
	echo "www-data ALL=(ALL) NOPASSWD:$MY_FILE" > /etc/sudoers.d/${MY_NAME}_www-data
	/etc/init.d/lighttpd restart
		
	### motion configuration
	log "Configuring motion"
	# set reasonable default settings to motion
	local ESCAPED_DATA_DIR=`echo $DATA_DIR|sed 's|/|\\\/|g'`
	local ESCAPED_MY_FILE=`echo $MY_FILE|sed 's|/|\\\/|g'`
	sed -i 's/start_motion_daemon=no/start_motion_daemon=yes/' $CAMERA_CONFIG_DEFAULT
	sed -i -E 's/^daemon .+/daemon on/' $CAMERA_CONFIG
	sed -i -E 's/^pre_capture .+/pre_capture 2/' $CAMERA_CONFIG
	sed -i -E 's/^post_capture .+/post_capture 2/' $CAMERA_CONFIG
	sed -i -E 's/^max_movie_time .+/max_movie_time 60/' $CAMERA_CONFIG
	sed -i -E 's/^ffmpeg_video_codec .+/ffmpeg_video_codec mkv/' $CAMERA_CONFIG
	sed -i -E 's/^locate_motion_mode .+/locate_motion_mode on/' $CAMERA_CONFIG
	sed -i -E 's/^locate_motion_style .+/locate_motion_style redbox/' $CAMERA_CONFIG
	sed -i -E 's/^text_changes .+/text_changes on/' $CAMERA_CONFIG
	sed -i -E 's/^text_double .+/text_double on/' $CAMERA_CONFIG
	sed -i -E 's/^output_pictures .+/output_pictures best/' $CAMERA_CONFIG
	sed -i -E 's/^locate_motion_mode .+/locate_motion_mode preview/' $CAMERA_CONFIG
	sed -i -E "s/^target_dir .+/target_dir $ESCAPED_DATA_DIR/" $CAMERA_CONFIG
	sed -i -E 's/^snapshot_filename .+/snapshot_filename .snapshots\/%Y_%m_%d_%H%M%S/' $CAMERA_CONFIG
	sed -i -E 's/^picture_filename .+/picture_filename year_%Y\/month_%m\/day_%d\/hour_%H\/%v-%Y_%m_%d_%H%M%S/' $CAMERA_CONFIG
	sed -i -E 's/^movie_filename .+/movie_filename year_%Y\/month_%m\/day_%d\/hour_%H\/video\/%v-%Y_%m_%d_%H%M%S/' $CAMERA_CONFIG
	sed -i -E "s/^; on_picture_save .+/on_picture_save sudo $ESCAPED_MY_FILE notify %f/" $CAMERA_CONFIG
	# allow motion user to run this script as root
	echo "motion ALL=(ALL) NOPASSWD:$MY_FILE" > /etc/sudoers.d/${MY_NAME}_motion
		
	### ssh configuration
	log "Configuring ssh"
	echo "PermitEmptyPasswords yes" >> /etc/ssh/sshd_config
	echo "ssh" >> /etc/securetty
	/etc/init.d/ssh restart
	
	### configure checkup cron job
	(crontab -l 2>/dev/null; echo "*/30 * * * * $MY_FILE checkup 2>&1 >/dev/null")| crontab -
}

# configure remote internet access through serveo.net
function configure_remote_access {
	log "Enabling remote Internet access through http://$DEVICE_NAME.serveo.net"
	killall autossh 2>/dev/null
	sleep 1
	# initiate serveo.net tunnel for web and ssh services
	autossh -f -M 0 -o "ServerAliveInterval 30" -o "ServerAliveCountMax 3" -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -R $DEVICE_NAME:80:localhost:80 -R $DEVICE_NAME:22:localhost:22 serveo.net
}

# configure the network
function configure_network {
	# configure the device to connect to an existing WiFi network
	if [[ "$WIFI_MODE" == "CLIENT" ]]; then
		log "Setting client wifi mode with SSID $WIFI_CLIENT_SSID, passphrase $WIFI_CLIENT_PASSPHRASE"
		# if it was running as an AP, stop the AP services
		iptables -t nat -F
		if pgrep -x "hostapd" > /dev/null; then
			systemctl stop hostapd
			systemctl disable hostapd
		fi
		if pgrep -x "dnsmasq" > /dev/null; then
			systemctl stop dnsmasq
			systemctl disable dnsmasq
		fi
		# clean up the existing network configuration
		clean_network_config
		# if a network IP is set, set the static IP
		if [[ -n "$NETWORK_IP" ]]; then
			log "Setting static network configuration: $NETWORK_IP $NETWORK_GW $NETWORK_DNS"
			#  set the provided static ip
			echo "interface $IFACE" >> $NETWORK_CONFIG
			echo "    static ip_address=$NETWORK_IP/24" >> $NETWORK_CONFIG
			# set the default gateway if provided
			if [[ -n "$NETWORK_GW" ]]; then
				echo "    static routers=$NETWORK_GW" >> $NETWORK_CONFIG
			fi
			# set the DNS if provided
			if [[ -n "$NETWORK_DNS" ]]; then
				echo "    static domain_name_servers=$NETWORK_DNS" >> $NETWORK_CONFIG
			fi
		fi
		# restart network services
		restart_network
		# connect to the wireless network
		raspi-config nonint do_wifi_ssid_passphrase "$WIFI_CLIENT_SSID" "$WIFI_CLIENT_PASSPHRASE"
		# configure remote access
		if [[ $NETWORK_REMOTE_ACCESS == 1 ]]; then
			configure_remote_access
		fi
	# configure the device to act as an access point
	else
		log "Setting AP wifi mode SSID $DEVICE_NAME with passphrase $WIFI_AP_PASSPHRASE"
		# if it was running as CLIENT, stop the CLIENT services
		killall autossh 2>/dev/null
		# write hostapd configuration
		clean_ap_config
		echo "ssid=$DEVICE_NAME" >> $AP_CONFIG
		# if a passphrase is provided, enable secure connection
		if [[ -n "$WIFI_AP_PASSPHRASE" && ${#WIFI_AP_PASSPHRASE} -ge 8 ]]; then
			echo "wpa=2" >> $AP_CONFIG
			echo "wpa_passphrase=$WIFI_AP_PASSPHRASE" >> $AP_CONFIG
			echo "wpa_key_mgmt=WPA-PSK" >> $AP_CONFIG
			echo "wpa_pairwise=TKIP" >> $AP_CONFIG
			echo "rsn_pairwise=CCMP" >> $AP_CONFIG
		fi
		# set a static ip
		local MY_IP=$AP_NETWORK.1
		log "Setting static network configuration to IP $MY_IP"
		clean_network_config
		echo "interface $IFACE" >> $NETWORK_CONFIG
		echo "    static ip_address=$MY_IP/24" >> $NETWORK_CONFIG
		echo "    static routers=$MY_IP" >> $NETWORK_CONFIG
		echo "    static domain_name_servers=$MY_IP" >> $NETWORK_CONFIG
		# prevent connecting as a wifi client
		echo "    nohook wpa_supplicant" >> $NETWORK_CONFIG
		restart_network
		# enable and start the services
		systemctl enable hostapd
		systemctl enable dnsmasq
		systemctl restart hostapd
		systemctl restart dnsmasq
		# intercept all the traffic and redirect it here
		iptables -t nat -F
		iptables -t nat -A PREROUTING -d 0/0 -p tcp --dport 80 -j DNAT --to-destination $AP_NETWORK.1:80
		iptables -t nat -A PREROUTING -d 0/0 -p tcp --dport 443 -j DNAT --to-destination $AP_NETWORK.1:80
		iptables -t nat -A PREROUTING -d 0/0 -p udp --dport 53 -j DNAT --to-destination $AP_NETWORK.1:53
		iptables -t nat -A PREROUTING -d 0/0 -p tcp --dport 22 -j DNAT --to-destination $AP_NETWORK.1:22
	fi
	# allow only specific inbound connections
	iptables -F
	iptables -A INPUT -i lo -j ACCEPT
	iptables -A INPUT -i $IFACE -p tcp -m tcp --dport 22 -j ACCEPT
	iptables -A INPUT -i $IFACE -p tcp -m tcp --dport 80 -j ACCEPT
	iptables -A INPUT -i $IFACE -p icmp -j ACCEPT
	iptables -A INPUT -i $IFACE -p udp --dport 67:68 --sport 67:68 -j ACCEPT
	iptables -A INPUT -i $IFACE -p udp --dport 5353 -j ACCEPT
	iptables -A INPUT -i $IFACE -p udp --dport 53 -j ACCEPT
	iptables -A INPUT -i $IFACE -m state --state RELATED,ESTABLISHED -j ACCEPT
	iptables -P INPUT DROP
	iptables -P OUTPUT ACCEPT
}

# configure the camera
function configure_camera {
	# set camera resolution
	if [[ -n "$CAMERA_RESOLUTION" ]]; then
		log "Setting resolution to $CAMERA_RESOLUTION"
		local WIDTH=`echo $CAMERA_RESOLUTION| sed -E 's/x.+$//'`
		local HEIGHT=`echo $CAMERA_RESOLUTION| sed -E 's/^.+x//'`
		sed -i -E "s/^width .+/width $WIDTH/" $CAMERA_CONFIG
		sed -i -E "s/^height .+/height $HEIGHT/" $CAMERA_CONFIG
	fi
	# set camera rotate
	if [[ -n "$CAMERA_ROTATE" ]]; then
		log "Setting camera rotate by $CAMERA_ROTATE degree"
		sed -i -E "s/^rotate .+/rotate $CAMERA_ROTATE/" $CAMERA_CONFIG
	fi
	# set camera framerate
	if [[ -n "$CAMERA_FRAMERATE" ]]; then
		log "Setting camera framerate to $CAMERA_FRAMERATE"
		sed -i -E "s/^framerate .+/framerate $CAMERA_FRAMERATE/" $CAMERA_CONFIG
	fi
	# set motion movie
	if [[ -n "$MOTION_MOVIE" ]]; then
		log "Setting motion movie to $MOTION_MOVIE"
		if [[ $MOTION_MOVIE == 1 ]]; then
			sed -i -E "s/^ffmpeg_output_movies .+/ffmpeg_output_movies on/" $CAMERA_CONFIG
		else
			sed -i -E "s/^ffmpeg_output_movies .+/ffmpeg_output_movies off/" $CAMERA_CONFIG
		fi
	fi
	# set motion threshold
	if [[ -n "$MOTION_THRESHOLD" ]]; then
		log "Setting motion threshold to $MOTION_THRESHOLD"
		sed -i -E "s/^threshold .+/threshold $MOTION_THRESHOLD/" $CAMERA_CONFIG
	fi
	# set motion frames
	if [[ -n "$MOTION_FRAMES" ]]; then
		log "Setting minimum motion frames to $MOTION_FRAMES"
		sed -i -E "s/^minimum_motion_frames .+/minimum_motion_frames $MOTION_FRAMES/" $CAMERA_CONFIG
	fi
	# set motion event gap
	if [[ -n "$MOTION_EVENT_GAP" ]]; then
		log "Setting motion event gap to $MOTION_EVENT_GAP seconds"
		sed -i -E "s/^event_gap .+/event_gap $MOTION_EVENT_GAP/" $CAMERA_CONFIG
	fi
	# restart motion
	log "Restarting camera"
	/etc/init.d/motion restart
}

# configure the notifications
function configure_notifications {
	log "Configuring mail server $EMAIL_SERVER"
	echo "mailhub=$EMAIL_SERVER" > $EMAIL_CONFIG
	echo "FromLineOverride=YES" >> $EMAIL_CONFIG
	if [[ -n "$EMAIL_USERNAME" ]]; then
		echo "AuthUser=$EMAIL_USERNAME" >> $EMAIL_CONFIG
	fi
	if [[ -n "$EMAIL_PASSWORD" ]]; then
		echo "AuthPass=$EMAIL_PASSWORD" >> $EMAIL_CONFIG
	fi		
	if [[ -n "$EMAIL_TLS" ]]; then
		echo "UseTLS=YES" >> $EMAIL_CONFIG
		echo "UseSTARTTLS=YES" >> $EMAIL_CONFIG
	fi
}

# deploy the admin panel into the web root
function configure_admin_panel {
	# remove all the files from the web root
	rm -rf $WEB_ROOT_DIR/*
	# copy the files
	cp -R $MY_DIR/web/* $WEB_ROOT_DIR
	# setup permissions
	chown -R www-data.www-data $WEB_ROOT_DIR
	# link the playback directory
	ln -s $DATA_DIR $WEB_ROOT_DIR/$PLAYBACK_DIR
}

# CLI
if [ "$1" = "configure" ]; then
	log "Configuring this device for $MY_NAME v$MY_VERSION"
	
	# adjust system settings
	log "Configuring bash"
	echo "source $MY_FILE" >> /etc/bash.bashrc
	log "Configuring to reboot on kernel panic"
	sysctl -w kernel.panic=10
	sysctl -w kernel.panic_on_oops=1
	log "Customizing motd"
	base64 -d <<<"X19fX19fIF8gXyAgICBfICAgICAgXwp8IF9fXyAoXykgfCAgfCB8ICAgIHwgfAp8IHxfLyAvX3wg
fCAgfCB8IF9fX3wgfF9fICAgX19fIF9fIF8gXyBfXyBfX18KfCAgX18vfCB8IHwvXHwgfC8gXyBc
ICdfIFwgLyBfXy8gXyBfIFwKfCB8ICAgfCBcICAvXCAgLyAgX18vIHxfKSB8IChffCAoX3wgfCB8
IHwgfCB8IHwKXF98ICAgfF98XC8gIFwvIFxfX198Xy5fXy8gXF9fX1xfXyxffF98IHxffCB8X3wK
Cgo=" > /etc/motd
	log "Disabling under-voltage warnings"
	sed -i '1s/^/:msg, contains, \"oltage\" stop\n/' /etc/rsyslog.conf
	systemctl restart rsyslog
	log "Disabling swap"
	dphys-swapfile swapoff
	dphys-swapfile uninstall
	systemctl stop dphys-swapfile
	systemctl disable dphys-swapfile
	log "Disabling cron email notifications"
	sed -i '1s/^/MAILTO=""\n/' /etc/crontab
	log "Disabling Power Management on WiFi interface"
	iw dev $IFACE set power_save off
	if [ ! -f $MY_CONFIG ]; then
		log "Generating empty configuration file"
		save_config
	fi
	if [ ! -d $DATA_DIR ]; then
		log "Creating data directory $DATA_DIR"
		mkdir -p $DATA_DIR
		chown motion.motion $DATA_DIR
	fi
	if [ ! -d $PERSIST_DIR ]; then
		mkdir -p $PERSIST_DIR
	fi
	log "Deploying the admin web panel"
	configure_admin_panel
	
	# configure system
	configure_system
	# configure services
	configure_services
	# configure network
	configure_network
	# configure camera
	configure_camera
	# configure notifications
	configure_notifications
fi

if [ "$1" = "configure_system" ]; then
	configure_system
fi

if [ "$1" = "configure_services" ]; then
	configure_services
fi

if [ "$1" = "configure_network" ]; then
	configure_network
fi

if [ "$1" = "configure_camera" ]; then
	configure_camera
fi

if [ "$1" = "configure_notifications" ]; then
	configure_notifications
fi

#################
# Upgrade
#################

# import firmware from file and execute upgrade routine ($1: new firmware path)
function import_firmware {
	if [[ -n "$1" ]]; then
		log "Importing new firmware from $1"
		# unzip the content of the new firmware
		local DIR=`mktemp -d`
		cd $DIR
		unzip "$1" -d $DIR > /dev/null
		# run the upgrade routing
		local FILE=$DIR/$MY_NAME/$MY_NAME.sh
		log "Executing upgrade routine on file $FILE"
		if [ -f $FILE ]; then
			chmod 755 $FILE
			$FILE upgrade $MY_VERSION
		else
			log "Invalid firmware, $FILE not found"
		fi
	fi
}

# run the upgrade routine ($1: version we are upgrading from)
function upgrade {
	if [[ -n "$1" ]]; then
		log "Upgrading from v$1 to v$MY_VERSION"
		enable_write_boot
		# backup current version
		local BACKUP_DIR="$MY_DIR/backup/v$1"
		rm -rf $BACKUP_DIR
		mkdir -p $BACKUP_DIR
		mv $MY_DIR/* $BACKUP_DIR
		# copy in the new files
		cp -R $MY_NAME/* $MY_DIR
		# upgrade configuration file
		if [[ -n "$NAME" ]]; then
			DEVICE_NAME=$NAME
		fi
		if [[ -n "$PASSWORD" ]]; then
			DEVICE_PASSWORD=$PASSWORD
		fi
		if [[ -n "$TIMEZONE" ]]; then
			DEVICE_TIMEZONE=$TIMEZONE
		fi
		if [[ -n "$COUNTRY_CODE" ]]; then
			DEVICE_COUNTRY_CODE=$COUNTRY_CODE
		fi
		if [[ -n "$AP_PASSPHRASE" ]]; then
			WIFI_AP_PASSPHRASE=$AP_PASSPHRASE
		fi
		if [[ -n "$WIFI_SSID" ]]; then
			WIFI_CLIENT_SSID=$WIFI_SSID
		fi
		if [[ -n "$WIFI_PASSPHRASE" ]]; then
			WIFI_CLIENT_PASSPHRASE=$WIFI_PASSPHRASE
		fi
		if [[ -n "$REMOTE_ACCESS" ]]; then
			NETWORK_REMOTE_ACCESS=$REMOTE_ACCESS
		fi
		if [[ -n "$DISABLE_MOVIE" ]]; then
			if [[ $DISABLE_MOVIE = 0 ]]; then
				MOTION_MOVIE=1
			else
				MOTION_MOVIE=0
			fi
		fi
		if [[ -n "$RESOLUTION" ]]; then
			CAMERA_RESOLUTION=$RESOLUTION
		fi
		if [[ -n "$ROTATE" ]]; then
			CAMERA_ROTATE=$ROTATE
		fi
		if [[ -n "$FRAMERATE" ]]; then
			CAMERA_FRAMERATE=$FRAMERATE
		fi
		save_config
		# reload configuration file
		load_config
		disable_write_boot
		# deploy the admin panel
		configure_admin_panel
	fi
}

# CLI
if [ "$1" = "import_firmware" ]; then
	import_firmware $2
fi
if [ "$1" = "upgrade" ]; then
	upgrade $2
fi

#############
# Chroot
#############

# mount the root filesystem as read-write and chroot into it
if [ "$1" = "chroot" ]; then
	# root read-only filesystem
	ROOT="/overlay/lower"
	# remount root and boot as read-write
	mount -o remount,rw $ROOT
	mount -o remount,rw /boot
	# mount other virtual filesystems underneath the root filesystem
	if [ -d $ROOT/proc ]; then
		mount -t proc proc $ROOT/proc
	fi
	if [ -d $ROOT/sys ]; then
		mount -t sysfs sys $ROOT/sys
	fi
	for DIR in dev dev/pts boot run; do
		if [ -d $ROOT/$DIR ]; then
			mount -o bind /$DIR $ROOT/$DIR
		fi
	done
	# chroot into the filesystem
	chroot $ROOT
	
	# done editing, sync and umount virtual filesystems
	sync
	umount $ROOT/proc
	umount $ROOT/sys
	for DIR in dev/pts dev boot run; do
		umount $ROOT/$DIR
	done
	# remount the boot and root filesystem as read-only
	mount -o remount,ro /boot
	mount -o remount,ro $ROOT
fi

#################
# Status
#################

# display status information
if [ "$1" = "status" ]; then
	echo "SYSTEM_UPTIME="`uptime |sed -E 's/^.+up (.+),\s\s\w user.+$/\1/'`
	echo "SYSTEM_CPU="`grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage }'|cut -d '.' -f1`
	echo "SYSTEM_TEMP="`vcgencmd measure_temp|cut -d '=' -f2|cut -d '.' -f1`
	echo "SERVICE_MOTION="`ps uax|grep /usr/bin/motion|grep -v grep|wc -l`
	echo "SERVICE_AP="`ps uax|grep /usr/sbin/hostapd|grep -v grep|wc -l`
	echo "NETWORK_IP="`hostname -I 2>/dev/null`
	echo "NETWORK_INTERNET="$(eval $INTERNET)
	if iwconfig $IFACE|grep -q "Mode:Master"; then
		echo "WIFI_SSID="`cat $AP_CONFIG |grep ^ssid|cut -d "=" -f2`
		echo "WIFI_SIGNAL=100"
	else
		if iwconfig $IFACE|grep -q "ESSID:off/any"; then
			echo "WIFI_SSID=None"
			echo "WIFI_SIGNAL=0"
		else
			echo "WIFI_SSID="`iwconfig $IFACE | grep ESSID | cut -d '"' -f 2`
			S1=`iwconfig $IFACE | awk '{if ($1=="Link"){split($2,A,"/");print A[1]}}'|sed 's/Quality=//g'`
			S2=`iwconfig $IFACE | awk '{if ($1=="Link"){split($2,A,"/");print A[2]}}'`
			echo "WIFI_SIGNAL="$(($S1*100/$S2))
		fi
	fi
	echo "DISK_ROM="`df |grep '/overlay/lower$'|awk '{print $5}'|sed 's/%//'`
	echo "DISK_CACHE="`df |grep '/$'|awk '{print $5}'|sed 's/%//'`
	echo "DISK_DATA="`df |grep "$DATA_MOUNT_POINT"|awk '{print $5}'|sed 's/%//'`
fi

#################
# Notify
#################

# notify about a motion ($2: filename)
if [ "$1" = "notify" ]; then
	PICTURE=$2
	# do not notify when generating a snapshots
	if [[ -n "$PICTURE" && $PICTURE != *"/.snapshots/"*  ]]; then
		# ensure at least one notification is enabled
		if [[ "$EMAIL_ENABLE" = "1" || "$SLACK_ENABLE" = "1" ]]; then
			log "Notifying about $PICTURE"
			# check if we have an internet connection
			if [ $(eval $INTERNET) = "1" ]; then
				if [ "$EMAIL_ENABLE" = "1" ]; then
					# send email notification
					log "Sending email to $EMAIL_TO"
					mpack -s "[$DEVICE_NAME] $NOTIFICATION_SUBJECT" "$PICTURE" "$EMAIL_TO"
				fi
				if [ "$SLACK_ENABLE" = "1" ]; then
					# send slack notification
					log "Sending slack notification to channel $SLACK_CHANNEL"
					curl -F file=@$PICTURE -F channels=$SLACK_CHANNEL -F token=$SLACK_TOKEN initial_comment="[$DEVICE_NAME] $NOTIFICATION_SUBJECT" https://slack.com/api/files.upload
				fi
			else
				# queue the notification
				echo $PICTURE >> $NOTIFICATION_QUEUE
				# if the notification queue is full, remove the oldest entry
				if [[ -f "$NOTIFICATION_QUEUE" && $(cat $NOTIFICATION_QUEUE|wc -l) -gt $NOTIFICATION_QUEUE_SIZE ]]; then
					sed -i -e "1d" $NOTIFICATION_QUEUE
				fi
				log "Disconnected from the network, queuing the notification"
			fi
		fi
	fi
fi

#################
# Checkup
#################

# perform periodic system checkup
if [ "$1" = "checkup" ]; then
	### reboot the device if the overlayed root filesystem is almost full (even if this shouldn't happen)
	if filesystem_full "/" $ROOT_CLEANUP_THRESHOLD; then 
		log "Root filesystem too full, rebooting"
		reboot
	fi

	### remove oldest files from the data directory when the data filesystem is almost full
	if [ -d "$DATA_DIR" ]; then
		# remove all the snapshots
		rm -rf $DATA_DIR/.snapshots
		# remove older files until the filesystem is down to an acceptable usage
		DAYS=365
		while filesystem_full $DATA_MOUNT_POINT $DATA_CLEANUP_THRESHOLD; do
			# find and delete all the directories in the data partition older than the given number of days
			find $DATA_DIR -type d -mtime +$DAYS -exec log "Purging file {}" \; -exec rm -rf "{}" \;
			# increment the counter so at the next cycle we will delete newer files
			DAYS=$(($DAYS-1))
		done
	fi

	### process the notification queue if not empty and we have an Internet connection
	if [[ -f "$NOTIFICATION_QUEUE" && $(eval $INTERNET) = "1" ]]; then
		# for each item run the notify command
		while read in; do log "Processing $in from the notification queue"; $MY_FILE notify $in;  done < $NOTIFICATION_QUEUE
		# empty the queue
		rm -f $NOTIFICATION_QUEUE
	fi

	### ensure the core services are running
	if ! pgrep -x "motion" > /dev/null; then
		log "Restarting motion service"
		/etc/init.d/motion restart
	fi
	if ! pgrep -x "lighttpd" > /dev/null; then
		log "Restarting lighttpd service"
		/etc/init.d/lighttpd restart
	fi
	if [[ "$WIFI_MODE" == "CLIENT" ]]; then
		# if disconnected from Internet, try reconnecting to the network
		if [ $(eval $INTERNET) = "0" ]; then
			log "Disconnected from the network, reconnecting"
			configure_network
		else
			if [[ $NETWORK_REMOTE_ACCESS == 1 ]]; then
				# check if the web service is correctly exposed through serveo.net
				if ! curl -v -m 10 --silent http://$DEVICE_NAME.serveo.net 2>&1 | grep -q "401 - Unauthorized"; then
					log "Remote Internet access not available, reconnecting"
					configure_remote_access
				fi
			fi
		fi
	fi
	# if AP services are not running, reconfigure the network
	if [[ "$WIFI_MODE" == "AP" ]]; then
		if ! pgrep -x "hostapd" > /dev/null; then
			log "hostapd not running, reconfiguring"
			configure_network
		fi
		if ! pgrep -x "dnsmasq" > /dev/null; then
			log "dnsmasq not running, reconfiguring"
			configure_network
		fi
	fi
fi

#############
# User Settings
#############

### system

# set the name
if [ "$1" = "set_device_name" ]; then
	if [[ -n "$2" ]]; then
		log "Saving device name $2"
		DEVICE_NAME=$2
		save_config
	fi
fi

# set the country code ($2: country code)
if [ "$1" = "set_device_country_code" ]; then
	if [[ -n "$2" ]]; then
		log "Saving device country code $2"
		DEVICE_COUNTRY_CODE=$2
		save_config
	fi
fi

# set the timezone
if [ "$1" = "set_device_timezone" ]; then
	if [[ -n "$2" ]]; then
		log "Saving device timezone $2"
		DEVICE_TIMEZONE=$2
		save_config
	fi
fi

# set all the passwords to the given value
if [ "$1" = "set_device_password" ]; then
	if [[ -n "$2" ]]; then
		log "Saving device password $2"
		DEVICE_PASSWORD=$2
		save_config
	fi
fi

# set debug
if [ "$1" = "set_debug" ]; then
	if [[ -n "$2" ]]; then
		log "Saving debug $2"
		DEBUG=$2
		save_config
	fi
fi

### network

# set the WiFi mode ($2: "AP" or "CLIENT")
if [ "$1" = "set_wifi_mode" ]; then
	if [[ -n "$2" ]]; then
		log "Saving WiFi mode $2"
		WIFI_MODE=$2
		save_config
	fi
fi

# set the AP passphrase ($2: optional passphrase)
if [ "$1" = "set_wifi_ap" ]; then
	if [[ -n "$2" ]]; then
		log "Saving AP passphrase $2"
		WIFI_AP_PASSPHRASE=$2
		save_config
	fi
fi

# set wifi client settings ($2: SSID, $3: optional passphrase)
if [ "$1" = "set_wifi_client" ]; then
	if [[ -n "$2" ]]; then
		log "Saving WiFi SSID $2 with passphrase $3"
		WIFI_CLIENT_SSID=$2
		WIFI_CLIENT_PASSPHRASE=$3
		save_config
	fi
fi

# set the network ip ($2: ip, $3: optional gw, $4: optional DNS )
if [ "$1" = "set_network_ip" ]; then
	log "Saving network configuration $2 $3 $2"
	NETWORK_IP=$2
	NETWORK_GW=$3
	NETWORK_DNS=$4
	save_config
fi

# set remote access capability ($2: 1 to enable, 0 to disable)
if [ "$1" = "set_network_remote_access" ]; then
	if [[ -n "$2" ]]; then
		log "Setting network remote access to $2"
		NETWORK_REMOTE_ACCESS=$2
		save_config
	fi
fi

### camera

# set the resolution of the camera ($2: resolution in the format <width>x<height>)
if [ "$1" = "set_camera_resolution" ]; then
	if [[ -n "$2" ]]; then
		log "Saving camera resolution $2"
		CAMERA_RESOLUTION=$2
		save_config
	fi
fi

# rotate the image ($2: degree)
if [ "$1" = "set_camera_rotate" ]; then
	if [[ -n "$2" ]]; then
		log "Saving camera rotate $2 degree"
		CAMERA_ROTATE=$2
		save_config
	fi
fi

# set the camera framerate ($2: framerate)
if [ "$1" = "set_camera_framerate" ]; then
	if [[ -n "$2" ]]; then
		log "Saving camera framerate $2"
		CAMERA_FRAMERATE=$2
		save_config
	fi
fi

# enable/disable saving movie upon motion ($2: 1 to enable, 0 to disable)
if [ "$1" = "set_motion_movie" ]; then
	if [[ -n "$2" ]]; then
		log "Saving motion movie to $2"
		MOTION_MOVIE=$2
		save_config
	fi
fi

# set the camera motion threshold ($2: threshold in pixels)
if [ "$1" = "set_motion_threshold" ]; then
	if [[ -n "$2" ]]; then
		log "Saving motion threshold $2"
		MOTION_THRESHOLD=$2
		save_config
	fi
fi

# set the camera minimum motion frames ($2: frames)
if [ "$1" = "set_motion_frames" ]; then
	if [[ -n "$2" ]]; then
		log "Saving minimum motion frames $2"
		MOTION_FRAMES=$2
		save_config
	fi
fi

# set the camera motion event gap ($2: event gap in seconds)
if [ "$1" = "set_motion_event_gap" ]; then
	if [[ -n "$2" ]]; then
		log "Saving motion event gap to $2"
		MOTION_EVENT_GAP=$2
		save_config
	fi
fi

### notifications

# enable/disable email notifications ($2: 1 enable, 0 disable)
if [ "$1" = "set_email_enable" ]; then
	if [[ -n "$2" ]]; then
		log "Setting email notifications to $2"
		EMAIL_ENABLE=$2
		save_config
	fi
fi

# configure email notifications ($2: to, $3: server, $4: TLS, $5: username, $6: password)
if [ "$1" = "set_email" ]; then
	if [[ -n "$2" && -n "$3" ]]; then
		log "Saving email configuration to $2 $3 $4 $5 $6"
		EMAIL_TO=$2
		EMAIL_SERVER=$3
		EMAIL_TLS=$4
		EMAIL_USERNAME=$5
		EMAIL_PASSWORD=$6
		save_config
	fi
fi

# enable/disable slack notifications ($2: 1 enable, 0 disable)
if [ "$1" = "set_slack_enable" ]; then
	if [[ -n "$2" ]]; then
		log "Setting slack notifications to $2"
		SLACK_ENABLE=$2
		save_config
	fi
fi

# configure slack notifications ($2: token $3: channel)
if [ "$1" = "set_slack" ]; then
	if [[ -n "$2" && -n "$3" ]]; then
		log "Saving slack configuration with token $2 and channel $3"
		SLACK_TOKEN=$2
		SLACK_CHANNEL=$3
		save_config
	fi
fi

