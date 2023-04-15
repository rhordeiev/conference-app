<?php

namespace App\Nova;

use App\Helpers\ZoomMeetingHelper;
use App\Http\Controllers\ZoomController;
use App\Rules\DurationIsLessThanHourRule;
use App\Rules\EndTimeLessThanMaxRule;
use App\Rules\ReportTimeIsAvailableRule;
use App\Rules\StartTimeMoreThanMinRule;
use Ganyicz\NovaCallbacks\HasCallbacks;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Report extends Resource
{
    use HasCallbacks;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Report>
     */
    public static $model = \App\Models\Report::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'topic';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search
        = [
            'id',
            'topic',
            'description',
        ];

    /**
     * Get the fields displayed by the resource.
     *
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $conference = \App\Models\Conference::getConferenceById(
            $request->conference
        );
        $reportId = \App\Models\Report::getReportByMeeting(
            $this->meeting_id
        )?->id;
        if ($this->meeting_id) {
            $meeting = ZoomController::getMeeting($this->meeting_id);
            $this->join_url = $meeting->join_url;
            $this->start_url = $meeting->start_url;
        }

        return [
            BelongsTo::make('Member', 'user')
                ->required(),
            BelongsTo::make('Conference')->required(),
            Text::make('Topic')->rules([
                'required',
                'min:2',
                'max:255',
            ])->sortable(),
            Text::make('Start time', 'start_time')->rules([
                'required',
                'date_multi_format:"H:i", "H:i:s"',
                new StartTimeMoreThanMinRule($conference?->start_time),
                new DurationIsLessThanHourRule(
                    $request->start_time,
                    $request->end_time
                ),
                new ReportTimeIsAvailableRule(
                    $request->conference,
                    $reportId
                ),
            ]),
            Text::make('End time', 'end_time')->rules([
                'required',
                'date_multi_format:"H:i", "H:i:s"',
                'after:start_time',
                new EndTimeLessThanMaxRule($conference?->end_time),
                new DurationIsLessThanHourRule(
                    $request->start_time,
                    $request->end_time
                ),
                new ReportTimeIsAvailableRule(
                    $request->conference,
                    $reportId
                ),
            ]),
            Textarea::make('Description'),
            File::make('Presentation')
                ->path('presentations')
                ->prunable()
                ->rules([
                    'max:10240',
                ])
                ->acceptedTypes('.pptx'),
            Boolean::make('Online', 'online', function () {
                return (bool) $this->meeting_id;
            }),
            Text::make('Join URL', 'join_url', function () {
                return $this->join_url;
            })
                ->readonly()
                ->copyable()
                ->hideWhenCreating()
                ->hideFromIndex(),
            Text::make('Start URL', 'start_url', function () {
                return $this->start_url;
            })
                ->readonly()
                ->copyable()
                ->hideWhenCreating()
                ->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * @throws GuzzleException
     */
    public static function beforeSave(Request $request, $model)
    {
//        dd($model->getOriginal('meeting_id'));
//        $online = $request->meeting_id;
        $reportInfo = $request->all();
        if ($request->query('editMode') !== 'create') {
            $reportInfo['topic'] = $model->topic;
            $reportInfo['description'] = $model->description;
            $reportInfo['conference'] = $model->conference_id;
        }
        if ($model->meeting_id) {
            ZoomController::remove($model->getOriginal('meeting_id'));
        }
        if (! $reportInfo['online']) {
            $model->meeting_id = null;
        } else {
            $reportDataForMeeting = [
                'topic' => $reportInfo['topic'],
                'description' => $reportInfo['description'],
            ];
            $conference = \App\Models\Conference::getConferenceById(
                $reportInfo['conference']
            );
            $reportDataForMeeting = array_merge(
                $reportDataForMeeting,
                ZoomMeetingHelper::getTimeDataOfMeeting($model, $conference)
            );
            $meetingInfo = ZoomController::create($reportDataForMeeting);
            $model->meeting_id = $meetingInfo['id'];
            $request->request->remove('meeting_id');
            $request->request->add(['meeting_id' => $meetingInfo['id']]);
        }
        $request->request->remove('online');
    }

    public static function afterDelete(NovaRequest $request, Model $model)
    {
        if ($model->meeting_id) {
            ZoomController::remove($model->meeting_id);
        }
    }
}
