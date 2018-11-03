# Introducing PiWebcam

When I need a webcam for monitoring my house or my backyard I use to go and buy it on Amazon/Aliexpress/ebay/Walmart/etc.
True, you can find very **cheap models** with a lot of features but most of the times I've been very **disappointed of my shopping experience**.

This is because these cheap Chinese or Chinese-rebranded cameras usually comes with a **poorly written software** on board and/or **inadequate hardware**. Sometimes the night vision is not working well or at all, it requires an obscure app to be installed on your mobile for a simple configuration, video recording is not accurate, the camera does not have on-board storage for offline recording, there is no web interface to interact with, if you enable motion detection, it starts originating thousands notifications forcing you to eventually turn everything off. This is just to name only some of the issues I've faced so far. 
I then started exploring **alternative options** such as building it by myself and a **Raspberry Pi** looked to me the most suitable platform for this project due to its size, cost, availability and built-in imaging software and hardware.

There are **already around a good number of projects** for using a Raspberry Pi as a webcam but generally speaking they **requires advanced knowledge** and skills and they look like **more ad-hoc solutions rather than finite products**.

**PiWebcam** is intended to provide a powerful imaging platform **for everyone, regardless of his/her previous knowledge**, ideal for the security camera use case. An installation script will take care of fully configuring the system with reasonable default settings, letting the user customizing through a **clean and mobile-friendly web interface** only a very limited number of relevant parameters. Nevertheless, thanks to its **powerful motion detection feature** augmented by an **object recognition capabilities** powered by an **artificial intelligence model**, PiWebcam can **notify the user** of any detected motion by sending a snapshot to an e-mail recipient or on posting it the user's favorite Slack channel.

With PiWebcam you can definitely build with no effort a professional webcam which works well also for night vision (with automatic control of IR Leds and IR-Cut filter, whenever connected). 

Main features include:

* Can be installed and configured by running a single command
* Simple, mobile-friendly web interface
* Snapshot and video recording upon motion detection
* AI-based object recognition capabilities to prevent false positives
* E-mail and Slack notifications
* Can act as an Access Point or connect to an existing WiFi network
* Can be accessed from the Internet without any additional configuration
* Survive to any power outages
* Can switch automatically to night mode and control IR Leds and IR Cut filter 
* Old files are automatically purged when the SD card is almost full
* Ability to import/export the entire configuration of the device

# Requirements

* Raspberry Pi Model 3 or Raspberry Pi Zero W
* Raspbian Stretch Lite
* Raspberry Pi Camera (any model) or a Generic USB Camera
* SD Card (16GB recommended)

# Installation

Installing PiWecam would require a fresh installation of Raspbian and a SD card. Please do not re-use an existing installation but start from scratch as listed below:

* Download Raspbian Stretch Lite operating system (<https://www.raspberrypi.org/downloads/raspbian/>)
* Write the image to a SD card (<https://www.raspberrypi.org/documentation/installation/installing-images/>)
* Download the latest release of PiWebcam from https://sourceforge.net/projects/piwebcam/files/
* Extract and copy the `PiWebcam` directory into the boot partition (on Windows, the only one accessible)
* For a headless setup, review https://www.raspberrypi.org/documentation/configuration/wireless/headless.md
* Plug the SD card on your Raspberry Pi, power it on and connect to it through SSH (Hostname: raspberrypi.local, Username: pi, Password: raspberry)
* Ensure the Raspberry is connected to Internet
* Run `sudo /boot/PiWebcam/PiWebcam.sh install`

At the end of the installation you will be requested to reboot the device to make the changes fully effective.
All the credentials will be summarized on the screen. 

Once rebooted, the device will start acting as an Access Point. 

Connect to the WiFi network created by the device and point your browser to http://PiWebcam.local to finalize the configuration.

To connect the webcam to an existing WiFi network, go to Device / Network, select "*WiFi Client*" and fill in your
"*WiFi Network*" and "*Passphrase*".

The entire device configuration (camera, network, notification and system settings) can be performed through the web interface. 
The configuration file can be easily exported and imported under Device / System. 

Furthermore, the configuration file (`PiWebcam.conf`) is stored in the boot partition under the PiWebcam folder and can be altered or copied out from there as well.

# Getting Started

PiWebcam already comes with reasonable default settings. Once installed, no additional configuration is required; PiWebcam will start taking snapshots and record videos, whether is connected or not to the network.

To access the webcam and/or customize the settings, connect to the web admin panel (default credentials are summarized at the end of the installation and reported in the "*Default Credentials*" section below). 

The following menu is available through the web interface:

* **Camera**: 
    * **View**: live camera streaming
    * **Playback**: access snapshots and movies recorded upon motion detection grouped by year/month/day/hour
    * **Notifications**: configure e-mail and slack notifications
    * **Settings**: customize camera settings
* **Device**:
    * **Status**: network status and CPU and storage utilization
    * **Network**: configure network settings
    * **System**: configure system settings
	* **Logs**: access PiWebcam logs

A detailed summary of all the available settings is reported in the "*Configuration*" section below.

When a motion is detected, PiWebcam will start recording a video (which will then be made available through the "*Playback*" menu of the web interface).  Once there will be no more motion, a picture highlighting with a red box the detected motion will be stored as well. If the object detection feature is enabled, any motion not containing the configured object will be ignored so to lower down false positives (e.g. if a motion is detected but no person is identified).

When notifications are enabled, the snapshot will be sent to the user's e-mail address and/or posted on the configured Slack channel. If an Internet connection is not available, the notification will be queued and released when the connection is restored next.

##Default Credentials

* **WiFI Access Point**:
    * **SSID**: PiWebcam-*XXXXX*"
    * **Passphrase**: PiWebcam-*XXXXX*
* **Web**:
    * **Username**: admin
    * **Password**: PiWebcam-*XXXXX*
* **SSH**:
    * **Username**: admin
    * **Password**: PiWebcam-*XXXXX*

Where *XXXXXX* are random characters that will be presented at the end of the installation process (e.g. *PiWebcam-d68c2f*)
# Configuration

The following  settings can be customized through the admin web panel.

## System Settings

* **Name**: The name of this device. Will be used as the hostname, as a prefix in the notifications and as sub-domain for Internet access
* **Password**: The same password will be then used for both SSH and web
* **Timezone**: The timezone will be used for displaying the right time
* **Country Code**: The country will be used for connecting to the WiFi
* **LEDs**: Turn on/off both the red and the green leds on the board 
* **Debug**: Increase the verbosity and print out debug messages for troubleshooting.

## Network Settings

* **WiFi Settings**: to make the device acting as an access point or connecting to an existing WiFi network
* **Network Settings**: optional static IP address, DNS, gateway
* **Allow remote Internet access**: if checked, the device will be reachable from the Internet. The service is provided by http://www.serveo.net

## Camera Settings

Camera:

* **Resolution**: Select the resolution for picture/video (default: 640x480)
* **Rotate**: Rotate image this number of degrees (default: 0)
* **Framerate**: Maximum number of frames to be captured per second (default: 2)
* **Night Mode**: Enter night mode manually or automatically (when the value of a pin changes)

Motion Detection: 

* **Record movie**: If checked a movie (in addition to a picture) will be recorded upon motion (default: checked)
* **Threshold**: Number of changed pixels that triggers motion detection (default: 1500)
* **Minimum motion frames**: The minimum number of frames in a row to be considered true motion (default: 1)
* **Event gap**: Seconds of no motion that triggers the end of an event (default: 60)
* **Motion processing**: When a motion is detected, process (e.g. notify and analyze) the entire video instead of just the snapshot picture (default: unchecked)

Image Analysis:

* **Enable object detection**: If checked, upon a motion the image will be further analyzed with an artificial intelligence model to detect a specific object.
* **Token**: The token for authenticating against the cloud service. Click on https://clarifai.com/developer/account/keys/create for generating a new token.
* **Object**: The object that must be present in the image to trigger the motion/notification (e.g. people). Click on https://clarifai.com/demo for testing your image and check with objects are identified
* **Threshold**: The probability threshold for the object to trigger the notification (e.g. 0.9)	
* **False positives**: If checked motion pictures and videos without the object will be kept (but not notified), otherwise they will be deleted as false positives (default: unchecked)

## Notification Settings

* **Enable e-mail notifications**: When a motion is detected, the snapshot is attached to an e-mail message and sent to the configured recipients
* **Recipients**: The e-mail address the notification has to be sent to. For multiple recipients, separate them with comma (e.g. user1@gmail.com, user2@gmail.com).
* **Mail Server**: The mail server to use (e.g. smtp.gmail.com:587)
* **Use TLS/SSL:** Check the box if the mail server requires SSL/TLS before starting the negotiation
* **Username**: Optional username for authenticating against the mail server. Leave it empty for no authentication.
* **Password**: Optional password for authenticating against the mail server

* **Enable Slack notifications**: When a motion is detected, the snapshot is posted on the configured slack channel
* **Token**: The token used for authenticating against Slack. Click on https://api.slack.com/custom-integrations/legacy-tokens for generating a new token.
* **Channel**: The slack channel to upload the snapshot once motion is detected(e.g. #general)

# Technical Overview

All PiWebcam files **reside in the boot partition** of the SD card, in a directory called PiWebcam. This includes a single bash file, `PiWebcam.sh` and the PHP pages for the admin panel.

During the installation process,  a very **basic system configuration** is performed (details can be found in the "*API*" section), an **initramfs image is created** and the **PiWebcam.sh** script is added to */etc/rc.local* so to be **executed at startup** with the "*configure*" parameter.

At the first reboot, the initramfs image will **shrink the root partition** (previously expanded to fill the entire SD card by the Raspbian installer) and **create a data partition** just after.

Both the boot and root filesystems are mounted read-only and an overlay filesystem is created by the initram image upon the root filesystem so that any change to the system is stored in memory only and get lost at the next reboot. In this way the device will be more **robust to misconfigurations**, can be easily **restored to factory defaults** and can **survive  to any power outage** since no system file is ever written to the SD card during normal operations. 
The **data filesystem** is instead formatted with **F2FS** (*Flash-Friendly File System*) which takes into account the characteristics of flash memory-based storage devices. 

During startup, PiWebcam **reads its configuration file** stored at `/boot/PiWebcam/PiWebcam.conf`,  **configure the system, the camera, the network and the notifications** based on the settings found there (details can be found in the "*API*" section) and **deploy the web interface** from `/boot/PiWebcam/web` into the web root location. Just after the installation or after a factory reset a default empty configuration file is created. Since the root filesystem is volatile and any change lost at the next reboot, all the relevant settings are stored in this configuration file.
The **configuration can be exported and imported** through the web interface or altered / copied out by mounting the SD card to any other system and accessing the FAT32 boot partition. When a device is reset to factory defaults, the configuration is simply deleted and the data partition formatted. 

For the initial configuration, there is **no need to install any mobile app or desktop software**: when no configuration is found, the device **acts as an access point** (by leveraging *hostapd* and *dnsmasq*) so the user can connect to it and customize the network settings.

PiWebcam comes with a **responsive, mobile-friendly web interface** based on the Bootstrap SB Admin 2 template (<https://startbootstrap.com/template-overviews/sb-admin-2/), running on *lighttpd* and exposed on port 80. The web panel, written in PHP and protected with basic authentication, has no real business logic but just wraps CLI commands provided by the PiWebcam.sh script which the web server user has the rights to run as root. When changing the password, the same is applied to both the system and the web interface. 

If **remote Internet access** is enabled, the device initiates a SSH tunnel through <https://serveo.net>, **without the need to configure any NAT or UPnP** in your router. The device name is used as hostname and both the web and ssh services are exposed.  Autossh is used to keep the connection always alive.

Motion detection is based on the excellent **Motion Project software** (<https://github.com/Motion-Project>) which, among the others, is capable of generating a single notification when consecutive motions are detected so you will **not be overloaded with notifications**. **Only relevant settings are exposed** to the user while reasonable defaults are set during the device startup. Both **pictures and movies are stored into the data filesystem** and grouped in folders by year/month/day/hour so to allow an easier access. All the recordings can be reviewed through the web interface with h5ai (<https://larsjung.de/h5ai/>),  a **modern file indexer** which allows files and directories to be displayed in a appealing way and providing picture and video previews without the need to download the content beforehand.

When a motion is detected, PiWebcam.sh is invoked with the "*notify*" parameter through the *on_picture_save*/*on_movie_end* motion's event. 
If object detection is enabled for further analyzing the image, the picture is sent to Clarifai (<https://clarifai.com/>) to **recognize all the objects** within the image. This would work great to lower down false positives e.g. if you are interested to know if there is somebody stealing in your house and not just a sudden light change. **For the security camera use case, Clarifai seems to fit best** since pretty accurate to identify objects and people whereas other services could be integrated in the future (Google Vision works great with landscapes but poorly to identify a person, Amazon Rekognition is accurate but prone to false negatives when identifying persons and its authentication schema is complex to integrate in a bash script). If the AI service cannot be reached, the analysis is queue for when the connection is restored. In case of a false positive (e.g. there are no people in the motion picture), by default both the motion picture and video are deleted and no notification is sent.

After that, PiWebcam check if an Internet connection is available and if so, sends out the notification. In addition to traditional e-mail notifications, sent out with *ssmtp*, with the detected motion picture attached, PiWebcam can also upload the same picture to a **Slack channel**. If you don't know Slack, check it out (<https://slack.com/>); it is a great collaboration tool but can also be used to create a group dedicated to your family, grant access to your family members, chat with them and allow PiWebcam or Home Automation utilities  (like e.g. http://my-house.sourceforge.io) to post updates over there.
If there is **no Internet connection, the notification does not get lost** but it is **queued and sent out when the connection is restored**.

Periodically, through cron, PiWebcam.sh is called with the "*checkup*" parameter which will **perform routine tasks to ensure the system is running smoothly**. It will reboot the device if the overlayed root filesystem is almost full (even if this shouldn't happen), **remove oldest pictures and movies** from the data directory when the data filesystem fills up, process the notification queue, **restart core services** if no more running and try reconnecting to the network when disconnected.

PiWebcam logs any activity in data filesystem so they survive a device reboot. Logs can be accessed and reviewed through the web interface as well.

An **upgrade functionality** is provided through the web interface as well. A zip file is expected to be uploaded containing a PiWebcam directory and the files of the new version of the software under it (the same package used during the installation). The device can also check for new updates, download and install them automatically. During an upgrade, all the files are extracted into a temporary directory the the PiWebcam.sh script of the new version is run with the "*upgrade*" parameter so to execute the version-specific upgrade routine. A basic upgrade will just replace the files in /boot/PiWebcam with the new one and the same can be done even manually and install additional software if needed.

When building an outdoor webcam, you might want to add to your project an external IR led board and a mechanical IR Cut filter and PiWebcam can handle all of those on your behalf. Night mode manually or automatically and for the latter a pin of the board (21 BCM) is monitored for changes; when turning HIGH, night mode is entered, when turning LOW is left. Entering night mode means turning on the IR leds (if connected) by setting HIGH a pin (26), toggling the IR CUT filter by sending a pulse to a pin (16 for OFF, 20 for ON) and adjusting camera settings for a better representation of the night scene. Keep in mind the GPIO pins cannot drive directly the IR Cut filter or the IR Leds; you would need a H-Bridge or a transistor in between to provide adequate current.

Upon installation, the system and web password as well as the Access Point passphrase are all set to the default device name. Always keep in mind in PiWebcam usability has been preferred over security in most of the cases (no HTTPS, passwords stored in log files, etc.) so consider further protecting the device from a security standpoint.

## API

All of the PiWebcam functionalities and settings can be controlled through the CLI (e.g. via SSH or serial) by invoking as root the `/boot/PiWebcam/PiWebcam.sh`script with the commands detailed below. For each command a detailed list of action is also provided.
Additionally, any setting exposed through the web interface, can also be set programmatically (by invoking e.g.  <http://user:password@piwebcam-d68c2f.local/system.php?DEVICE_NAME=newName>).

**Install PiWebcam (run manually by the user at the first installation only)**

```
sudo /boot/PiWebcam/PiWebcam.sh install
```

* Enabling serial output
* Enabling SSH
* Enabling camera
* Setting default hostname
* Installing dependencies
* Disabling apt cache
* Stop and disable all the services
* Create and install an initramfs image which:
    * At the first reboot only:
        * Resize the root filesystem previously expanded to fill the entire SD card by the Raspbian installer
        * Create a data partition just after the root partition
        * Format the new partition in a f2fs (<https://en.wikipedia.org/wiki/F2FS>) format 
        * Update fstab to mount the data filesystem
    * At every reboot (unless "*skipoverlay*" is added to /boot/cmdline.txt):
        * Moun the root filesystem as read-only  
        * Create an overlay filesystem (<https://en.wikipedia.org/wiki/OverlayFS>)
        * Initiate a tmpfs filesystem on it
        * Update /etc/fstab
* Add the script with the "configure" parameter to /etc/rc.local so to configure the device at boot time
* Renaming pi user to admin
* Cleaning up wpa_supplicant
* Cleaning up network interfaces
* Making /boot as read-only
* Requesting to resize root and create the data partition after rebooting

**Configure the device for PiWebcam (run through /etc/rc.local at boot time)**

```
sudo /boot/PiWebcam/PiWebcam.sh configure
```

* Configuring to reboot on kernel panic
* Customizing motd
* Disabling under-voltage warnings
* Disabling swap (since already running in tmpfs)
* Disabling cron email notifications
* Disabling Power Management on WiFi interface
* Generating empty configuration file (if not already there)
* Creating data directory (if not already there)
* Configure the GPIO
* Deploying the admin web panel from /boot into the web root location
* Run *configure_system*
* Run *configure_services*
* Run *configure_network*
* Run *configure_camera*
* Run *configure_notifications*
* Monitor the value of the night mode pin by running as a background process 

**Apply system settings configuration (run during startup and when changing system settings through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh configure_system
```

* Configuring device name
* Configuring system password
* Configuring timezone
* Configuring country code

**Apply services configuration (run during startup)**

```
sudo /boot/PiWebcam/PiWebcam.sh configure_services
```

* Configuring hostapd
    * Allow running as a daemon
* Configuring dnsmasq
    * Offer DHCP services on the AP network 
    * Resolve all hosts to redirect to the device
* Configuring lighttpd
    * Enable required modules
    * Configure h5ai to handle directory indexing (used by the "*Playback*" menu)
    * Enable basic authentication to allow the admin user to access the web interface
    * Remove lighttpd default index file
    * Allow www-data user to run this script as root through a sudoers configuration file (used by the web interface)
* Configuring motion
    * Set reasonable default settings to motion configuration file
    * Allow motion user to run this script as root through a sudoers configuration file (used for notifications)
* Configuring SSH
    * Allow empty passwords
* Configure cron
    * Add a cron job for running this script with the "*checkup*"parameter periodically

**Apply network configuration (run during startup and when changing network settings through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh configure_network
```

* If WiFi mode is set to Wireless client:
    * stop the AP services
    * clean up the existing network configuration
    * if a network IP is set, set the static IP / DNS / Gateway
    * restart network services
    * connect to the wireless network
    * if remote access is set initiate serveo.net tunnel for web and ssh services
* If WiFi mode is set to Access Point:
    * write hostapd configuration
    * if a passphrase is provided, enable secure connection
    * set a static ip
    * prevent connecting as a wifi client
    * enable and start hostapd and dnsmasq services
    * intercept all the traffic and redirect it here
* allow only specific inbound connections

**Apply camera configuration (run during startup and when changing camera settings through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh configure_camera
```

* Set user's provided settings in motion configuration file
* Restart the motion service

**Apply notifications configuration (run during startup and when changing notification settings through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh configure_notifications
```

* Configure SSMTP mail server 

**Format the data partition (run when performing a Data Reset through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh data_reset
```

* Format the data partition

**Restore to factory defaults (run when performing a Factory Reset through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh factory_reset
```

* Delete the configuration file from */boot/PiWebcam/PiWebcam.conf*
* Format the data partition

**Reboot the device (run when performing a reboot through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh reboot
```

* Reboot the device

**Show PiWebcam logs (run when accessing the logs through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh show_logs
```

* Show the latest PiWebcam logs

**Show the configuration file**

```
sudo /boot/PiWebcam/PiWebcam.sh show_config
```

* Read and return the configuration file */boot/PiWebcam/PiWebcam.conf*

**Import configuration from file (run when importing a new configuration through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh import_config <file>
```

* Copy the provided temporary file into */boot/PiWebcam/PiWebcam.conf*

**Import firmware from file and execute upgrade routine (run when performing a System Update through the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh import_firmware <PiWebcam.zip>
```

* Unzip the content of the new firmware provided as argument
* Executing upgrade routine on the extracted PiWebcam.sh file

**Run the upgrade routine of the new firmware  (run by "import_firmware")**

```
sudo /boot/PiWebcam/PiWebcam.sh upgrade
```

* Version-specific upgrade routine

**Mount the root filesystem as read-write and chroot into it**

```
sudo /boot/PiWebcam/PiWebcam.sh chroot [command]
```

* remount root and boot filesystems as read-write
* mount other virtual filesystems underneath the root filesystem
* chroot into the filesystem to allow performing persistent changes to the root filesystem
* run an optional command provided
* once exited, sync and umount virtual filesystems
* remount the boot and root filesystem as read-only

**Display status information (run when accessing the Status page of the web panel)**

```
sudo /boot/PiWebcam/PiWebcam.sh status
```

* display status information such as:
    * system uptime
    * cpu utilization
    * temperature
    * root filesystem utilization (ROM Used)
    * overlay filesystem utilization (Cache Used)
    * data filesystem utilization (Storage Used)
    * network ip
    * WiFi SSID
    * WiFi Signal level
    * Motion/Access point services status

**Notify about a motion (run by motion for "on_save" events)**

```
sudo /boot/PiWebcam/PiWebcam.sh motion <filename>
```

* If an Internet connection is available:
	* perform image analysis and object detection (if configured)
    * send email notification (if configured)
    * send slack notification (if configured)
* If there disconnected from Internet:
    * queue the notification to a text file in the data filesystem
    * if the notification queue is full, remove the oldest entry

**Perform a periodic system checkup (run by cron)**

```
sudo /boot/PiWebcam/PiWebcam.sh checkup
```

* reboot the device if the overlayed root filesystem is almost full (even if this shouldn't happen)
* remove oldest files from the data directory when the data filesystem is almost full
* process the notification queue if not empty and we have an Internet connection
* Restart core services if no more running
* if disconnected from Internet, try reconnecting to the network

**Manually enable/disable night mode (run during startup if night mode is "ON" or "OFF" or, when "AUTO", by the "night_mode_service" process)**

```
sudo /boot/PiWebcam/PiWebcam.sh night_mode <0|1>
```

* toggle the IR Cut filter by sending a pulse to its pins (if connected)
* turns on/off the IR Leds (if connected)

**Check the requested night mode through the value of a pin (run during startup as a background process)**

```
sudo /boot/PiWebcam/PiWebcam.sh night_mode_service
```

* start in day mode when controlling night mode
* wait for a (change) interrupt on the night mode trigger pin
* read and set the requested mode (LOW: day, HIGH: night)
* continue the cycle

**Save a configuration setting (run when the saving settings from the web panel. Require "configure_*" to apply the changes)**

```
sudo /boot/PiWebcam/PiWebcam.sh set <setting> <value>
```

Where setting is one of the following (the same setting is stored in the configuration file):

Setting  | Description
-------  | ------
DEVICE_NAME | name of the device
DEVICE_PASSWORD | password of the device
DEVICE_TIMEZONE | timezone of the device
DEVICE_COUNTRY_CODE | country code of the device
DEVICE_LED | If unchecked both the red and the green leds on the board will be turned off (default: checked)
WIFI_MODE | Connect to an existing WiFi network ("CLIENT") or act as an access point ("AP")
WIFI_AP_PASSPHRASE | The passphrase to use when connecting to this access point
WIFI_CLIENT_SSID | The name of the wireless network (SSID) to connect to
WIFI_CLIENT_PASSPHRASE | The passphrase to use to connect to the network
NETWORK_IP | static IP address for this device
NETWORK_GW | default gateway
NETWORK_DNS | DNS server
NETWORK_REMOTE_ACCESS | If set the device will be reachable from the Internet
CAMERA_RESOLUTION | The resolution for picture/video
CAMERA_ROTATE | Rotate image this number of degrees
CAMERA_FRAMERATE | Maximum number of frames to be captured per second
CAMERA_NIGHT_MODE | Adjust the camera settings and the IR LEDs and Cut filter (if connected) for night vision. It can be ON (always night), OFF (always day) or AUTO (enter night mode when the value of a pin changes)
MOTION_RECORD_MOVIE | If set a movie (in addition to the picture) will be recorded upon motion
MOTION_THRESHOLD | Number of changed pixels that triggers motion detection
MOTION_FRAMES | The minimum number of frames in a row to be considered true motion
MOTION_EVENT_GAP | Seconds of no motion that triggers the end of an event
MOTION_PROCESS_MOVIE | When a motion is detected, process (e.g. notify and analyze) the entire video instead of just the snapshot picture
AI_ENABLE | If checked, upon a motion the image will be further analyzed with an AI model to detect a specific object
AI_TOKEN | The API key for authenticating against the AI service
AI_OBJECT | The object that must be present in the image to trigger the notification
AI_THRESHOLD | The probability threshold for the object to trigger the notification
AI_KEEP_NOT_FOUND | If checked motion pictures and videos without the object will be kept (but not notified), otherwise they will be deleted as false positives
EMAIL_ENABLE | Enable email notifications
EMAIL_TO | Set email recipients
EMAIL_SERVER | The mail server to use
EMAIL_TLS | Set if the mail server requires SSL/TLS before starting the negotiation
EMAIL_USERNAME | Optional username for the authentication
EMAIL_PASSWORD | Optional password for the authentication
SLACK_ENABLE | Enable Slack notifications
SLACK_TOKEN | The token used for authenticating against Slack
SLACK_CHANNEL | The slack channel to upload the snapshot once motion is detected
DEBUG | Enable/disable debug mode for troubleshooting

# Bill of Materials

## Indoor Webcam

The following sample BOM could be considered for building an indoor webcam:

Item  | Cost | Link
------  | ------| ------ 
Raspberry Pi Zero W  | $20 | https://www.aliexpress.com/item/-/32836672820.html
Acrylic Case | $1 | https://www.aliexpress.com/item/VONETS-Acrylic-Case-Cover-Box-Shell-Aluminum-Heatsinks-40-Pin-Connector-Header-Screwdriver-Screw-Set-for/32811914803.html
16GB SD Card | $5 | https://www.aliexpress.com/item/Original-SanDisk-80mb-s-class-10-128gb-64gb-32gb-16gb-Ultra-memory-TF-micro-SD-Card/32689968307.html
2.5A  Power Supply | $3 | https://www.aliexpress.com/item/5V2A-Power-Supply-Adapter-Micro-Port-Power-Charger-US-EU-UK-AU-With-ON-OFF/32520570725.html
Camera cable for Pi Zero | $1 | https://www.aliexpress.com/item/For-Raspberry-Pi-Zero-Camera-Cable-FFC-Cable-For-Raspberry-Pi-zero-W-H-zero-1/32890256486.html
Acrylic Camera Holder | $1 | https://www.aliexpress.com/item/Acrylic-Holder-Camera-Mount-Bracket-For-Raspberry-Pi-3-B-3-Mounting-Bracket/32902702298.html
Raspberry Camera Night Vision with IR-CUT and Wide Angle  | $17 | https://www.aliexpress.com/item/Raspberry-Pi-3-B-IR-CUT-Camera-Night-Vision-Focal-Adjustable-5MP-Fish-Eye-Automatically-Switch/32881466491.html
| **$48** |

## Outdoor Webcam

The following sample BOM could be considered for building an outdoor webcam:

Item  | Cost | Link
------  | ------| ------ 
Raspberry Pi Zero W  | $20 | https://www.aliexpress.com/item/-/32836672820.html
Waterproof Camera Housing  | $7 | https://www.aliexpress.com/item/CCTV-Camera-Housing-Outdoor-Bullet-Camera-s-Case-Shell-Whit-for-Security-CCTV-IR-IP-Camera/32844215710.html
16GB SD Card | $5 | https://www.aliexpress.com/item/Original-SanDisk-80mb-s-class-10-128gb-64gb-32gb-16gb-Ultra-memory-TF-micro-SD-Card/32689968307.html
IR Cut Double Filter | $1 | https://www.aliexpress.com/item/HD-MP-IR-CUT-filter-M12-0-5-lens-mount-double-filter-switcher-for-cctv-camera/32793880567.html
12v - 5v buck regulator | $1 | https://www.aliexpress.com/item/Ultra-small-LM2596-power-supply-module-DC-DC-BUCK-3A-adjustable-buck-module-regulator-ultra-LM2596S/32440888820.html
Micro USB Male plug | $1 | https://www.aliexpress.com/item/10sets-lot-Micro-5P-USB-Male-Plug-Solder-Type-Tail-Charging-Plug-90-Degree-Free-Shipping/32846969677.html
12v Female plug | $1 | https://www.aliexpress.com/item/Tanbaby-5pcs-lot-5-5-X-2-1mm-12V-Male-and-Female-DC-Cable-Connector-Adapter/32776328118.html
12v IR Led Array | $2 | https://www.aliexpress.com/item/52mm-diameter-4-array-IR-led-Spot-Light-Infrared-4x-IR-LED-board-DC12V-300-400mA/32904475675.html
12v 3A Power Supply | $4 | https://www.aliexpress.com/item/5V-24V-12V-Lighting-Transformer-AC-110V-220V-to-12V-Power-Supply-1A-2A-3A-5A/32808214233.html
Raspberry Camera Night Vision Wide Angle  | $13 | https://www.aliexpress.com/item/Raspberry-Pi-Zero-W-Camera-Module-Night-Vision-Wide-Angle-Fisheye-5MP-Webcam-with-Infrared-IR/32802572768.html
| **$55** |

# About this project

* Project Page: http://piwebcam.sourceforge.io
* User Manual: https://sourceforge.net/p/piwebcam/wiki
* Bug Report: https://sourceforge.net/p/piwebcam/tickets
* Instructables: https://www.instructables.com/id/Fully-featured-Outdoor-Security-Camera-Based-on-Ra/

## Changelog
* v1.2 (Build 080eedc):
	* Fixed a bug preventing notifications from being sent
	* Fixed a bug preventing logs from being generated
	* Fixed a bug causing the AP to result unreachable after a passphrase change
	* The device now reboots after importing a new configuration
	* Improved image quality and motion detection when switching between day and night
	* Default framerate set to 2 to limit CPU utilization
* v1.1:
	* Added support for object detection and automatic image analysis through artificial intelligence models
	* Added support for night vision and management of an IR Cut filter and IR Leds
	* Added configurable camera framerate and motion event gap settings to the web interface
	* Added functionality to check for updates, download and install them automatically
	* Added option to disable (turn off) the power led of the board
	* PiWebcam logs now accessible from the web interface
	* Added option to notify the full movie instead of just the snapshot of the motion
	* Added option to delete all the user's data but keeping the configuration ("Data Reset")
	* Quicker startup and more robust shutdown/reboot processes
	* Enhanced the upgrade routine
	* The previous version is backed up during an upgrade
	* The refresh interval of the live view can now be adjusted in realtime
	* New API call "set" replacing all the "set_*" functions
	* Fixed compatibility issues between Raspberry Pi Model 3 and Zero W
	* Other minor fixes
* v1.0:
    * First Release
