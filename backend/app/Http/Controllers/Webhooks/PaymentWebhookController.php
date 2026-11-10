<?php

namespace App\Http\Controllers\Webhooks;

use App\Domains\Payments\DTOs\WebhookRequestData;
use App\Domains\Payments\Exceptions\GatewayRequestFailed;
use App\Domains\Payments\Exceptions\InvalidWebhookSignature;
use App\Domains\Payments\Services\WebhookIntake;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * `POST /webhooks/{gateway}`: raw body, no session, no CSRF, rate limited. 200 when stored or
 * already stored, 400 on a bad or missing signature, 503 when the provider's own verification
 * endpoint could not be reached (so the provider retries).
 */
class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, string $gateway, WebhookIntake $intake): JsonResponse
    {
        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            $headers[strtolower($name)] = (string) ($values[0] ?? '');
        }

        try {
            $result = $intake->receive($gateway, new WebhookRequestData($request->getContent(), $headers, $request->query()), $request->ip());
        } catch (InvalidWebhookSignature) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid signature.', 'code' => 'invalid_signature'], 400);
        } catch (GatewayRequestFailed) {
            return new JsonResponse(['success' => false, 'message' => 'Verification unavailable.', 'code' => 'verification_unavailable'], 503);
        }

        return new JsonResponse(['success' => true, 'duplicate' => $result['duplicate']]);
    }
}
