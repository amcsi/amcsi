<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SendGrid;
use SendGrid\Mail\Mail;

class EmailSendTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'io:email-test {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending an email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws SendGrid\Mail\TypeException
     */
    public function handle()
    {
        if (!config('mail.from.address')) {
            throw new \LogicException('You must have the MAIL_FROM_ADDRESS env var set.');
        }

        $sg = new SendGrid(config('services.sendgrid.api_key'));
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));
        $email->addTo($this->option('to'), 'Test user');
        $email->setAsm((int) config('services.sendgrid.unsubscribe_group_id'));
        $email->setTemplateId(config('services.sendgrid.template_id'));
        $email->addDynamicTemplateDatas([
            'titleText' => 'Dynamic Title Text!',
        ]);
        $emailResponse = $sg->send($email);
        if (($statusCode = $emailResponse->statusCode()) !== 202) {
            throw new \RuntimeException("Could not send email. Status code $statusCode:\n{$emailResponse->body()}");
        }

        echo "Email sent.\n";
    }
}
