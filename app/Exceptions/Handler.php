<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use App\Exceptions\Landing\CouldntDetectSite;
use App\Exceptions\Landing\UnknownLandingIdentifier;
use App\Exceptions\Flow\UnknownFlowIdentifier;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Visitor\TooManyOrdersException;
use App\Exceptions\Request\IncorrectParameterException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        NotFoundHttpException::class,
        UnknownFlowIdentifier::class,
        UnknownLandingIdentifier::class,
        ValidationException::class,
        CouldntDetectSite::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        if (config('env.dd_exceptions', false)) {
            dd($e);
        }

        parent::report($e);

        if (app()->bound('sentry') && $this->shouldReport($e) && config('env.sentry_debug', true)) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if (app()->environment('testing')) {
            throw $e;
        }

        if ($e instanceof IncorrectParameterException || $e instanceof TooManyOrdersException) {
            return response()->view('errors.general', ['e' => $e], 500);
        }

        if ($e instanceof HttpException
            || $e instanceof CouldntDetectSite
            || $e instanceof UnknownFlowIdentifier
//            || $e instanceof ModelNotFoundException
        ) {
            return response()->view('errors.404', []);
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        \Log::error('unauthenticated in Handler.php');
        throw new \LogicException('unauthenticated in Handler.php');
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
