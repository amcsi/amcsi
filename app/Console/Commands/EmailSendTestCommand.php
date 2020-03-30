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
    protected $signature = 'io:email-test {--to=*}';

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
        foreach ($this->option('to') as $index => $address) {
            $name = 'Test User ' . ($index + 1); // Assign a name to the user.
            $email->addTo($address, $name, ['name' => $name]);
        }
        $email->setAsm((int) config('services.sendgrid.unsubscribe_group_id'));
        $email->setTemplateId(config('services.sendgrid.template_id'));
        $contentId = 'contentId';
        $email->addAttachment(
            base64_encode(file_get_contents(__DIR__ . '/EmailSendTestCommand/blue.jpg')),
            'image/jpeg',
            'blue.jpg',
            'inline',
            $contentId
        );
        $email->addDynamicTemplateDatas([
            'titleText' => 'Dynamic Title Text!',
            'firstParagraph' => 'This is an example paragraph text.',
            // This is how you set the subject for SendGrid template emails if you want a dynamic subject.
            // Also make sure that the template on the SendGrid interface has the subject: {{subject}}
            'subject' => 'Test subject',
            // An example of referencing an attached image with cid.
            // Though it's recommended to just link to an external image, because it's supported in all email clients.
            'image' => "cid:$contentId",
        ]);
        $emailResponse = $sg->send($email);
        if (($statusCode = $emailResponse->statusCode()) !== 202) {
            throw new \RuntimeException("Could not send email. Status code $statusCode:\n{$emailResponse->body()}");
        }

        echo "Email sent.\n";
    }
}
