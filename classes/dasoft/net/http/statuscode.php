<?php
/**
 * Dasoft Toolkit
 * 
 * @category	Dasoft
 * @package		Dasoft\Http\Net
 * @author		Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright	Copyright (c) 2010-2011 Dasoft Inc. (http://www.dasoft.ca)
 * @license		http://dtk.dasoft.ca/license
 * @version		$Id$
 */

namespace Dasoft\Net;

/**
 * Status Codes
 * 
 * @category	Dasoft
 * @package		DasoftHttp\\Net
 * @author		Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright	Copyright (c) 2010-2011 Dasoft Inc. (http://www.dasoft.ca)
 * @license		http://dtk.dasoft.ca/license
 * @version		$Id$
 */
abstract class StatusCode
{
	const OK = 200;
	const CREATED = 201;
	const ACCEPTED = 202;
	const NO_CONTENT = 204;
	
	const BAD_REQUEST = 400;
	const BAD_REQUEST_DATE_MISSING = 4001;
	const BAD_REQUEST_IOTYPE_MISSING = 4002;
	const BAD_REQUEST_CONTENT_MALFORMED = 4003;
	
	const UNAUTHORIZED = 401;
	const UNAUTHORIZED_AUTH_REQUIRED = 4011;
	const UNAUTHORIZED_AUTH_FAILED = 4012;
	const UNAUTHORIZED_SIG_EXPIRED = 4013;
	
	const FORBIDDEN = 403;
	
	const NOT_FOUND = 404;
	
	const METHOD_NOT_ALLOWED = 405;
	
	const NOT_ACCEPTABLE = 406;
	
	const REQUEST_TIMEOUT = 408;
	
	const CONFLICT = 409;
	const CONFLICT_DUPLICATE = 4091;
	
	const GONE = 410;
	
	const PRECONDITION_FAILED = 412;
	const PRECONDITION_FAILED_UNKNOWN_API_VERSION = 4121;
	
	const UNSUPPORTED_MEDIA_TYPE = 415;
	
	const UNPROCESSABLE_ENTITY = 422;
	
	const UPGRADE_REQUIRED = 426;
	
	const INTERNAL_SERVER_ERROR = 500;
	
	const NOT_IMPLEMENTED = 501;
	
	const SERVICE_UNAVAILABLE = 503;
} 