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

        // Set what address and name to send from.
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $subject = 'Test subject';
        // NOTE: setting the subject the following way is ignored if you use SendGrid templates.
        // See addDynamicTemplateDatas() lower down to see what you need to do in case of dynamic templates.
        $email->setSubject($subject);

        /* Always have an unsubscribe group, otherwise clicking on the Unsubscribe link (which is mandatory to place)
           would result in a global unsubscribe, preventing the user from receiving such important emails such as
           "forgot password" emails. https://sendgrid.com/docs/ui/sending-email/recipient-subscription-preferences/ */
        $email->setAsm((int) config('services.sendgrid.unsubscribe_group_id'));

        // Attach an image.
        $contentId = 'contentId';
        $email->addAttachment(
            base64_encode(file_get_contents(__DIR__ . '/EmailSendTestCommand/blue.jpg')),
            'image/jpeg',
            'blue.jpg',
            'inline',
            $contentId
        );

        /* Templates and variables */

        // Set which template to use (unless you want to provide the html yourself).
        $email->setTemplateId(config('services.sendgrid.template_id'));

        // Template placeholder replacements common to all recipients.
        $baseTemplateData = [
            'titleText' => 'Dynamic Title Text!',
            'firstParagraph' => 'This is an example paragraph text.',
            'exampleArray' => [
                [
                    'exampleParagraph' => 'Array paragraph 1',
                ],
                [
                    'exampleParagraph' => 'Array paragraph 2',
                ],
            ],
            // This is how you set the subject for SendGrid template emails if you want a dynamic subject.
            // Also make sure that the template on the SendGrid interface has the subject: {{subject}}
            'subject' => $subject,
            // An example of referencing an attached image with cid.
            // Though it's recommended to just link to an external image, because it's supported in all email clients.
            'image' => "cid:$contentId",
        ];

        /* Recipients */

        // Add recipients.
        foreach ($this->option('to') as $index => $address) {
            // Assign a name to the user for the sake of this test.
            $name = 'Test User ' . ($index + 1);

            // Merge base template data with recipient-specific template data.
            // Unfortunately I couldn't find any way to just globally set the base template data, so you'll have to
            // repeat them for every recipient.
            $templateData = array_replace($baseTemplateData, ['name' => $name]);

            // Add addressee.
            $email->addTo(
                // e.g. "john.doe@example.com"
                $address,
                // e.g. "John Doe" to result in "John Doe <john.doe@example.com>".
                $name,

                // $templateData is only needed if using dynamic SendGrid templates.
                $templateData,

                // If you are not using per-recipient template data (such as "Dear {{name}}") or you don't mind if
                // all To/Cc recipients of the email "see each other", then you can leave this argument out.
                //
                // Otherwise, by setting this value correctly, different users will get different emails potentially
                // with different content (depending on template data), and _not_ see each other as recipients.
                // You need to make sure this personalizationIndex value is unique to each recipient, starting from 0
                // for the first recipient, and incrementing for each additional recipient. Even if you use a mixture of
                // To and Cc, the indexes must match the order of adding the recipient to the email.
                $index
            );


        }

        /* Send */

        $emailResponse = $sg->send($email);
        if (($statusCode = $emailResponse->statusCode()) !== 202) {
            throw new \RuntimeException("Could not send email. Status code $statusCode:\n{$emailResponse->body()}");
        }

        echo "Email sent.\n";
    }
}
