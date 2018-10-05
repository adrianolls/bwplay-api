<?php

namespace App\Http\Controllers;

use App\RPC\Opcodes;
use App\RPC\ReadPacket;
use App\RPC\WritePacket;
use App\RPC\Structs\GRoleInventory;
use Illuminate\Http\Request;

class GameController extends Controller {
    // protected $request;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request) {
        //$this->request = $request;
    }

    public function broadcastRequest(Request $request) {
        $data = [
            'sender' => ($request->has('roleid') ? $request->input('roleid') : 0),
            'message' => $request->input('message'),
            'channel' => intval($request->input('channel'))
        ];
        self::broadcast($data);
        return response("", 200);
    }

    public static function broadcast($data) {
        $broadcastPacket = new WritePacket();
        $broadcastPacket->WriteUByte($data['channel']);
        $broadcastPacket->WriteUByte(0);
        $broadcastPacket->WriteUInt32($data['sender']);
        $broadcastPacket->WriteUString($data['message']);
        $broadcastPacket->WriteOctets("");
        $broadcastPacket->Pack(Opcodes::$game['broadcast']);
        $broadcastPacket->Send(WritePacket::GPROVIDER_PORT);
        return $broadcastPacket;
    }

    public function onlineListRequest() {
        $users = [];
        $handler = -1;
        $count = 0;
        do {
            try {
                $onlinesPacket = new WritePacket();
                $onlinesPacket->WriteInt32(32); // GM ROLEID
                $onlinesPacket->WriteInt32(1); // Localsid
                $onlinesPacket->WriteUInt32($handler); // Handler
                $onlinesPacket->WriteOctets(1); // Cond
                $onlinesPacket->Pack(Opcodes::$game['onlines']);
                $onlinesPacket->Send(WritePacket::GDELIVERYD_PORT);
                $onlinesResponse = new ReadPacket($onlinesPacket);
                $info = $onlinesResponse->ReadPacketInfo();
                $data = [
                    'packet' => $info,
                    'retcode' => $onlinesResponse->ReadUInt32(),
                    'gmroleid' => $onlinesResponse->ReadInt32(),
                    'localsid' => $onlinesResponse->ReadInt32(),
                    'handler' => $onlinesResponse->ReadUInt32(),
                    'counter' => $onlinesResponse->ReadCUInt32(),
                ];
                for ($i = 0; $i < $data['counter']; $i++) {
                    $user = [
                        'userid' => $onlinesResponse->swap_endian($onlinesResponse->ReadUInt32()),
                        'roleid' => $onlinesResponse->ReadUInt32(),
                        'linkid' => $onlinesResponse->ReadUInt32(),
                        'localsid' => $onlinesResponse->ReadUInt32(),
                        'gsid' => $onlinesResponse->ReadUInt32(),
                        'status' => $onlinesResponse->ReadByte(),
                        'name' => $onlinesResponse->ReadString()
                    ];
                    $users[] = $user;
                }
                $handler = $data['handler'];
                $count += $data['counter'];
            } catch (\ErrorException $e) {
                
            }
        } while ($handler !== 4294967295);
        $data['users'] = $users;
        $data['counter'] = $count;
        return response()->json($data, 200);
    }

    public function emailRequest(Request $request) {
        $item = new GRoleInventory();
        $item->pos = 0;
        $item->id = ($request->has('itemid') ? $request->itemid : 0);
        $item->count = ($request->has('stack') ? $request->stack : 0);
        $item->max_count = ($request->has('max_stack') ? $request->max_stack : 0);
        $item->data = ($request->has('data') ? $request->data : 0);
        $item->proctype = ($request->has('proctype') ? $request->proctype : 0);
        $item->expire_date = ($request->has('expire_date') ? $request->expire_date : 0);
        $item->guid1 = ($request->has('guid1') ? $request->guid1 : 0);
        $item->guid2 = ($request->has('guid2') ? $request->guid2 : 0);
        $item->mask = ($request->has('mask') ? $request->mask : 0);
        $data = [
            'receiver' => $request->roleid,
            'title' => $request->has('title') ? $request->title : "",
            'context' => $request->has('context') ? $request->context : "",
            'attach_item' => $item,
            'attach_money' => ($request->has($request->money)) ? $request->money : 0,
        ];
        $this->email($data);
        return response("", 200);
    }

    public function email($data) {
        $email = new WritePacket();
        $email->WriteUInt32(1);
        $email->WriteUInt32(32);
        $email->WriteUByte(3);
        $email->WriteUInt32($data['receiver']);
        $email->WriteUString($data['title']);
        $email->WriteUString($data['context']);
        // GROLEINVENTORY
        $email->WriteUInt32($data['attach_item']->id);
        $email->WriteUInt32($data['attach_item']->pos);
        $email->WriteUInt32($data['attach_item']->count);
        $email->WriteUInt32($data['attach_item']->max_count);
        $email->WriteOctets($data['attach_item']->data);
        $email->WriteUInt32($data['attach_item']->proctype);
        $email->WriteUInt32($data['attach_item']->expire_date);
        $email->WriteUInt32($data['attach_item']->guid1);
        $email->WriteUInt32($data['attach_item']->guid2);
        $email->WriteUInt32($data['attach_item']->mask);
        // END GROLEINVENTORY
        $email->WriteUInt32($data['attach_money']);
        $email->Pack(Opcodes::$game['email']);
        $email->Send(WritePacket::GDELIVERYD_PORT);
    }

}
