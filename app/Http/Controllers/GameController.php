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

    public function getServerAttr(Request $request){

        $getattr = new WritePacket();
        $getattr->WriteInt32(-1); // retcode
        $getattr->WriteInt32(32); // gmroleid
        $getattr->WriteUInt32(-1); // localsid
        $getattr->WriteUByte($request->attr); // attribute
        $getattr->Pack(Opcodes::$game['GetGMAttr']);
        $getattr->Send(WritePacket::GDELIVERYD_PORT);
        $getattrRes = new ReadPacket($getattr);
        // echo 'data:' . bin2hex($getattrRes->data) . "\n";

        $info = $getattrRes->ReadPacketInfo();
        $data = $getattrRes->ReadOctets();
        // echo 'hex:' . $data . "\n";
    
        $lastbyte = substr($data, -2); // GET THE TWO LAST BYTES FROM RESPONSE
        // echo 'lastbyte:' . $lastbyte . "\n";
        $byteval = hexdec($lastbyte); // CONVERT THE HEX REPRESENTATION INTO BYTE VALUE
        // echo 'lastbyte:' . $byteval . "\n";
        if($request->type == 1){ // EXP READING ( RETURN FLOAT MOST OF TIMES LIKE 5.0 )
            $doubleValue = (double)$byteval;
            return ($doubleValue / 10);
        }

        if($request->type == 0){ // OTHERS TYPES (RETURN 0 OR 1 MOST OF TIMES)
            return ($byteval);
        }

    }

    public function setServerAttr(Request $request){

        $setattr = new WritePacket();
        $setattr->WriteInt32(-1); // retcode
        $setattr->WriteInt32(32); // gmroleid
        $setattr->WriteUInt32(-1); // localsid
        // echo 'request0:' . bin2hex($setattr->request) . "\n";
        $setattr->WriteUByte($request->attr); // attribute
        // echo 'request1:' . bin2hex($setattr->request) . "\n";

        if($request->type == 1){ // EXP SETTING ( FLOATING VALUE LIKE 4.9)
            $val = doubleval($request->value) * 10;
            // echo 'val:' . $val . "\n";
            $byteString = pack('C', $val);
            $hex_representation = bin2hex($byteString);
            // echo 'hex_representation:' . $hex_representation . "\n";
            $setattr->WriteOctets($hex_representation);

        } else {
            $val = doubleval($request->value); // OTHER SETTING ( USUALLY VALUE ARE 0 OR 1  )
            $byteString = pack('C', $val);
            $hex_representation = bin2hex($byteString);
            $setattr->WriteOctets($hex_representation);
        }
        $setattr->Pack(Opcodes::$game['SetGMAttr']);
        // echo 'request_last:' . bin2hex($setattr->request) . "\n";
        $setattr->Send(WritePacket::GDELIVERYD_PORT);
    }
    public function test(Request $request) {
        $octets = hex2bin($request->octets);
        // echo 'octets from server:' . bin2hex($octets) . "\n";
        $res = new ReadPacket(null, $octets);
        $oct_res = $res->ReadOctets();
        $getattr = new WritePacket();
        $getattr->WriteOctets($oct_res); // retcode
        // echo 'data:' . bin2hex($getattr->request) . " :: ";

        $test = null;
        
        return response()->json($test, 200, ['Content-Type' => 'text/html']);
    }

}
