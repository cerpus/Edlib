<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\Tag;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

/**
 * One-time job to fetch missing H5P content type titles from LTI Tool, create as tags and attach to content versions
 */
class H5PAttachContentTypeTitle extends Command
{
    protected $signature = 'edlib:h5p-attach-content-type-title';

    public function handle(): void
    {
        $this->info('Select the LTI tool to fetch the missing H5P content type data from, only content connected to the selected tool are updated');
        $tools = LtiTool::select(['id', 'name'])->pluck('name', 'id')->toArray();
        if (count($tools) === 0) {
            $this->info('No LTI Tools found');
            return;
        }else if (count($tools) > 1) {
            $selectedTool = $this->choice(
                'Available LTI Tools',
                $tools,
                attempts: 1,
                multiple: false
            );
        } else {
            $selectedTool = array_key_first($tools);
            if (!$this->confirm("Found one LTI Tool named '{$tools[$selectedTool]}', proceed with this?", false)) {;
                return;
            }
        }

        $tool = LtiTool::findorFail($selectedTool);
        $urlParts = parse_url($tool->creator_launch_url);
        if (!is_array($urlParts) or !array_key_exists('scheme', $urlParts) or !array_key_exists('host', $urlParts)) {
            $this->error("Failed to extract scheme and host from LTI Tool launch url: '{$tool->creator_launch_url}'");
            return;
        }

        $resourceCount = ContentVersion::where('lti_tool_id', $selectedTool)->count();
        if ($resourceCount === 0) {
            $this->info('Selected LTI Tool do not have any content versions.');
            return;
        }

        $this->info('Number of content versions connected to the selected tool: <comment>' . $resourceCount . '</comment>');

        // 1. Get the H5P machine names used by the content
        $tags = Tag::select('tags.id', 'tags.name')
            ->where('prefix', '=', 'h5p')
            ->join('content_version_tag', 'content_version_tag.tag_id', '=', 'tags.id')
            ->join('content_versions', 'content_versions.id', '=', 'content_version_tag.content_version_id')
            ->where('content_versions.lti_tool_id', $selectedTool)
            ->distinct()
            ->get()
            ->toArray();

        $this->info('Unique machine names found: <comment>' . count($tags) . '</comment>');

        // 2. Fetch the title for the machine names and create as tag
        $url = "{$urlParts['scheme']}://{$urlParts['host']}/v1/h5p/library/title";
        $this->info("Querying <comment>{$urlParts['host']}</comment> for content type titles");

        try {
            $client = new Client();
            $response = $client->request('POST', $url, [
                RequestOptions::HEADERS => [
                    'X-PSK' => $tool->consumer_key,
                ],
                RequestOptions::JSON => (object)["machineNames" => collect($tags)->pluck('name')->toArray()],
            ]);

            $titles = json_decode($response->getBody()->getContents(), true);
            $this->info('Titles received: <comment>' . count($titles) . '</comment>');
            $totalCount = 0;
            foreach ($tags as $tag) {
                $this->output->write("<info>Processing tag</info> '<comment>{$tag['name']}</comment>'");
                $title = $titles[$tag['name']] ?? $tag['name'];

                $this->output->write("<info>, title</info> '<comment>$title</comment>': ");
                $title_tag = Tag::firstOrCreate([
                    'prefix' => 'h5p_title',
                    'name' => $title,
                ]);

                // 3. Connect the title tags to the content versions
                $count = 0;
                $changeCount = 0;
                ContentVersion::whereIn('id', function ($query) use ($tag) {
                    $query->select('content_version_id')
                        ->from('content_version_tag')
                        ->where('tag_id', '=', $tag['id']);
                })
                ->where('lti_tool_id', '=', $selectedTool)
                ->chunkById(100, function (Collection $contentVersions) use ($title_tag, &$count, &$changeCount) {
                    $count += $contentVersions->count();
                    $this->output->write(".");
                    $contentVersions->each(function (ContentVersion $contentVersion) use ($title_tag, &$changeCount) {
                        if ($contentVersion->tags()->where('id', $title_tag->id)->doesntExist()) {
                            $changeCount ++;
                            $contentVersion->tags()->attach(
                                $title_tag->id,
                                ['verbatim_name' => $title_tag->name]
                            );
                        }
                    });
                });
                $totalCount += $count;
                $this->output->write(" Created new tag for <comment>$changeCount</comment> of total <comment>$count</comment> content versions", newline: true);
            }
            $this->output->write("Total content versions processed: <comment>$totalCount</comment>", newline: true);
        } catch (ClientException $e) {
            $this->error($e->getMessage());
            return;
        }
    }
}
