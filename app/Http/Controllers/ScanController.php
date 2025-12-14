<?php
/*
Copyright © Magd Almuntaser, OneXGen Technology. All rights reserved.
Project: MPWA Whatsapp Gateway | Multi Device
Licensed under the CC BY-NC-ND 4.0 License.
For details, visit https://creativecommons.org/licenses/by-nc-nd/4.0/.
*/

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ScanController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function scan(Device $number)
    {

        return view('theme::scan', [
            'number' => $number
        ]);
    }
    
    public function code(Device $number)
    {

        return view('theme::connect-via-code', [
            'number' => $number
        ]);
    }
    
    /**
     * Poll connection status and QR code for HTTP polling mode
     */
    public function pollConnection(Request $request, $device)
    {
        try {
            $deviceModel = Device::where('body', $device)->first();
            
            if (!$deviceModel) {
                return response()->json([
                    'status' => false,
                    'msg' => __('Device not found')
                ], 404);
            }
            
            return response()->json([
                'status' => true,
                'data' => [
                    'qr_code' => $deviceModel->qr_code,
                    'connection_data' => $deviceModel->connection_data ? json_decode($deviceModel->connection_data) : null,
                    'device_status' => $deviceModel->status,
                    'pairing_code' => $deviceModel->pairing_code,
                    'qr_generated_at' => $deviceModel->qr_generated_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Start connection via HTTP (no WebSocket)
     */
    public function startConnection(Request $request)
    {
        try {
            $device = $request->device;
            
            // Build Node.js URL (subdomain or localhost)
            $waUrlServer = rtrim(env('WA_URL_SERVER'), '/');
            $portNode = env('PORT_NODE');
            dd($waUrlServer,$portNode);
            
            if (!empty($portNode) && (strpos($waUrlServer, 'localhost') !== false || strpos($waUrlServer, '127.0.0.1') !== false)) {
                $nodeUrl = $waUrlServer . ':' . $portNode;
            } else {
                $nodeUrl = $waUrlServer;
            }
            
            $response = Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post($nodeUrl . '/start-connection', [
                    'device' => $device
                ]);
                dd($response->json());
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Start connection via pairing code
     */
    public function startConnectionViaCode(Request $request)
    {
        try {
            $device = $request->device;
            
            // Build Node.js URL (subdomain or localhost)
            $waUrlServer = rtrim(env('WA_URL_SERVER'), '/');
            $portNode = env('PORT_NODE');
            
            if (!empty($portNode) && (strpos($waUrlServer, 'localhost') !== false || strpos($waUrlServer, '127.0.0.1') !== false)) {
                $nodeUrl = $waUrlServer . ':' . $portNode;
            } else {
                $nodeUrl = $waUrlServer;
            }
            
            $response = Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post($nodeUrl . '/connect-via-code', [
                    'device' => $device
                ]);
            
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }
}
