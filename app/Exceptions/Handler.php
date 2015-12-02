<?php namespace KlinkDMS\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Debug\Exception\FatalErrorException;
use KlinkException;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
		
		if(app()->environment('local')){
		
			if ($this->isHttpException($e))
			{	
				return $this->renderHttpException($e);
			}
			else if($e instanceof TokenMismatchException)
	        {
	        	if($request->wantsJson()){
	        		return new JsonResponse(array('error' => trans('errors.token_mismatch_exception')), 500);
	        	}
				else if($request->ajax()){
	        		return response(trans('errors.token_mismatch_exception'), 500);
	        	}
	
	            return response(trans('errors.token_mismatch_exception'), 500);
	        }
			else if($e instanceof FatalErrorException){
				if($request->wantsJson()){
	        		return new JsonResponse(array('error' => trans('errors.fatal', array('reason' => $e->getMessage()))), 500);
	        	}
				else if($request->ajax()){
	        		return response(trans('errors.fatal', array('reason' => $e->getMessage())), 500);
	        	}
	
	            return response(trans('errors.fatal', array('reason' => $e->getMessage())), 500);
			}
			
			return parent::render($request, $e);
			
		}
		else {
			\Log::error('Exception Handler render', ['e' => $e]);
			if ($this->isHttpException($e))
			{
				
				if($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException){
					
					$message = empty($e->getMessage()) ? trans('errors.404_text') : $e->getMessage(); 
					
					if($request->wantsJson()){
		        		return new JsonResponse(array('error' => $message), 404);
		        	}
					else if($request->ajax()){
		        		return response($message, 404);
		        	}
					
					return response()->make(view('errors.404', ['error_message' => $message]), 404);
				}
				else if($e->getStatusCode(413)){
					if($request->wantsJson()){
		        		return new JsonResponse(array('error' => trans('errors.413_text')), 413);
		        	}
					else if($request->ajax()){
		        		return response(trans('errors.413_text'), 413);
		        	}
				}
				
				
				return $this->renderHttpException($e);
			}
			else if($e instanceof ModelNotFoundException)
	        {
	        	if($request->wantsJson()){
	        		return new JsonResponse(array('error' => trans('errors.404_text')), 404);
	        	}
				else if($request->ajax()){
	        		return response(trans('errors.404_text'), 404);
	        	}
	
	            return response()->make(view('errors.404'), 404);
	        }
			else if($e instanceof TokenMismatchException)
	        {
	        	if($request->wantsJson()){
	        		return new JsonResponse(array('error' => trans('errors.token_mismatch_exception')), 401);
	        	}
				else if($request->ajax()){
	        		return response(trans('errors.token_mismatch_exception'), 401);
	        	}
	
	            return response(trans('errors.token_mismatch_exception'), 401);
	        }
			else if($e instanceof \Symfony\Component\Debug\Exception\FatalErrorException)
	        {
	        	if($request->wantsJson()){
	        		return new JsonResponse(array('error' => trans('errors.500_text')), 500);
	        	}
				else if($request->ajax()){
	        		return response(trans('errors.500_text'), 500);
	        	}
	
	            return response()->make(view('errors.500'), 500);
	        }
			else if($e instanceof ForbiddenException)
	        {
	        	if($request->wantsJson()){
	        		return new JsonResponse(array('error' => trans('errors.403_text')), 403);
	        	}
				else if($request->ajax()){
	        		return response(trans('errors.403_text'), 403);
	        	}
	
	            return response()->make(view('errors.403'), 403);
	        }
			else if($e instanceof KlinkException)
	        {
	        	if($request->wantsJson()){
	        		return new JsonResponse(array('error' => trans('errors.klink_exception_text')), 500);
	        	}
				else if($request->ajax()){
	        		return response(trans('errors.klink_exception_text'), 500);
	        	}
	
	            return response(trans('errors.klink_exception_text'), 500);
	        }
			else if($e instanceof FatalErrorException){
				if($request->wantsJson()){
	        		return new JsonResponse(array('error' => trans('errors.fatal', array('reason' => $e->getMessage()))), 500);
	        	}
				else if($request->ajax()){
	        		return response(trans('errors.fatal', array('reason' => $e->getMessage())), 500);
	        	}
	
	            return response(trans('errors.fatal', array('reason' => $e->getMessage())), 500);
			}
			else
			{
				return parent::render($request, $e);
			}
	
		}
		
	}

}