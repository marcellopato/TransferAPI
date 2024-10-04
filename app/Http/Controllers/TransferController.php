<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * @throws GuzzleException
     */

    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|numeric|min:0.01',
            'payer' => 'required|exists:users,id',
            'payee' => 'required|exists:users,id',
        ]);

        $payer = User::find($request->payer);

        if ($payer->tipo_usuario === 'lojista') {
            return response()->json(['error' => 'Lojistas não podem realizar transferências'], 403);
        }

        if ($payer->saldo < $request->value) {
            return response()->json(['error' => 'Saldo insuficiente'], 400);
        }

        try {
            // Tentar fazer a chamada à API externa de autorização
            $client = new Client();
            $response = $client->get('https://util.devi.tools/api/v2/authorize');
            $authorization = json_decode($response->getBody()->getContents());

            if ($authorization->data->authorization === false) {
                return response()->json(['error' => 'Autorização negada pela API externa.'], 403);
            }

        } catch (RequestException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 403) {
                // Tratar o caso específico de "403 Forbidden"
                return response()->json(['error' => 'Autorização negada pela API externa.'], 403);
            }

            return response()->json(['error' => 'Ocorreu um erro ao tentar autorizar a transferência. Por favor, tente novamente mais tarde.'], 500);
        }

        DB::transaction(function () use ($payer, $request) {
            $payer->saldo -= $request->value;
            $payer->save();

            $payee = User::find($request->payee);
            $payee->saldo += $request->value;
            $payee->save();
        });

        $notificationResult = $this->sendNotification($request->payee, $request->value);

        return response()->json([
            'status' => 'Transferência realizada com sucesso!',
            'notification' => $notificationResult['message'],
        ]);
    }




    private function sendNotification($payeeId, $value)
    {
        $client = new Client();
        $payee = User::find($payeeId);

        try {
            $response = $client->post('https://util.devi.tools/api/v1/notify', [
                'json' => [
                    'email' => $payee->email,
                    'message' => 'Você recebeu uma transferência de R$' . $value,
                ]
            ]);

            return [
                'status' => 'success',
                'message' => 'Notificação enviada com sucesso!'
            ];

        } catch (Exception $e) {
            return [
                'status' => 'fail',
                'message' => 'Transferência realizada, mas houve um erro ao enviar a notificação: ' . $e->getMessage(),
            ];
        }
    }

}
