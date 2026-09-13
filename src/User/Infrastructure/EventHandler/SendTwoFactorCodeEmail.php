<?php

namespace App\User\Infrastructure\EventHandler;

use App\User\Domain\Event\TwoFactorCodeRequested;
use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\Attribute\EventHandler;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

final readonly class SendTwoFactorCodeEmail
{
    public function __construct(private MailerInterface $mailer) {}

    /**
     * @throws TransportExceptionInterface
     */
    #[Asynchronous('async_email_queue')]
    #[EventHandler(endpointId: 'send_2fa_code_email_endpoint')]
    public function handle(TwoFactorCodeRequested $event): void
    {
        $email = (new TemplatedEmail())
            ->from('noreply@yourdomain.com')
            ->to($event->email)
            ->subject('Your 2FA Verification Code')
            ->htmlTemplate('user/2fa_code.html.twig')
            ->context(['code' => $event->code]);

        $this->mailer->send($email);
    }
}
