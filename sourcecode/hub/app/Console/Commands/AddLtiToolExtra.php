<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function is_string;

class AddLtiToolExtra extends Command
{
    protected $signature = <<<'EOF'
    edlib:add-lti-tool-extra
    {parent : ID or slug of the parent tool}
    {name : The name of the extra}
    {url : The launch URL for the extra}
    {--admin : The extra is for admins only}
    {--slug= : An optional slug for the extra}
    EOF;

    public function handle(): void
    {
        $tool = LtiTool::where('id', $this->argument('parent'))
            ->orWhere('slug', $this->argument('parent'))
            ->firstOrFail();

        DB::transaction(function () use ($tool) {
            $slug = $this->option('slug');
            $extra = new LtiToolExtra();
            $extra->name = $this->argument('name');
            $extra->lti_launch_url = $this->argument('url');
            $extra->admin = $this->option('admin');
            if (is_string($slug)) {
                $extra->slug = $slug;
            }
            $extra->lti_tool_id = $tool->id;
            $extra->save();
        });
    }
}
