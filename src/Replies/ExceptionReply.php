<?php

namespace Centaur\Replies;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\UrlGenerator;

class ExceptionReply extends Reply
{
    /**
     * The default status code to include with the server response
     * @var integer
     */
    protected $statusCode = 500;

    /**
     * A boolean flag indicating if the manager class action was successful
     * @var boolean
     */
    protected $success = false;

    /**
     * Convert the reply to the appropriate redirect or response object
     * @var  string $url
     * @return Response|Redirect
     */
    public function dispatch($url = '/')
    {
        if (request()->wantsJson()) {
            return new JsonResponse($this->toArray(), $this->statusCode);
        }

        // Should we post a flash message?
        if ($this->has('message')) {
            session()->flash('error', $this->message);
        }

        // Go to the specified url
        return redirect($this->determineRedirectUrl())
            ->withInput(request()->input());
    }
}
