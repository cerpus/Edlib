<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\LtiToolEditMode;
use App\Enums\LtiVersion;
use App\Models\LtiTool;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class AddLtiTool extends Command
{
    protected $signature = <<<'EOF'
    edlib:add-lti-tool
    {name : The name of the tool}
    {url : The URL to which LTI Deep Linking requests will be sent}
    {--slug=} The URL slug of the tool
    {--send-name} Send the name of the user to the tool upon launch
    {--send-email} Send the email address of the user to the tool upon launch
    {--edlib-editable} The tool accepts edit requests via an Edlib-specific mechanism
    EOF;

    protected $description = 'Adds an LTI tool to be used for creating content.';

    public function handle(): void
    {
        $tool = new LtiTool();
        $tool->lti_version = LtiVersion::Lti1_1;
        $tool->name = $this->argument('name');
        $tool->creator_launch_url = $this->argument('url');
        $tool->consumer_key = $this->ask('Key');
        $tool->consumer_secret = $this->secret('Secret');
        $tool->send_name = $this->option('send-name');
        $tool->send_email = $this->option('send-email');

        if ($this->option('slug')) {
            $tool->slug = $this->option('slug');
        }

        if ($this->option('edlib-editable')) {
            $tool->edit_mode = LtiToolEditMode::DeepLinkingRequestToContentUrl;
        }

        $tool->save();

        $this->info('The tool has been created. Tool ID:');
        $this->line($tool->id, verbosity: OutputInterface::VERBOSITY_QUIET);
    }
}
