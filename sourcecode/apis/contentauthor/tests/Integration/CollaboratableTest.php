<?php

namespace Tests\Integration;

use App\Collaborator;
use App\Game;
use App\Mail\AddedAsCollaboratorMail;
use App\QuestionSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CollaboratableTest extends TestCase
{
    use RefreshDatabase;

    public function test_collaboratable_model_setup_for_question_set()
    {
        $questionSet = QuestionSet::factory()->create();

        $this->assertCount(0, $questionSet->collaborators);

        $collaborator = Collaborator::factory()->make();

        $questionSet->collaborators()->save($collaborator);

        $questionSet = $questionSet->fresh();

        $this->assertCount(1, $questionSet->collaborators);

        $collaborator = Collaborator::factory()->make();
        $questionSet->collaborators()->save($collaborator);
        $questionSet = $questionSet->fresh();

        $this->assertCount(2, $questionSet->collaborators);

        $questionSet->collaborators()->where('email', $collaborator->email)->delete();

        $questionSet = $questionSet->fresh();
        $this->assertCount(1, $questionSet->collaborators);
    }

    public function test_collaboratable_model_setup_for_game()
    {
        $game = Game::factory()->create();

        $this->assertCount(0, $game->collaborators);

        $collaborator = Collaborator::factory()->make();

        $game->collaborators()->save($collaborator);

        $game = $game->fresh();

        $this->assertCount(1, $game->collaborators);

        $collaborator = Collaborator::factory()->make();
        $game->collaborators()->save($collaborator);
        $game = $game->fresh();

        $this->assertCount(2, $game->collaborators);

        $game->collaborators()->where('email', $collaborator->email)->delete();

        $game = $game->fresh();
        $this->assertCount(1, $game->collaborators);
    }

    public function test_you_can_set_collaborators_from_an_array_of_email_addresses()
    {
        $questionSet = QuestionSet::factory()->create();
        $this->assertCount(0, $questionSet->collaborators);
        $collaborator = Collaborator::factory()->make(['email' => 'collaborator1@example.com']);
        $questionSet->collaborators()->save($collaborator);
        $questionSet = $questionSet->fresh();
        $this->assertCount(1, $questionSet->collaborators);
        $questionSet2 = QuestionSet::factory()->create();
        $collaborator2 = Collaborator::factory()->make(['email' => 'collaborator1@example.com']);
        $questionSet2->collaborators()->save($collaborator2);


        $questionSet->setCollaborators(['a@b.com', 'c@d.com']);
        $questionSet = $questionSet->fresh();
        $this->assertCount(2, $questionSet->collaborators);
        $this->assertCount(0, $questionSet->collaborators()->where('email', $collaborator->email)->get());
        $this->assertCount(1, $questionSet->collaborators()->where('email', 'a@b.com')->get());
        $this->assertCount(1, $questionSet->collaborators()->where('email', 'c@d.com')->get());

        // Make sure other content is not affected
        $questionSet2 = $questionSet2->fresh();
        $this->assertCount(1, $questionSet2->collaborators);
        $this->assertCount(1, $questionSet2->collaborators()->where('email', 'collaborator1@example.com')->get());
    }

    public function test_you_can_get_a_list_of_new_collaborators()
    {
        $questionSet = QuestionSet::factory()->create();
        $collaborator = Collaborator::factory()->make(['email' => 'a@b.com']);
        $questionSet->collaborators()->save($collaborator);
        $this->assertCount(0, $questionSet->newCollaborators());
        $this->assertFalse(in_array('a@b.com', $questionSet->newCollaborators()));

        $questionSet->setCollaborators(['a@b.com', 'c@d.com', 'e@f.com']);
        $this->assertTrue(is_array($questionSet->newCollaborators()));
        $this->assertCount(2, $questionSet->newCollaborators());
        $this->assertTrue(in_array('c@d.com', $questionSet->newCollaborators()));
        $this->assertTrue(in_array('e@f.com', $questionSet->newCollaborators()));
        $this->assertFalse(in_array('a@b.com', $questionSet->newCollaborators()));
    }

    public function test_only_valid_emails_are_added()
    {
        $questionSet = QuestionSet::factory()->create();
        $collaborator = Collaborator::factory()->make(['email' => 'a@b.com']);
        $questionSet->collaborators()->save($collaborator);

        $questionSet->setCollaborators(['a@b.com', 'c(at)d.com']);
        $questionSet = $questionSet->fresh();
        $this->assertCount(0, $questionSet->newCollaborators());
        $this->assertCount(0, $questionSet->collaborators()->where('email', 'c(at)d.com')->get());
        $this->assertCount(1, $questionSet->collaborators()->where('email', 'a@b.com')->get());
    }

    public function test_you_can_send_emails_to_new_collaborators()
    {
        Mail::fake();

        $this->withSession([
            'id' => 1,
            'name' => 'User 1',
            'email' => 'sender@example.com',
            'originalSystem' => 'EdStep'
        ]);
        $questionSet = QuestionSet::factory()->create();
        $collaborator = Collaborator::factory()->make(['email' => 'a@b.com']);
        $questionSet->collaborators()->save($collaborator);

        $questionSet->setCollaborators(['a@b.com', 'c@d.com'])->notifyNewCollaborators();

        Mail::assertQueued(AddedAsCollaboratorMail::class, function ($mail) {
            return $mail->hasTo('c@d.com');
        });
        // TODO: Test that a mail is sent to all new collaborators, there are some nice assertions in Laravel 5.6 to do this.
    }

    public function test_no_mail_is_sent_when_no_new_collaboratos_has_been_added()
    {
        Mail::fake();

        $this->withSession([
            'id' => 1,
            'name' => 'User 1',
            'email' => 'sender@example.com',
            'originalSystem' => 'EdStep'
        ]);
        $questionSet = QuestionSet::factory()->create();
        $collaborator = Collaborator::factory()->make(['email' => 'a@b.com']);
        $questionSet->collaborators()->save($collaborator);
        $collaborator = Collaborator::factory()->make(['email' => 'c@d.com']);
        $questionSet->collaborators()->save($collaborator);

        $questionSet->setCollaborators(['a@b.com', 'c@d.com'])->notifyNewCollaborators();

        Mail::assertNotSent(AddedAsCollaboratorMail::class);
    }

    public function test_can_get_a_list_of_collaborators_emails()
    {
        $questionSet = QuestionSet::factory()->create();
        $collaborator = Collaborator::factory()->make(['email' => 'a@b.com']);
        $questionSet->collaborators()->save($collaborator);

        $questionSet = $questionSet->fresh();
        $this->assertEquals('a@b.com', $questionSet->getCollaboratorEmails());

        $collaborator = Collaborator::factory()->make(['email' => 'c@d.com']);
        $questionSet->collaborators()->save($collaborator);

        $questionSet = $questionSet->fresh();
        $this->assertEquals('a@b.com,c@d.com', $questionSet->getCollaboratorEmails());

        $questionSet->setCollaborators(['1@b.com', '2@d.com']);
        $questionSet = $questionSet->fresh();
        $this->assertEquals('1@b.com,2@d.com', $questionSet->getCollaboratorEmails());
    }

    public function test_feature_toggle_off()
    {
        Mail::fake();
        config(['feature.collaboration' => false]);
        $questionSet = QuestionSet::factory()->create();
        $questionSet->setCollaborators(['a@b.com'])->notifyNewCollaborators(); // Typical invocation of this feature

        // No emails are added and no mail is sent.
        $this->assertEmpty($questionSet->newCollaborators());
        Mail::assertNotSent(AddedAsCollaboratorMail::class);
    }

    public function test_feature_toggle_on()
    {
        Mail::fake();
        config(['feature.collaboration' => true]);
        $questionSet2 = QuestionSet::factory()->create();
        $questionSet2->setCollaborators(['a@b.com'])->notifyNewCollaborators(); // Typical invocation of this feature

        // Email added and mail is sent
        $this->assertCount(1, $questionSet2->newCollaborators());
        Mail::assertQueued(AddedAsCollaboratorMail::class, function ($mail) {
            return $mail->hasTo('a@b.com');
        });
    }
}
