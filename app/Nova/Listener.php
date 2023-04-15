<?php

namespace App\Nova;

use Ganyicz\NovaCallbacks\HasCallbacks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Listener extends Resource
{
    use HasCallbacks;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\User>
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search
        = [
            'firstname',
            'lastname',
            'email',
            'country_name',
            'phone',
        ];

    /**
     * Get the fields displayed by the resource.
     *
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [

            Text::make('First name', 'firstname')
                ->sortable()
                ->rules('required', 'max:255'),
            Text::make('Last name', 'lastname')
                ->sortable()
                ->rules('required', 'max:255'),
            Password::make('Password')
                ->rules('required', 'max:60'),
            Date::make('Birthdate')
                ->sortable()
                ->rules('required', 'date', 'before:tomorrow'),
            BelongsTo::make('Country')
                ->sortable()
                ->rules('required'),
            Text::make('Phone')
                ->rules('required', 'numeric'),
            Email::make('Email')
                ->sortable()
                ->rules('required', 'email', 'unique:users', 'max:60'),
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

    public static function beforeSave(NovaRequest $request, Model $model)
    {
        $model->type = 'listener';
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->where('type', '=', 'listener');
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('type', '=', 'listener');
    }
}
