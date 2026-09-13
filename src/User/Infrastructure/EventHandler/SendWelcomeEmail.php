<?php

namespace App\User\Infrastructure\EventHandler;

use App\User\Domain\Event\UserWasRegistered;
use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\Attribute\EventHandler;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

final readonly class SendWelcomeEmail
{
    // Inject Symfony's infrastructure here, NOT in the Aggregate
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire('%app.frontend_url%')]
        private string $frontendUrl
    ) {}

    // This attribute tells Ecotone to automatically trigger this method
    // whenever a UserWasRegistered event is published anywhere in the app.
    /**
     * @throws TransportExceptionInterface
     */
    #[Asynchronous('async_email_queue')]
    #[EventHandler(endpointId: 'send_welcome_email_endpoint')]
    public function handle(UserWasRegistered $event): void
    {
        // 1. Generate an absolute URL pointing to our new Controller route
        $verificationUrl = sprintf(
            '%s/verify-email?userId=%s&token=%s',
            rtrim($this->frontendUrl, '/'),
            $event->userId,
            $event->verificationToken
        );

        $email = (new TemplatedEmail())
            ->from('noreply@yourdomain.com')
            ->to($event->email)
            ->subject('Please verify your account')
            ->htmlTemplate('user/verify_account.html.twig') // 3. Point to the template
            ->context([
                // 4. Pass the variables Twig needs
                'verificationUrl' => $verificationUrl
            ]);

        $this->mailer->send($email);
    }
}
