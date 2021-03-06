[![Open Web Presence](https://openwebpresence.com/img/footer-logo.png)]
(http://openwebpresence.com/ "Open Web Presence")

# Open Web Presence Framework Support Library

[![Author](https://img.shields.io/badge/author-btafoya@briantafoya.com-blue.svg?style=flat-square)](https://www.briantafoya.com)
[![GitHub Tag](https://img.shields.io/github/tag/openwebpresence/owp-framework.svg?style=flat-square)](https://github.com/openwebpresence/owp-framework)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist](https://img.shields.io/packagist/dt/openwebpresence/owp-framework.svg?maxAge=86400&style=flat-square)](https://packagist.org/packages/openwebpresence/owp-framework)
[![Build Status](https://travis-ci.org/openwebpresence/owp-framework.png?branch=master&style=flat-square)](https://travis-ci.org/openwebpresence/owp-framework)

This framework is corrently under active development (Feb, 2017) and subject to MANY changes until version 2.0 is completed and released officially.

Visit [Open Web Presence](http://openwebpresence.com) for more information.

Class documentation can be found in the generated [phpDoc](https://openwebpresence.github.io/owp-framework/index.html).

### Features

* Operating framework for [OpenWebPresence](https://openwebpresence.com).
* Provide MySQL debugging suppport via [FirePHPCode](https://github.com/firephp/firephp-core).
* Send email via [phpMailer](https://github.com/PHPMailer/PHPMailer).
* Operating framework for [OpenWebPresence](https://openwebpresence.com).
* Provide _get/_set data methods for the [OpenWebPresence](https://openwebpresence.com) framework.
* Misc support methods for the [OpenWebPresence](https://openwebpresence.com) framework.

### User Class Hooks

* owpUDF_On_addUserValiateData(object db, object firephp, array user_data)
* owpUDF_On_addUserSuccess(int userID, object db, object firephp);
* owpUDF_On_deleteUser(int userID, object db, object firephp);
* owpUDF_On_logOut(int userID, object db, object firephp);
* owpUDF_On_setStatusID(int userID, object db, object firephp);
* owpUDF_On_updatePassword(int userID, object db, object firephp);
* owpUDF_On_updateUser(int userID, object db, object firephp);
* owpUDF_On_userLoginCore(int userID, object db, object firephp);

### Messaging Class Hooks

* owpUDF_On_sendEmailDirect(int userID, object db, object firephp);
* owpUDF_On_sendEmailViaSMTP(int userID, object db, object firephp);

#### Exception Codes

All of the above methods when returning anything other than false will throw and exception with the contents of the return as the exception message. (string).

* 10 - owpUser method exception
* 20 - InvalidArgumentException
* 30 - User Class Hook exception

### Requirements

    "require": {
        "php": ">=5.6.0",
        "ext-imap": "*",
        "vlucas/phpdotenv": "^2.4",
        "guzzlehttp/guzzle": "~6.0",
        "firephp/firephp-core": "^0.4.0",
        "openwebpresence/owp-users": "^1.2",
        "jv2222/ezsql": "dev-master",
        "phpmailer/phpmailer": "^5.2",
        "squeaky-minds/squeaky-minds-php-helper": "^1.1.1",
        "hautelook/phpass": "^1.1"
    }

### Installation by Composer

    "require": {
        "openwebpresence/owp-framework": "~1.0"
    }

Or

	$ composer require openwebpresence/owp-framework