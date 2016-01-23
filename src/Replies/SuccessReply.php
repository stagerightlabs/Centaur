<?php

namespace Centaur\Replies;

use Illuminate\Http\JsonResponse;

class SuccessReply extends Reply
{
    /**
     * The reccomended status code to include with the server response
     * @var integer
     */
    protected $statusCode = 200;

    /**
     * A boolean flag indicating if the manager class action was successful
     * @var boolean
     */
    protected $success = true;

    /**
     * Convert the reply to the appropriate redirect or response object
     * @var  string $url
     * @return Response|Redirect
     */
    public function dispatch($url = '/')
    {
        $request = app('request');

        if ($request->ajax() || $request->wantsJson()) {
            return new JsonResponse($this->toArray(), $this->statusCode);
        }

        // Should we post a flash message?
        if ($this->has('message')) {
            session()->flash('success', $this->message);
        }

        // Go to the specified url
        return redirect($url);
    }
}
