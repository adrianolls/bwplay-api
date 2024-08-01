<?php

namespace App\RPC;

class Opcodes {

    public static $game = [
        'broadcast' => 0x78,
        'onlines' => 0x160,
        'email' => 0x1076,
        'GMAttr' => 0x179,
        'GetGMAttr' => 0x178,
        'SetGMAttr' => 0x179,
    ];
    public static $user = [
        'userRoles' => 0xD49,
        'removeLock' => 0x310,
        'onlineStatus' => 0x1A4,
    ];
    public static $role = [
        'getRole' => 0x1F43,
        'getFaction' => 0x11FE,
        'getUserFaction' => 0x11FF,
        'putRole' => 0x1F42,
        'renameRole' => 0xD4C,
        'getRoleId' => 0xBD9,
        'getRoleDetail' => 0xbbd,
    ];

}
