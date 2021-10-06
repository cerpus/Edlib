<p>{!! trans('emails/collaboration-invite.you-have-been-invited', ['inviterName' => $mailData->inviterName, 'originSystemName' => $mailData->originSystemName, 'contentTitle' => $mailData->contentTitle]) !!}</p>

@if(!empty($mailData->originSystemName))
    <p>{!! trans('emails/collaboration-invite.please-log-in', ['originSystemName' => $mailData->originSystemName, 'loginUrl' => $mailData->loginUrl]) !!}</p>
@endif

<p>{!! trans('emails/collaboration-invite.you-can-also-use',['loginUrl' => $mailData->loginUrl]) !!}</p>

<p>{!! trans('emails/collaboration-invite.regards') !!}</p>
@if(empty($mailData->originalSystemName))
<p>{!! trans('emails/collaboration-invite.the-edlib-team') !!}</p>
@else
<p>{!! trans('emails/collaboration-invite.the-xxx-edlib-teams', ['originSystemName' => $mailData->originSystemName]) !!}</p>
@endif

