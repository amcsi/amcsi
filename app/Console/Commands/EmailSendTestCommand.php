<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;

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
     */
    public function handle()
    {
        if (!config('mail.from.address')) {
            throw new \LogicException('You must have the MAIL_FROM_ADDRESS env var set.');
        }

        \Mail::send([], [], function (Message $message) {
            /** @noinspection PhpParamsInspection */
            $message
                ->to($this->option('to'))
                ->embedData([
                    'personalizations' => [
                        [
                            'dynamic_template_data' => [
                                'title' => 'Subject',
                                'name'  => 's-ichikawa',
                            ],
                        ],
                    ],
                    'asm' => [
                        'group_id' => (int) config('services.sendgrid.unsubscribe_group_id')
                    ],
                    'template_id' => config('services.sendgrid.template_id'),
                    'dynamic_template_data' => [
                        'titleText' => 'Dynamic Title Text!',
                    ],
                ], SendgridTransport::SMTP_API_NAME);
        });
    }
}
