# Modified NuxBill - PHP Mikrotik Billing System

A modified version of PHPNuxBill with enhanced features for device management, payment gateways, and improved customer experience.

![Modified NuxBill](install/img/logo.png)

## Feature

- Voucher Generator and Print
- [Freeradius](https://github.com/hotspotbilling/phpnuxbill/wiki/FreeRadius)
- Self registration
- User Balance
- Auto Renewal Package using Balance
- Multi Router Mikrotik
- Hotspot & PPPOE
- Easy Installation
- Multi Language
- Payment Gateway
- SMS validation for login
- Whatsapp Notification to Consumer
- Telegram Notification for Admin

## Modified Features (wilndotcom Fork)

- **Device Access** - CPE Device Management Dashboard with statistics and charts
- **Fiber Management** - OLT Devices, ONU Management, CPE Routers, Profiles
- **Support Ticket System** - Customer tickets with notifications and admin replies
- **Customer Wallet** - Balance/debt display with sidebar menu integration
- **M-Pesa Payment Gateways** - STK Push, Paybill/Till/Bank integration
- **Hotspot Plugin** - MikroTik Hotspot user management
- **CPE Device Access** - Tenda, Ubiquiti, Huawei, TP-Link device management
- **Customer Notifications** - Sound alerts and banner notifications for new messages
- **Ticket Notifications** - Unread ticket reply alerts for customers
- **Form Autofill** - Improved browser autocomplete support
- **GPL-2.0 License** - Open source with full rights, freedom to use/modify/distribute

See [How it Works / Cara Kerja](https://github.com/hotspotbilling/phpnuxbill/wiki/How-It-Works---Cara-kerja)

## Payment Gateway And Plugin

### Available Payment Gateways:
- **M-Pesa STK Push** - STK push notification to customer phone
- **M-Pesa Paybill/Till/Bank** - Paybill, Till Number, Bank Account integration
- [More Payment Gateways](https://github.com/orgs/hotspotbilling/repositories?q=payment+gateway)

### Available Plugins:
- **Hotspot Plugin** - MikroTik Hotspot user creation and management
- **Asset Manager** - Equipment and asset tracking
- **Speedtest** - Network speed testing
- [More Plugins](https://github.com/orgs/hotspotbilling/repositories?q=plugin)

You can download payment gateway and Plugin from Plugin Manager

## System Requirements

Most current web servers with PHP & MySQL installed will be capable of running PHPNuxBill

Minimum Requirements

- Linux or Windows OS
- Minimum PHP Version 8.2
- Both PDO & MySQLi Support
- PHP-GD2 Image Library
- PHP-CURL
- PHP-ZIP
- PHP-Mbstring
- MySQL Version 4.1.x and above

can be Installed in Raspberry Pi Device.

The problem with windows is hard to set cronjob, better Linux

## Changelog

[CHANGELOG.md](CHANGELOG.md)

## Installation

[Installation instructions](https://github.com/hotspotbilling/phpnuxbill/wiki)

### Device Access Setup
1. Go to **Admin → Device Access** menu
2. Run the installer or create `tbl_cpe_devices` table manually
3. Add your CPE devices (Tenda, Ubiquiti, Huawei, TP-Link)

### Payment Gateway Setup
1. Go to **Admin → Payment Gateway**
2. Configure M-Pesa credentials (Consumer Key, Secret, Passkey)
3. Set your Shortcode (Paybill/Till Number)

## Freeradius

Support [Freeradius with Database](https://github.com/hotspotbilling/phpnuxbill/wiki/FreeRadius)

## Community Support

- [Github Discussion](https://github.com/hotspotbilling/phpnuxbill/discussions)
- [Telegram Group](https://t.me/phpmixbill)

## Technical Support

This Software is Free and Open Source, Without any Warranty.

Even if the software is free, but Technical Support is not,
Technical Support Start from Rp 500.000 or $50

If you chat me for any technical support,
you need to pay,

ask anything for free in the [discussion](/hotspotbilling/phpnuxbill/discussions) page or [Telegram Group](https://t.me/phpnuxbill)

Contact me at [Telegram](https://t.me/ibnux)

## License

GNU General Public License version 2 or later

see [LICENSE](LICENSE) file


## Donate

Support the ongoing development of this modified NuxBill project.

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://paypal.me/wilndotcom)

### Original Project
Donations to the original PHPNuxBill author (Ibnu Maksum):
[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://paypal.me/ibnux)

## SPONSORS

- [mixradius.com](https://mixradius.com/) Paid Services Billing Radius
- [mlink.id](https://mlink.id)
- [https://github.com/sonyinside](https://github.com/sonyinside)

## Thanks
Special thanks to the original PHPNuxBill project by Ibnu Maksum and all contributors. This modified version by wilndotcom extends the original with additional features for device management, payment gateways, and enhanced customer experience.

<a href="https://github.com/wilndotcom/modified-nuxbill/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=wilndotcom/modified-nuxbill" />
</a>
