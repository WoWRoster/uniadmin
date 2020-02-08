# UniAdmin

UniAdmin is a back-end web-based tool for managing the configuration of and logos in UniUploader and
auto-updating WoW addons.

If you don't know what UniUploader is, then... well... you're a bit ahead of yourself then aren't you? ;-)

<!-- TOC -->

- [1. Requirements](#1-requirements)
- [2. Recommended Tools](#2-recommended-tools)
- [3. Installation](#3-installation)
- [4. Upgrading](#4-upgrading)
  - [4.1. Standard Upgrade Instructions](#41-standard-upgrade-instructions)
  - [4.2. Upgrade from 0.7.0](#42-upgrade-from-070)
  - [4.3. Upgrade from 0.7.5](#43-upgrade-from-075)
- [5. Thanks](#5-thanks)
- [6. FAQ](#6-faq)
- [7. Support](#7-support)
- [8. License](#8-license)
- [9. Known Bugs / Gotchyas](#9-known-bugs--gotchyas)
- [10. Change Log](#10-change-log)
- [11. The Future](#11-the-future)

<!-- /TOC -->

## 1. Requirements

- A web server (Apache, IIS, or any software able to run php)
- PHP 4.3 or higher <http://www.php.net>
- MySQL Database 4.0 or higher <http://www.mysql.com>

## 2. Recommended Tools

- phpMyAdmin or another tool for managing MySQL databases

## 3. Installation

1. Create a new database (eg. `uniadmin`)
1. Upload the contents of the zip file to your webserver
1. After FTPing, CHMOD the following folders to `0777`, or change NTFS file permissions for these folders to make them available as "Everyone - Write" on a Windows machine.

    ```ini
    [uniadmin]
      addon_temp
      addon_zips
      cache
      logos
    ```

1. Go to your UniAdmin install on the web and follow the instructions

The admin user is created on installation

Read the help page for additional info.

## 4. Upgrading

### 4.1. Standard Upgrade Instructions

Run index.php?p=upgrade and follow the instructions

### 4.2. Upgrade from 0.7.0

There is no upgrade from 0.7.0

Upgrading from 0.7.0 to a higher version will force you to install fresh

### 4.3. Upgrade from 0.7.5

It is suggested that you clear all your addons after upgrading

This is because the auto "Full Path" scanner has been implemented

javaUniUploader and phpUniUploader require this new setting to function properly

## 5. Thanks

Name       | Contribution
------     | ------
sturmy     |
fubu2k     | French localization
Carasak    |
Shadowsong | German localization
Zajsoft    | Great modifications to AddOn uploading, providing a better `.toc` file scanner
Zeryl      | Help with parsing strings into multi-dimensional arrays<br>WoWAce module code
Exerladan  | Code to parse the new WoWAce XML file list<br>WoWAce file list XML format parsing

## 6. FAQ

**Q.** I'm not sure what I set `SYNCHROURL` and `PRIMARYURL` to in the Settings Management page.

**A.** `SYNCHROURL` is the URL path to the UniAdmin interface.php, eg. `http://www.myserver.com/uniadmin/interface.php` `PRIMARYURL` is the URL path to the upload page of the system you are sending data to, eg. for the WoW Roster it would be `http://www.myserver.com/roster/update.php` . For other systems, please check with the site owner or their on-line help for the appropriate URL.

**Q.** I'm still confused about the settings in the Settings Management page and how to configure them.

**A.**

1. First, hover your mouse over each setting, you'll get a tooltip with the corresponding part of the UniUploader interface.
1. If you are still confused
    - Manually configure UniUploader with the settings needed for your config.
      - Open the `settings.ini` and you'll see all the settings just like in the Settings Management page.
    - Or just upload your copy of `settings.ini` to the settings page in UniAdmin.

**Q.** How do I reset a password

**A.**

1. Go to http://gdataonline.com/makehash.php
1. Type desired password in
1. Generate the hash
1. Put the hash in the password field of users table in UniAdmin database - for the correct user(s)

## 7. Support

For any support issues, questions, comments, feedback, or suggestions please go to the support forums

<http://www.wowroster.net/Forums/viewforum/f=24.html>

## 8. License

UniAdmin is licensed under the GNU General Public License v3.
Please see [licence.txt](licence.txt) for more info.

## 9. Known Bugs / Gotchyas

**Bug:** Addon zip files that have more than one addon in them may not show correctly

**Solution:** After you upload an addon zip, edit the info on the addon details page

## 10. Change Log

See [changelog.txt](changelog.txt).

## 11. The Future

List for future versions of UA
