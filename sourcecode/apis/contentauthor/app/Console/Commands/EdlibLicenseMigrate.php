<?php

namespace App\Console\Commands;

use App\Article;
use App\Events\ResourceSaved;
use App\Game;
use App\H5PContent;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\ResourceDataObject;
use App\Link;
use App\QuestionSet;
use Cerpus\LicenseClient\Contracts\LicenseContract;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class EdlibLicenseMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edlib:license-migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data stored in LicenseService to local database';

    private LicenseContract $licenseContract;
    private ProgressBar $progressBar;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LicenseContract $contract)
    {
        parent::__construct();
        $this->licenseContract = $contract;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Migrating license data');

        $this->progressBar = $this->output->createProgressBar();
        $this->progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:6s% (%message%)');
        $this->progressBar->setBarCharacter('=');
        $this->progressBar->setEmptyBarCharacter(' ');
        $this->progressBar->setProgressCharacter('>');

        $this->migrateLicense(Article::class, 'articles', ResourceDataObject::ARTICLE);
        $this->migrateLicense(QuestionSet::class, 'question_sets', ResourceDataObject::QUESTIONSET);
        $this->migrateLicense(Game::class, 'games', ResourceDataObject::GAME);
        $this->migrateLicense(Link::class, 'links', ResourceDataObject::LINK);
        $this->migrateLicense(H5PContent::class, 'h5p_contents', ResourceDataObject::H5P);

        $this->newLine();

        return 0;
    }

    private function migrateLicense(string $className, string $tableName, string $resourceType): void
    {
        $this->newLine();
        $count = DB::table($tableName)->count('id');

        if ($count > 0) {
            $this->progressBar->setMessage($className);
            $this->progressBar->start($count);
            $this->progressBar->display();

            DB::table($tableName)
                ->select('id')
                ->orderBy('id')
                ->chunk(50, function($coursIds) use ($className, $tableName, $resourceType) {
                    foreach ($coursIds as $item) {
                        $this->progressBar->advance();
                        try {
                            $content = $this->licenseContract->getContent($item->id);
                            if (!empty($content->licenses)) {
                                $licenses = $content->licenses[0];
                                $license = License::toEdLibLicenseString($licenses);
                                if (!empty($license)) {
                                    /** @var Article|Game|H5PContent|Link|QuestionSet $local */
                                    $local = app($className)::find($item->id);
                                    // Replace PRIVATE with EDLL (Edlib License)
                                    $local->license = $license === License::LICENSE_PRIVATE ? License::LICENSE_EDLIB : $license;
                                    $local->save();
                                    if ($local->license !== $license) {
                                        event(new ResourceSaved(
                                            new ResourceDataObject(
                                                $local->id,
                                                $local->title,
                                                ResourceSaved::UPDATE,
                                                $resourceType
                                            ),
                                            $local->getEdlibDataObject()
                                        ));
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $this->progressBar->clear();
                            $this->line('Exception: (' . $e->getCode() . ') ' . $e->getMessage() . '. Table: ' . $tableName . ', id: ' . $item->id);
                            $this->progressBar->display();
                        }
                    }
                });
            $this->progressBar->finish();
        } else {
            $this->line('No data found in table ' . $tableName);
        }
    }
}
