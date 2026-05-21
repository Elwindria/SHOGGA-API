<?php

namespace App\Brevo;

use Brevo\Brevo;
use Psr\Log\LoggerInterface;
use Throwable;
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
            $this->client->transactionalEmails->sendTransacEmail(
                new SendTransacEmailRequest([
                    'to' => [
                        new SendTransacEmailRequestToItem([
                            'email' => $email,
                        ]),
                    ],
                    'templateId' => $id,
                    'params' => [],
                ])
            );

            $this->logger->info('Brevo email envoyé', [
                'email' => $email,
                'template_id' => $id,
            ]);

            return true;

        } catch (Throwable $e) {

            $this->logger->error('Erreur envoi email Brevo', [
                'email' => $email,
                'template_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}