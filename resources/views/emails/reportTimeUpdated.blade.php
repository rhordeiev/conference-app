<div>
    Greetings! Conference <b>{{$conferenceTitle}}</b>
    (<a href="{{env('MAIL_APP_URL') . "/conference/{$conferenceId}"}}">
        {{env('MAIL_APP_URL') . "/conference/{$conferenceId}"}}
    </a>)
    member <b>{{$username}}</b> with the report which topic is <b>{{$reportTopic}}</b>
    (<a href="{{env('MAIL_APP_URL') . "/report/{$reportId}"}}">
        {{env('MAIL_APP_URL') . "/report/{$reportId}"}}
    </a>)
    has changed their report time.
    <br>
    <br>
    Report time: <b>{{$reportTime}}</b>
</div>
