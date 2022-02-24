<?php

namespace Tests\Traits;


use Tests\TestCase;
use App\Libraries\H5P\Packages\ImagePair;
use Cerpus\CoreClient\DataObjects\BehaviorSettingsDataObject;

class ImagePairTraitTest extends TestCase
{
    public function testEnableAndDisableRetry()
    {
        $imagePair = app(ImagePair::class, ['packageStructure' => $this->getImagePairStructure()]);

        $settings = json_decode($imagePair->applyBehaviorSettings(BehaviorSettingsDataObject::create(['enableRetry' => true])), true);
        $this->assertArrayHasKey('behaviour', $settings);
        $this->assertTrue($settings['behaviour']);

        $settings = json_decode($imagePair->applyBehaviorSettings(BehaviorSettingsDataObject::create(['enableRetry' => false])), true);
        $this->assertArrayHasKey('behaviour', $settings);
        $this->assertFalse($settings['behaviour']);
    }

    public function getImagePairStructure()
    {
        return '{"taskDescription":"Drag images from the left to match them with corresponding images on the right","cards":[{},{}],"behaviour":false,"l10n":{"checkAnswer":"Check","tryAgain":"Retry","showSolution":"Show Solution","score":"You got @score of @total points"}}';
    }
}
