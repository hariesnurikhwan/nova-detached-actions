<?php

namespace Brightspot\Nova\Tools\DetachedActions;

use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionMethod;
use Laravel\Nova\Exceptions\MissingActionHandlerException;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Nova;

abstract class DetachedAction extends Action
{
    /**
     * Indicates if this action is only available on the custom index toolbar.
     *
     * @var bool
     */
    public $showOnIndexToolbar = true;

    /**
     * Indicates if this action is available on the resource index view.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * The displayable label of the button.
     *
     * @var string
     */
    public $label;

    /**
     * Get the displayable label of the button.
     *
     * @return string
     */
    public function label()
    {
        return $this->label ?: Nova::humanize($this);
    }

    /**
     * Execute the action for the given request.
     *
     * @param  \Laravel\Nova\Http\Requests\ActionRequest  $request
     * @return mixed
     * @throws MissingActionHandlerException
     */
    public function handleRequest(ActionRequest $request)
    {
        $method = ActionMethod::determine($this, $request->targetModel());

        if (! method_exists($this, $method)) {
            throw MissingActionHandlerException::make($this, $method);
        }

        $fields = $request->resolveFields();

        $results = DispatchAction::forModels(
            $request,
            $this,
            $method,
            collect([]),
            $fields
        );

        return $this->handleResult($fields, [$results]);
    }

    /**
     * Determine if the action is to be shown on the custom index toolbar.
     *
     * @return bool
     */
    public function shownOnIndexToolbar()
    {
        return $this->showOnIndexToolbar;
    }

    /**
     * Prepare the action for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'detachedAction' => true,
            'label' => $this->label(),
            'showOnIndexToolbar' => $this->shownOnIndexToolbar(),
        ], parent::jsonSerialize(), $this->meta());
    }
}
