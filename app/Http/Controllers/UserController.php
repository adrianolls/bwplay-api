<?php

namespace App\Http\Controllers;

use App\RPC\Opcodes;
use App\RPC\ReadPacket;
use App\RPC\WritePacket;
use Illuminate\Http\Request;

class UserController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        
    }

    public function rolesRequest(Request $request) {
        $getUserRoles = new WritePacket();
        $getUserRoles->WriteUInt32(-1);
        $getUserRoles->WriteUInt32($request->userid);
        $getUserRoles->Pack(Opcodes::$user['userRoles']);
        $getUserRoles->Send(WritePacket::GAMEDBD_PORT);
        $result = new ReadPacket($getUserRoles);
        $result->ReadPacketInfo();
        $result->ReadInt32(); // ???
        $result->ReadInt32(); // Return Code
        $data['count'] = $result->ReadCUInt32(); // RoleCount
        for ($i = 0; $i < $data['count']; $i++) {
            $data['users'][] = [
                'roleid' => $result->ReadUInt32(),
                'rolename' => $result->ReadString()
            ];
        }
        return response()->json($data, 200);
    }

}
