<?php

namespace App\Brevo;

use Brevo\Brevo;
use Psr\Log\LoggerInterface;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;

class BrevoService
{
    private Brevo $client;

    public function __construct(
        private LoggerInterface $logger
    ) {
        $this->client = new Brevo(
            apiKey: $_ENV['BREVO_API_KEY']
        );
    }

    public function sendEmailByTemplateId(
        string $email,
        int $id
    ): bool {
        try {
            $response = $this->client->transactionalEmails->sendTransacEmail(
                new SendTransacEmailRequest([
                    'to' => [
                        new SendTransacEmailRequestToItem([
                            'email' => $email,
                        ]),
                    ],
                    'templateId' => $id,
                ])
            );

            $this->logger->info('[Brevo] Email Brevo envoyé', [
                'email' => $email,
                'template_id' => $id,
                'response' => $response,
            ]);

            return true;

        } catch (\Brevo\Exceptions\BrevoApiException $e) {
            $this->logger->error('[Brevo] Erreur API Brevo', [
                'email' => $email,
                'template_id' => $id,
                'status_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => $e->getBody(),
            ]);

            return false;
        }
    }
}