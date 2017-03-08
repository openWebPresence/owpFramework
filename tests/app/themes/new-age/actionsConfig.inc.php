<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   PHPUnit actionsConfig
 * @author    Brian Tafoya <btafoya@briantafoya.com>
 * @version   1.0
 * @license   MIT
 * @license   https://opensource.org/licenses/MIT The MIT License
 * @category  OpenWebPresence_Support_Library
 * @link      http://openwebpresence.com OpenWebPresence

 *
 * Copyright (c) 2017, Brian Tafoya
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

$defaultAction = [
    "none" => "home",
    "isUser" => "myAccount",
    "isAdmin" => "myAccount"
];

$actionsConfig = [
    "404" => [
        "view" => [
            "app/themes/new-age/view/common/header.inc.php",
            "app/themes/new-age/view/common/nav.inc.php",
            "app/themes/new-age/view/pages/404.inc.php",
            "app/themes/new-age/view/common/footer.inc.php"
        ],
        "mod" => [
        ],
        "js" => [
        ],
        "css" => [
        ],
        "permissions" => [
            "isUser" => 0,
            "isAdmin" => 0,
        ]
    ],
    "ajax" => [
        "view" => [
        ],
        "mod" => [
            "app/themes/new-age/lib/OwpAjaxUdf.inc.php"
        ],
        "js" => [
        ],
        "css" => [
        ],
        "permissions" => [
            "isUser" => 0,
            "isAdmin" => 0,
        ]
    ],
    "cssAssets" => [
        "view" => [
        ],
        "mod" => [
            "app/themes/new-age/lib/OwpcssAssets.inc.php"
        ],
        "js" => [
        ],
        "css" => [
        ],
        "permissions" => [
            "isUser" => 0,
            "isAdmin" => 0,
        ]
    ],
    "home" => [
        "view" => [
            "app/themes/new-age/view/common/header.inc.php",
            "app/themes/new-age/view/common/nav.inc.php",
            "app/themes/new-age/view/pages/home.inc.php",
            "app/themes/new-age/view/common/footer.inc.php"
        ],
        "mod" => [
            "app/themes/new-age/mod/OwpHome.inc.php"
        ],
        "js" => [

        ],
        "css" => [

        ],
        "permissions" => [
            "isUser" => 0,
            "isAdmin" => 0,
        ]
    ],
    "jsAssets" => [
        "view" => [
        ],
        "mod" => [
            "app/themes/new-age/lib/OwpjsAssets.inc.php"
        ],
        "js" => [
        ],
        "css" => [
        ],
        "permissions" => [
            "isUser" => 0,
            "isAdmin" => 0,
        ]
    ],
    "myAccount" => [
        "view" => [
            "app/themes/new-age/view/pages/myAccount.inc.php"
        ],
        "mod" => [
            "app/themes/new-age/mod/myAccount.inc.php"
        ],
        "js" => [
            "app/themes/new-age/view/pages/myAccount/myAccount.js"
        ],
        "css" => [
            "app/themes/new-age/view/pages/myAccount/myAccount.css"
        ],
        "permissions" => [
            "isUser" => 1,
            "isAdmin" => 0,
        ]
    ]
];
