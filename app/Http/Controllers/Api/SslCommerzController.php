<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
class SslCommerzController extends Controller
{
    protected function apiUrl()
    {
        return config('sslcommerz.sandbox') ? config('sslcommerz.sandbox_api') : config('sslcommerz.live_api');
    }

    public function createPayment(Request $req)
    {
        $req->validate(['amount' => 'required|numeric']);

        $tran_id = 'TRX' . time() . rand(1000, 9999);
        $order = Order::create([
            'tran_id' => $tran_id,
            'amount' => $req->amount,
            'currency' => $req->currency ?? 'BDT',
            'status' => 'pending'
        ]);

        $post_data = [
            'store_id' => config('sslcommerz.store_id'),
            'store_passwd' => config('sslcommerz.store_password'),
            'total_amount' => $order->amount,
            'currency' => $order->currency,
            'tran_id' => $order->tran_id,
            'success_url' => secure_url('/sslcommerz/success'),  // HTTPS
            'fail_url'    => secure_url('/sslcommerz/fail'),     // HTTPS
            'cancel_url'  => secure_url('/sslcommerz/cancel'),   // HTTPS
            'ipn_url'     => secure_url('/sslcommerz/ipn'),      // HTTPS

            // Customer info
            'cus_name'    => $req->cus_name ?? 'Customer',
            'cus_email'   => $req->cus_email ?? 'noemail@example.com',
            'cus_add1'    => $req->cus_add1 ?? 'House 1, Street 1',
            'cus_city'    => $req->cus_city ?? 'Dhaka',
            'cus_postcode' => $req->cus_postcode ?? '1207',
            'cus_country' => $req->cus_country ?? 'BD',
            'cus_phone'   => $req->cus_phone ?? '01700000000',
            'shipping_method' => 'NO',
            'product_name' => $req->product_name ?? 'Online Course',
            'product_category' => $req->product_category ?? 'digital',
            'product_profile' => $req->product_profile ?? 'digital',
        ];

        $client = new Client(['verify' => false]);
        $res = $client->request('POST', $this->apiUrl(), [
            'form_params' => $post_data,
            'timeout' => 30,
        ]);

        $json = json_decode((string)$res->getBody(), true);
        if (isset($json['GatewayPageURL'])) {
            return response()->json(['gateway_url' => $json['GatewayPageURL'],'tran_id' => $tran_id]);
        }

        return response()->json(['error' => 'unable to create session', 'raw' => $json], 500);
    }
    // Validate payment
    public function validatePayment(Request $request)
    {
        $request->validate(['tran_id' => 'required|string']);

        $order = Order::where('tran_id', $request->tran_id)->first();
        if (!$order) {
            return response()->json(['status' => 'failed', 'message' => 'Order not found'], 404);
        }

        $client = new Client();
        $response = $client->post($this->apiUrl() . '/order/status', [
            'json' => ['tran_id' => $order->tran_id],
        ]);

        $data = json_decode($response->getBody(), true);

        if (isset($data['status']) && $data['status'] === 'VALID') {
            $order->update(['status' => 'paid']);
            return response()->json(['status' => 'paid', 'order' => $order]);
        }

        return response()->json(['status' => 'failed', 'order' => $order]);
    }

    
    public function success(Request $request)
    {
        $data = $request->all();
        $order = Order::where('tran_id', $data['tran_id'] ?? null)->first();
        if ($order) $order->update(['payload' => $data]);

        $valid = $this->validateTransaction($data['val_id'] ?? null, $data['tran_id'] ?? null);

        if (in_array($valid['status'], ['VALID', 'VALIDATED'])) {
            if ($order) $order->update(['status' => 'paid', 'val_id' => $data['val_id'] ?? null, 'bank_tran_id' => $data['bank_tran_id'] ?? null]);

            // Redirect to Flutter success screen
            return response('<html><body>
                <script>window.location.href="' . $this->frontendSuccessUrl($data) . '";</script>
            </body></html>');
        } else {
            if ($order) $order->update(['status' => 'failed']);
            return response('<html><body><h1>Payment validation failed ❌</h1></body></html>');
        }
    }

    public function fail(Request $request)
    {
        $data = $request->all();
        $tran_id = $data['tran_id'] ?? null;
        $order = Order::where('tran_id', $tran_id)->first();
        if ($order) $order->update(['status' => 'failed', 'payload' => $data]);
        return response('<html><body><h1>Payment Failed ❌</h1></body></html>');
    }

    public function cancel(Request $request)
    {
        $data = $request->all();
        $tran_id = $data['tran_id'] ?? null;
        $order = Order::where('tran_id', $tran_id)->first();
        if ($order) $order->update(['status' => 'cancelled', 'payload' => $data]);
        return response('<html><body><h1>Payment Cancelled 🚫</h1></body></html>');
    }

    public function ipn(Request $request)
    {
        $data = $request->all();
        $tran_id = $data['tran_id'] ?? null;
        $order = Order::where('tran_id', $tran_id)->first();
        if (!$order) return response()->json(['status' => 'FAILED', 'message' => 'Order not found'], 404);

        $valid = $this->validateTransaction($data['val_id'] ?? null, $tran_id);
        $status = in_array($valid['status'], ['VALID', 'VALIDATED']) ? 'paid' : 'failed';
        $order->update(['status' => $status, 'payload' => $data, 'val_id' => $data['val_id'] ?? null, 'bank_tran_id' => $data['bank_tran_id'] ?? null]);
        return response()->json(['status' => 'OK']);
    }

    protected function frontendSuccessUrl($data)
    {
        $params = http_build_query(['tran_id' => $data['tran_id'] ?? '', 'val_id' => $data['val_id'] ?? '']);
        return url('/payment-done?' . $params);
    }

    protected function validateTransaction($val_id = null, $tran_id = null)
    {
        $validator = config('sslcommerz.sandbox') ?
            'https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php' :
            'https://securepay.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php';

        try {
            $client = new Client(['verify' => false]);
            $res = $client->request('GET', $validator, [
                'query' => [
                    'tran_id' => $tran_id,
                    'Store_Id' => config('sslcommerz.store_id'),
                    'Store_Passwd' => config('sslcommerz.store_password'),
                    'v' => 1
                ],
                'timeout' => 20
            ]);
            $txt = (string)$res->getBody();
            return ['status' => strpos($txt, 'VALID') !== false ? 'VALID' : (strpos($txt, 'FAILED') !== false ? 'FAILED' : 'UNKNOWN'), 'raw' => $txt];
        } catch (\Exception $e) {
            return ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }
}