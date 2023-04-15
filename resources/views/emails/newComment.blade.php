<div>
    Greetings! User <b>{{$username}}</b> has left new comment on report <b>{{$reportTopic}}</b>
    (<a href="{{env('MAIL_APP_URL') . "/report/{$reportId}"}}">
        {{env('MAIL_APP_URL') . "/report/{$reportId}"}}
    </a>)
    of conference <b>{{$conferenceTitle}}</b>
    (<a href="{{env('MAIL_APP_URL') . "/conference/{$conferenceId}"}}">
        {{env('MAIL_APP_URL') . "/conference/{$conferenceId}"}}
    </a>).
</div>
