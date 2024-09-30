<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
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

        // Chamar o serviço de autorização externa
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://util.devi.tools/api/v2/authorize');
        $authorization = json_decode($response->getBody()->getContents());

        if ($authorization->data->authorization === false) {
            return response()->json(['error' => 'Autorização negada'], 403);
        }

        // Transação atômica para garantir consistência
        DB::transaction(function () use ($payer, $request) {
            $payer->saldo -= $request->value;
            $payer->save();

            $payee = User::find($request->payee);
            $payee->saldo += $request->value;
            $payee->save();
        });

        // Tentar enviar a notificação
        $notificationResult = $this->sendNotification($request->payee, $request->value);

        // Retornar o resultado da transferência e da notificação
        return response()->json([
            'status' => 'Transferência realizada com sucesso!',
            'notification' => $notificationResult['message'],
        ]);
    }



    private function sendNotification($payeeId, $value)
    {
        $client = new \GuzzleHttp\Client();
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

        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => 'Transferência realizada, mas houve um erro ao enviar a notificação: ' . $e->getMessage(),
            ];
        }
    }

}
