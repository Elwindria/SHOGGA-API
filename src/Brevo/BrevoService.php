<?php

namespace App\Brevo;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use GuzzleHttp\Client;
use Brevo\Client\Model\SendSmtpEmail;

class BrevoService
{
    private TransactionalEmailsApi $apiInstance;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $_ENV['BREVO_API_KEY']);

        $this->apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );
    }

    public function sendEmailByTemplateId(
        string $email,
        int $id
    ): void {
        $sendSmtpEmail = new SendSmtpEmail([
            'to' => [[
                'email' => $email,
            ]],
            'templateId' => $id,
            'params' => [],
        ]);

        $this->apiInstance->sendTransacEmail($sendSmtpEmail);
    }
}