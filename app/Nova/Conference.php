<?php

namespace App\Nova;

use Ganyicz\NovaCallbacks\HasCallbacks;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Trinityrank\GoogleMapWithAutocomplete\TRMap;

class Conference extends Resource
{
    use HasCallbacks;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Conference>
     */
    public static $model = \App\Models\Conference::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search
        = [
            'id',
            'title',
        ];

    /**
     * Get the fields displayed by the resource.
     *
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $latitude = $this->lat;
        $longitude = $this->lng;
        if (! $this->lat) {
            $latitude = 48.3794;
        }
        if (! $this->lng) {
            $longitude = 31.1656;
        }

        return [
            Text::make('Title')->rules([
                'required',
                'min:2',
                'max:255',
            ])->sortable(),
            Date::make('Date')->rules([
                'required',
                'date',
            ])->sortable(),
            Text::make('Start time', 'start_time')->rules([
                'required',
                'date_multi_format:"H:i", "H:i:s"',
            ]),
            Text::make('End time', 'end_time')->rules([
                'required',
                'date_multi_format:"H:i", "H:i:s"',
                'after:start_time',
            ]),
            Number::make('Latitude', 'lat')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->hideFromDetail(),
            Number::make('Longitude', 'lng')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->hideFromDetail(),
            TRMap::make('Address')
                ->latitude($latitude)
                ->longitude($longitude)
                ->hideFromIndex(),
            BelongsTo::make('Country')->required(),
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

    public static function beforeSave(Request $request, $model)
    {
        $conference = $request->all();
        $address = [];
        $address['lat'] = $conference['address']['latitude'];
        $address['lng'] = $conference['address']['longitude'];
        $request->request->remove('address');
        $request->request->add(['address' => $address]);
    }
}
