<?php

declare(strict_types=1);

namespace Unit\Models;

use Engelsystem\Models\Question;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Str;

class QuestionTest extends TestCase
{
    use HasDatabase;

    /**
     * @var User
     */
    private $user1;

    /**
     * @var User
     */
    private $user2;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->user1 = User::create(
            [
                'name'     => 'user1',
                'password' => '',
                'email'    => 'user1@example.com',
                'api_key'  => '',
            ]
        );

        $this->user2 = User::create(
            [
                'name'     => 'user2',
                'password' => '',
                'email'    => 'user2@example.com',
                'api_key'  => '',
            ]
        );
    }

    /**
     * @covers \Engelsystem\Models\Question::answerer
     */
    public function testAnswerer(): void
    {
        $question = $this->createQuestion($this->user1, $this->user2);
        $loadedQuestion = Question::find($question->id);

        $this->assertSame($this->user1->id, $loadedQuestion->user->id);
        $this->assertSame($this->user1->id, $loadedQuestion->user_id);
        $this->assertSame($loadedQuestion->text, $loadedQuestion->text);
        $this->assertSame($this->user2->id, $loadedQuestion->answerer->id);
        $this->assertSame($this->user2->id, $loadedQuestion->answerer_id);
        $this->assertSame($loadedQuestion->answer, $loadedQuestion->answer);
    }

    /**
     * @covers \Engelsystem\Models\Question::unanswered
     */
    public function testUnanswered(): void
    {
        $question1 = $this->createQuestion($this->user1);
        $question2 = $this->createQuestion($this->user1);
        // create some answered questions as well
        $this->createQuestion($this->user1, $this->user2);
        $this->createQuestion($this->user1, $this->user2);

        $unAnsweredQuestionIds = Question::unanswered()->pluck('id')->toArray();
        $this->assertCount(2, $unAnsweredQuestionIds);
        $this->assertContains($question1->id, $unAnsweredQuestionIds);
        $this->assertContains($question2->id, $unAnsweredQuestionIds);
    }

    /**
     * @covers \Engelsystem\Models\Question::answered
     */
    public function testAnswered(): void
    {
        $question1 = $this->createQuestion($this->user1, $this->user2);
        $question2 = $this->createQuestion($this->user1, $this->user2);
        // create some unanswered questions as well
        $this->createQuestion($this->user1);
        $this->createQuestion($this->user1);

        $answeredQuestionIds = Question::answered()->pluck('id')->toArray();
        $this->assertCount(2, $answeredQuestionIds);
        $this->assertContains($question1->id, $answeredQuestionIds);
        $this->assertContains($question2->id, $answeredQuestionIds);
    }

    /**
     * @param User      $user
     * @param User|null $answerer
     * @return Question
     */
    private function createQuestion(User $user, ?User $answerer = null): Question
    {
        $data = [
            'user_id' => $user->id,
            'text'    => Str::random(),
        ];

        if ($answerer !== null) {
            $data += [
                'answerer_id' => $answerer->id,
                'answer'      => Str::random(),
            ];
        }

        return Question::create($data);
    }
}
