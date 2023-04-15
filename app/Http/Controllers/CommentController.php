<?php

namespace App\Http\Controllers;

use App\Helpers\CommentHelper;
use App\Http\Requests\CommentRequest;
use App\Mail\NewCommentMailable;
use App\Models\Comment;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function all(Request $request, int $reportId): Collection|array
    {
        $offset = $request->query('offset');
        $limit = $request->query('limit');
        $comments = Comment::all()->where(
            'report_id',
            $reportId
        )->skip($offset)->take($limit);

        return $comments->count() !== 0 ? $comments : [];
    }

    public function create(CommentRequest $request)
    {
        $validatedDate = $request->validated();
        $validatedDate['date'] = CommentHelper::transformCommentDateField(
            $validatedDate['date']
        );
        $comment = new Comment($validatedDate);
        $creationSuccess = $comment->save();

        if ($creationSuccess) {
            $report = Report::getReportById($comment->report_id);
            $reportCreator = User::getUserById($report->creator_id);
            $reportConference = Conference::getConferenceById(
                $report->conference_id
            );
            Mail::to($reportCreator->email)->queue(
                new NewCommentMailable(
                    $reportConference->title,
                    $reportConference->id,
                    "{$comment->firstname} {$comment->lastname}",
                    $report->topic,
                    $report->id
                )
            );
        }

        return $comment->id;
    }

    public function edit(CommentRequest $request)
    {
        $validatedDate = $request->validated();
        $validatedDate['date'] = CommentHelper::transformCommentDateField(
            $validatedDate['date']
        );
        Comment::getCommentById($request->get('id'))->update(
            $validatedDate
        );
    }
}
