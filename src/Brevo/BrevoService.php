<?php

namespace App\Brevo;

use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;

class BrevoService
{
    private Brevo $client;

    public function __construct()
    {
        $this->client = new Brevo(
            apiKey: $_ENV['BREVO_API_KEY']
        );
    }

    public function sendEmailByTemplateId(
        string $email,
        int $id
    ): void {
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
    }
}