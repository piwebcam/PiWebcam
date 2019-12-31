# User Guide

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

## Web Interface

The following  settings can be customized through the admin web panel.

### System Settings

* **Name**: The name of this device. Will be used as the hostname, as a prefix in the notifications and as sub-domain for Internet access
* **Password**: The same password will be then used for both SSH and web
* **Timezone**: The timezone will be used for displaying the right time
* **Country Code**: The country will be used for connecting to the WiFi
* **LEDs**: Turn on/off both the red and the green leds on the board 
* **Debug**: Increase the verbosity and print out debug messages for troubleshooting.

### Network Settings

* **WiFi Settings**: to make the device acting as an access point or connecting to an existing WiFi network
* **Network Settings**: optional static IP address, DNS, gateway
* **Allow remote Internet access**: if checked, the device will be reachable from the Internet. The service is provided by http://www.serveo.net

### Camera Settings

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

### Notification Settings

* **Enable e-mail notifications**: When a motion is detected, the snapshot is attached to an e-mail message and sent to the configured recipients
* **Recipients**: The e-mail address the notification has to be sent to. For multiple recipients, separate them with comma (e.g. user1@gmail.com, user2@gmail.com).
* **Mail Server**: The mail server to use (e.g. smtp.gmail.com:587)
* **Use TLS/SSL:** Check the box if the mail server requires SSL/TLS before starting the negotiation
* **Username**: Optional username for authenticating against the mail server. Leave it empty for no authentication.
* **Password**: Optional password for authenticating against the mail server

* **Enable Slack notifications**: When a motion is detected, the snapshot is posted on the configured slack channel
* **Token**: The token used for authenticating against Slack. Click on https://api.slack.com/custom-integrations/legacy-tokens for generating a new token.
* **Channel**: The slack channel to upload the snapshot once motion is detected(e.g. #general)

## CLI

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

## About

* Project Page: https://piwebcam.github.io
* Bug Report: https://github.com/piwebcam/PiWebcam/issues
* Instructables: https://www.instructables.com/id/Fully-featured-Outdoor-Security-Camera-Based-on-Ra/
